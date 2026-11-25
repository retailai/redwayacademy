<?php
/** Shared helpers: escaping, flash messages, notifications, uploads. */
require_once __DIR__ . '/db.php';

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function now_str(): string { return date('Y-m-d H:i'); }
function digits_only(string $s): string { return preg_replace('/\D/', '', $s) ?? ''; }
function base_url(string $path = ''): string { return '/' . ltrim($path, '/'); }

function flash(string $msg): void {
    // Use start_session() (auth.php) when available: it runs only after
    // config.php has set the cookie params/name. A raw session_start() here
    // would create a default host-only PHPSESSID cookie and lose the
    // shared .redwayacademy.com login.
    if (function_exists('start_session')) start_session();
    elseif (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION['_flash'][] = $msg;
}
function flashes(): array {
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}
function abort_page(int $code, string $message): void {
    http_response_code($code);
    render('error', ['code' => $code, 'message' => $message]);
    exit;
}

/** Render a template inside the shared layout. */
function render(string $view, array $vars = []): void {
    $user = current_user();
    $unread = 0;
    if ($user) {
        $st = db()->prepare('SELECT COUNT(*) FROM notifications WHERE recipient_phone=? AND is_read=0');
        $st->execute([$user['phone']]);
        $unread = (int)$st->fetchColumn();
    }
    $vars['current_user'] = $user;
    $vars['unread_count'] = $unread;
    $vars['csrf_token'] = csrf_token();
    $vars['is_super_admin'] = $user ? is_super_admin($user) : false;
    $vars['retake_active'] = retake_campaign_active();
    $vars['subdomain_role'] = function_exists('site_host_role') ? site_host_role() : null;
    extract($vars);
    $view_file = __DIR__ . '/../templates/' . $view . '.php';
    if (!is_file($view_file)) { http_response_code(500); echo 'Missing template: ' . e($view); return; }
    include __DIR__ . '/../templates/layout.php';
    exit; // A rendered page is the end of the request; never fall through to the 404 handler.
}

function notify(string $phone, string $message, string $kind = 'info'): void {
    if ($phone === '') return;
    $st = db()->prepare('INSERT INTO notifications (recipient_phone, message, kind, created_at) VALUES (?,?,?,?)');
    $st->execute([$phone, $message, $kind, now_str()]);
}
function notify_parents_of(string $student_phone, string $message, string $kind = 'arrival'): void {
    notify($student_phone, $message, $kind);
    $st = db()->prepare("SELECT phone FROM users WHERE role='parent' AND linked_student_phone=?");
    $st->execute([$student_phone]);
    foreach ($st->fetchAll() as $p) notify($p['phone'], $message, $kind);
}

/** Text-only course thumbnail: gradient tile class cycled by course id. */
function thumb_class(array $c): string {
    $classes = ['t-blue', 't-red', 't-green', 't-yellow', 't-grey'];
    return $classes[((int)($c['id'] ?? 1) - 1) % count($classes)];
}
/** First letter of the course title, for the text-only thumbnail tile. */
function thumb_letter(array $c): string {
    $t = trim((string)($c['title'] ?? 'R'));
    return mb_strtoupper(mb_substr($t, 0, 1, 'UTF-8'), 'UTF-8');
}

/** Key-value settings (monthly fees, campaign switches). Defaults are
 * seeded in init_db(); an admin's saved value is never overwritten. */
function setting_get(string $key, string $default = ''): string {
    $st = db()->prepare('SELECT v FROM settings WHERE k=?');
    $st->execute([$key]);
    $r = $st->fetch();
    return $r ? (string)$r['v'] : $default;
}
function setting_set(string $key, string $value): void {
    if (db_driver() === 'mysql') {
        $st = db()->prepare('INSERT INTO settings (k, v) VALUES (?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)');
        $st->execute([$key, $value]);
    } else {
        $st = db()->prepare('INSERT INTO settings (k, v) VALUES (?,?) ON CONFLICT(k) DO UPDATE SET v=excluded.v');
        $st->execute([$key, $value]);
    }
}
/** Monthly subscription fee (৳/month): live classes, 3 days a week on
 * Google Meet / Zoom. One fee for both tracks: ৳1450. */
function track_monthly_fee(string $track): int {
    return max(0, (int)setting_get('subscription_fee_live', '1450'));
}
/** Recorded-class fee (৳). 0 means "not set yet" — decided later by the owner. */
function recorded_fee(): int {
    return max(0, (int)setting_get('recorded_fee', '0'));
}
/** Live class days per week (admin-editable, default 3). */
function live_days_per_week(): int {
    $d = (int)setting_get('live_days_per_week', '3');
    return min(7, max(1, $d));
}
/** Bilingual live-class schedule line, built from the stored days setting
 * so it updates when the admin changes the days. */
function live_class_text(): string {
    return sprintf(t('live_class'), num(live_days_per_week()));
}
/** Bilingual track name. */
function track_label(string $track): string {
    return $track === 'spoken' ? t('track_spoken') : t('track_academic');
}
/** The two tracks, in display order. */
function tracks(): array { return ['academic', 'spoken']; }
/** Free SSC/HSC retake is a campaign: on by default, flippable in Admin Panel. */
function retake_campaign_active(): bool {
    return setting_get('retake_campaign_active', '1') === '1';
}

/** Enrollment: a verified bKash/Nagad order IS the enrollment.
 * A verified monthly subscription also enrols the student in EVERY course
 * of that track until its valid_until date (30 days from verification). */
function is_subscribed(string $phone, string $track): bool {
    $st = db()->prepare("SELECT COUNT(*) FROM orders WHERE student_phone=? AND plan='monthly' AND track=? AND status='verified' AND valid_until IS NOT NULL AND valid_until<>'' AND valid_until>=?");
    $st->execute([$phone, $track, date('Y-m-d')]);
    return (int)$st->fetchColumn() > 0;
}
function subscription_until(string $phone, string $track): string {
    $st = db()->prepare("SELECT MAX(valid_until) FROM orders WHERE student_phone=? AND plan='monthly' AND track=? AND status='verified'");
    $st->execute([$phone, $track]);
    return (string)($st->fetchColumn() ?: '');
}
function is_enrolled(string $phone, int $course_id): bool {
    $st = db()->prepare("SELECT COUNT(*) FROM orders WHERE student_phone=? AND course_id=? AND status='verified'");
    $st->execute([$phone, $course_id]);
    if ((int)$st->fetchColumn() > 0) return true;
    $c = db()->prepare('SELECT track FROM courses WHERE id=?');
    $c->execute([$course_id]);
    $row = $c->fetch();
    if (!$row) return false;
    return is_subscribed($phone, (string)($row['track'] ?? 'academic'));
}
/** Verified courses for a phone, most recent first (one-time orders plus
 * every course of any actively subscribed track). */
function enrolled_courses(string $phone): array {
    $st = db()->prepare("SELECT DISTINCT c.* FROM courses c WHERE c.id IN (SELECT course_id FROM orders WHERE student_phone=? AND status='verified') OR c.track IN (SELECT track FROM orders WHERE student_phone=? AND plan='monthly' AND status='verified' AND valid_until IS NOT NULL AND valid_until<>'' AND valid_until>=?) ORDER BY c.id");
    $st->execute([$phone, $phone, date('Y-m-d')]);
    return $st->fetchAll();
}

function allowed_upload(string $name): bool {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    return in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true);
}
/** Move an uploaded file to uploads/ with a random name. Returns stored name or ''. */
function store_upload(array $file): string {
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return '';
    if (!allowed_upload($file['name'])) return '';
    if ($file['size'] > MAX_UPLOAD_MB * 1024 * 1024) return '';
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $safe = bin2hex(random_bytes(4)) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
    $dest = UPLOAD_DIR . '/' . $safe;
    return move_uploaded_file($file['tmp_name'], $dest) ? $safe : '';
}
