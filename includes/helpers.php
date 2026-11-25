<?php
/** Shared helpers: escaping, flash messages, notifications, uploads. */
require_once __DIR__ . '/db.php';

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function now_str(): string { return date('Y-m-d H:i'); }
function digits_only(string $s): string { return preg_replace('/\D/', '', $s) ?? ''; }
function base_url(string $path = ''): string { return '/' . ltrim($path, '/'); }

function flash(string $msg): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
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

/** Enrollment: a verified bKash/Nagad order IS the enrollment. */
function is_enrolled(string $phone, int $course_id): bool {
    $st = db()->prepare("SELECT COUNT(*) FROM orders WHERE student_phone=? AND course_id=? AND status='verified'");
    $st->execute([$phone, $course_id]);
    return (int)$st->fetchColumn() > 0;
}
/** Verified courses for a phone, most recent first. */
function enrolled_courses(string $phone): array {
    $st = db()->prepare("SELECT DISTINCT c.* FROM courses c JOIN orders o ON o.course_id=c.id WHERE o.student_phone=? AND o.status='verified' ORDER BY o.id DESC");
    $st->execute([$phone]);
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
