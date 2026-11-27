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
/** Module heading for the syllabus accordion and lesson sidebar.
 * The Spoken English course has named modules (1–4); every other course
 * keeps the plain "Module N" heading. */
function module_heading(array $course, int $m): string {
    $head = sprintf(t('module'), num($m));
    if ((string)($course['paper'] ?? '') === 'Spoken English' && $m >= 1 && $m <= 4) {
        $head .= ' — ' . t('module_spoken_' . $m);
    }
    return $head;
}
/** The two tracks, in display order. */
function tracks(): array { return ['academic', 'spoken']; }
/** Free SSC/HSC retake is a campaign: on by default, flippable in Admin Panel. */
function retake_campaign_active(): bool {
    return setting_get('retake_campaign_active', '1') === '1';
}

/** Extract a YouTube video ID from a watch / youtu.be / embed / shorts URL.
 * Returns '' when the URL is not a YouTube link or the ID is malformed.
 * Lessons have no videos yet — video_url is NULL until an admin pastes one. */
function youtube_id(string $url): string {
    $url = trim($url);
    if ($url === '') return '';
    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) return '';
    $host = strtolower(preg_replace('/^www\./', '', (string)$parts['host']));
    $id = '';
    if ($host === 'youtu.be') {
        $id = trim((string)($parts['path'] ?? ''), '/');
    } elseif ($host === 'youtube.com' || str_ends_with($host, '.youtube.com')) {
        $path = (string)($parts['path'] ?? '');
        if ($path === '/watch') {
            parse_str((string)($parts['query'] ?? ''), $qs);
            $id = (string)($qs['v'] ?? '');
        } elseif (preg_match('#^/(embed|shorts|v)/([^/]+)#', $path, $m)) {
            $id = $m[2];
        }
    }
    return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
}

/** First uncompleted lesson number for a student in a course.
 * Returns 1 when nothing is done yet, or when everything is done (review). */
function next_lesson_no(int $user_id, int $course_id): int {
    $st = db()->prepare('SELECT COUNT(*) FROM lessons WHERE course_id=?');
    $st->execute([$course_id]);
    $total = (int)$st->fetchColumn();
    if ($total < 1) return 1;
    $done = lesson_progress($user_id, $course_id);
    for ($n = 1; $n <= $total; $n++) {
        if (!in_array($n, $done, true)) return $n;
    }
    return 1;
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
// ---- two courses only: Academic English is one course, split by class ----
function class_bucket(string $level): string {
    $l = trim($level);
    if ($l === '9' || $l === '10' || $l === '9-10' || $l === '৯-১০') return '9-10';
    if ($l === '11' || $l === '12' || $l === '11-12' || $l === '১১-১২') return '11-12';
    return $l;
}
function student_class_bucket(?array $user): ?string {
    if (!$user || ($user['role'] ?? '') !== 'student') return null;
    $l = trim((string)($user['class_level'] ?? ''));
    if ($l === '') return null;
    return class_bucket($l);
}
// URL to a student's own Academic class page (used when they try another class).
function own_class_url(?array $user): ?string {
    $b = student_class_bucket($user);
    return $b === null ? null : '/courses?track=academic&class=' . urlencode($b);
}

// ---- lesson progress / per-lesson note / course rating ----
function lesson_mark_done(int $user_id, int $course_id, int $lesson_no): void {
    if (db_driver() === 'mysql') {
        $st = db()->prepare("INSERT INTO lesson_progress (user_id, course_id, lesson_no, done) VALUES (?,?,?,1) ON DUPLICATE KEY UPDATE done=1, updated_at=CURRENT_TIMESTAMP");
    } else {
        $st = db()->prepare("INSERT INTO lesson_progress (user_id, course_id, lesson_no, done) VALUES (?,?,?,1) ON CONFLICT(user_id, course_id, lesson_no) DO UPDATE SET done = 1, updated_at = CURRENT_TIMESTAMP");
    }
    $st->execute([$user_id, $course_id, $lesson_no]);
}
function lesson_progress(int $user_id, int $course_id): array {
    $st = db()->prepare("SELECT lesson_no FROM lesson_progress WHERE user_id = ? AND course_id = ? AND done = 1");
    $st->execute([$user_id, $course_id]);
    return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}
function lesson_note_get(int $user_id, int $course_id, int $lesson_no): string {
    $st = db()->prepare("SELECT note FROM lesson_progress WHERE user_id = ? AND course_id = ? AND lesson_no = ?");
    $st->execute([$user_id, $course_id, $lesson_no]);
    return (string)($st->fetchColumn() ?: '');
}
function lesson_note_save(int $user_id, int $course_id, int $lesson_no, string $note): void {
    if (db_driver() === 'mysql') {
        $st = db()->prepare("INSERT INTO lesson_progress (user_id, course_id, lesson_no, done, note) VALUES (?,?,?,0,?) ON DUPLICATE KEY UPDATE note=VALUES(note), updated_at=CURRENT_TIMESTAMP");
    } else {
        $st = db()->prepare("INSERT INTO lesson_progress (user_id, course_id, lesson_no, done, note) VALUES (?,?,?,0,?) ON CONFLICT(user_id, course_id, lesson_no) DO UPDATE SET note = excluded.note, updated_at = CURRENT_TIMESTAMP");
    }
    $st->execute([$user_id, $course_id, $lesson_no, $note]);
}
function course_rating_get(int $user_id, int $course_id): int {
    $st = db()->prepare("SELECT stars FROM course_ratings WHERE user_id = ? AND course_id = ?");
    $st->execute([$user_id, $course_id]);
    return (int)($st->fetchColumn() ?: 0);
}
function course_rating_save(int $user_id, int $course_id, int $stars): void {
    if (db_driver() === 'mysql') {
        $st = db()->prepare("INSERT INTO course_ratings (user_id, course_id, stars) VALUES (?,?,?) ON DUPLICATE KEY UPDATE stars=VALUES(stars), updated_at=CURRENT_TIMESTAMP");
    } else {
        $st = db()->prepare("INSERT INTO course_ratings (user_id, course_id, stars) VALUES (?,?,?) ON CONFLICT(user_id, course_id) DO UPDATE SET stars = excluded.stars, updated_at = CURRENT_TIMESTAMP");
    }
    $st->execute([$user_id, $course_id, $stars]);
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
// Only 2 courses: a monthly Academic subscription covers the student's own
// registered class only. When $class_bucket is given, Academic paper courses
// of other classes are left out (Spoken is one course for all).
function enrolled_courses(string $phone, ?string $class_bucket = null): array {
    $st = db()->prepare("SELECT DISTINCT c.* FROM courses c WHERE c.id IN (SELECT course_id FROM orders WHERE student_phone=? AND status='verified') OR c.track IN (SELECT track FROM orders WHERE student_phone=? AND plan='monthly' AND status='verified' AND valid_until IS NOT NULL AND valid_until<>'' AND valid_until>=?) ORDER BY c.id");
    $st->execute([$phone, $phone, date('Y-m-d')]);
    $rows = $st->fetchAll();
    if ($class_bucket !== null) {
        $rows = array_values(array_filter($rows, fn($c) => $c['track'] !== 'academic' || class_bucket((string)($c['class_level'] ?? '')) === $class_bucket));
    }
    return $rows;
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
