<?php
/**
 * Redway Academy — auth, CSRF, login rate-limit.
 * 3 phone-number logins: Teacher/Admin/Super Admin, Student, Parent.
 * Passwords: password_hash / password_verify. First teacher = Super Admin.
 */
require_once __DIR__ . '/db.php';

function start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['_initiated'])) { session_regenerate_id(true); $_SESSION['_initiated'] = true; }
}

function current_user(): ?array {
    start_session();
    if (empty($_SESSION['user_id'])) return null;
    $st = db()->prepare('SELECT * FROM users WHERE id=?');
    $st->execute([(int)$_SESSION['user_id']]);
    $u = $st->fetch();
    if (!$u) return null;
    if ((int)($u['active'] ?? 1) === 0) { session_destroy(); start_session(); return null; }
    return $u;
}
function is_super_admin(?array $u): bool { return $u !== null && ($u['role'] ?? '') === 'teacher' && (int)($u['is_super_admin'] ?? 0) === 1; }

function login_required(): array {
    $u = current_user();
    if (!$u) { flash('Please log in first.'); redirect('/login'); }
    return $u;
}
function teacher_required(): array {
    $u = current_user();
    if (!$u || $u['role'] !== 'teacher') { flash('Teacher login required.'); redirect('/login'); }
    return $u;
}
/** Role display names, shared by login / register / dashboard. */
function role_label(string $role): string {
    return ['student' => 'Student', 'parent' => 'Parent', 'teacher' => 'Teacher / Admin'][$role] ?? $role;
}
/** Each role's own dashboard page. */
function dashboard_url(array $u): string {
    return $u['role'] === 'teacher' ? '/dashboard/teacher'
        : ($u['role'] === 'parent' ? '/dashboard/parent' : '/dashboard/student');
}
/** Require a specific role; anyone else is sent to their own dashboard. */
function role_required(string $role): array {
    $u = login_required();
    if ($u['role'] !== $role) {
        flash('This page is for ' . role_label($role) . ' accounts. You are logged in as ' . role_label($u['role']) . ' — this is your dashboard instead.');
        redirect(dashboard_url($u));
    }
    return $u;
}
function admin_required(): array {
    $u = current_user();
    if (!$u || $u['role'] !== 'teacher') { flash('Admin Panel needs a teacher / admin login.'); redirect('/login'); }
    return $u;
}
function super_admin_required(): array {
    $u = admin_required();
    if (!is_super_admin($u)) { flash('Only the Super Admin can do that.'); redirect('/admin'); }
    return $u;
}

// --- CSRF -----------------------------------------------------------------
function csrf_token(): string {
    start_session();
    if (empty($_SESSION['_csrf_token'])) $_SESSION['_csrf_token'] = bin2hex(random_bytes(16));
    return $_SESSION['_csrf_token'];
}
function csrf_valid(?string $token): bool {
    start_session();
    return $token !== null && $token !== '' && hash_equals((string)($_SESSION['_csrf_token'] ?? ''), $token);
}
function require_csrf(): void {
    if (!csrf_valid($_POST['csrf_token'] ?? null)) abort_page(403, 'Security check failed (CSRF). Please go back and try again.');
}

// --- Login rate-limit: 5 wrong attempts per phone in 15 minutes -----------
function login_blocked(string $phone): bool {
    if ($phone === '') return false;
    $since = date('Y-m-d H:i', time() - 15 * 60);
    $st = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE phone=? AND attempted_at>=?');
    $st->execute([$phone, $since]);
    return (int)$st->fetchColumn() >= 5;
}
function record_failed_login(string $phone): void {
    if ($phone === '') return;
    $st = db()->prepare('INSERT INTO login_attempts (phone, attempted_at) VALUES (?,?)');
    $st->execute([$phone, now_str()]);
    // Prune old rows.
    db()->exec("DELETE FROM login_attempts WHERE attempted_at < '" . date('Y-m-d H:i', time() - 15 * 60) . "'");
}
