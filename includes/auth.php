<?php
/**
 * Redway Academy — auth, CSRF, login rate-limit.
 * 3 phone-number logins: Teacher/Admin/Super Admin, Student, Parent.
 * Passwords: password_hash / password_verify. First teacher = Super Admin.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/i18n.php';

function start_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['_initiated'])) { session_regenerate_id(true); $_SESSION['_initiated'] = true; }
}

function current_user(): ?array {
    start_session();
    if (empty($_SESSION['user_id'])) try_remember_login();
    if (empty($_SESSION['user_id'])) return null;
    $st = db()->prepare('SELECT * FROM users WHERE id=?');
    $st->execute([(int)$_SESSION['user_id']]);
    $u = $st->fetch();
    if (!$u) return null;
    if ((int)($u['active'] ?? 1) === 0) { logout_user(); return null; }
    return $u;
}

/**
 * Persistent "remember me" login — 30 days, like other websites.
 * A random selector identifies the token row; a random validator proves
 * ownership. Only HMAC-SHA256(validator, SECRET_KEY) is stored in the DB,
 * so a leaked database cannot be used to log in. The cookie is HttpOnly,
 * Secure in production, SameSite=Lax, and shared on .redwayacademy.com so
 * one login works on the main site and all role subdomains.
 */
function remember_cookie_options(int $expires): array {
    return ['expires' => $expires, 'path' => '/', 'domain' => cookie_domain(),
        'secure' => APP_ENV === 'production', 'httponly' => true, 'samesite' => 'Lax'];
}
function issue_remember_token(int $user_id): void {
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $hash = hash_hmac('sha256', $validator, SECRET_KEY);
    $expires = time() + REMEMBER_LIFETIME;
    $st = db()->prepare('INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at, created_at) VALUES (?,?,?,?,?)');
    $st->execute([$user_id, $selector, $hash, $expires, time()]);
    setcookie(REMEMBER_COOKIE, $selector . ':' . $validator, remember_cookie_options($expires));
    $_COOKIE[REMEMBER_COOKIE] = $selector . ':' . $validator;
}
function clear_remember_cookie(): void {
    setcookie(REMEMBER_COOKIE, '', remember_cookie_options(time() - 3600));
    // Also clear a host-only variant, so a stale pre-fix cookie can never
    // resurrect a login after logout.
    $opts = remember_cookie_options(time() - 3600);
    $opts['domain'] = '';
    setcookie(REMEMBER_COOKIE, '', $opts);
    unset($_COOKIE[REMEMBER_COOKIE]);
}
/** Revoke only this device's token (logout on this device). */
function revoke_remember_token(): void {
    $raw = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if ($raw !== '' && str_contains($raw, ':')) {
        [$selector] = explode(':', $raw, 2);
        $st = db()->prepare('DELETE FROM remember_tokens WHERE selector=?');
        $st->execute([$selector]);
    }
    clear_remember_cookie();
}
/** Revoke every persistent login for a user (password change / suspected theft). */
function revoke_all_remember_tokens(int $user_id): void {
    $st = db()->prepare('DELETE FROM remember_tokens WHERE user_id=?');
    $st->execute([$user_id]);
}
/**
 * Validate the remember-me cookie. On success, restore the session and
 * rotate the token (single-use: the old token is deleted, a fresh one is
 * issued), so a stolen cookie stops working after the real user returns.
 * Returns the user row, or null.
 */
function try_remember_login(): ?array {
    $raw = $_COOKIE[REMEMBER_COOKIE] ?? '';
    if ($raw === '' || !str_contains($raw, ':')) return null;
    [$selector, $validator] = explode(':', $raw, 2);
    if ($selector === '' || $validator === '') return null;
    $st = db()->prepare('SELECT * FROM remember_tokens WHERE selector=?');
    $st->execute([$selector]);
    $row = $st->fetch();
    if (!$row) { clear_remember_cookie(); return null; }
    if ((int)$row['expires_at'] < time()) {
        $del = db()->prepare('DELETE FROM remember_tokens WHERE id=?');
        $del->execute([(int)$row['id']]);
        clear_remember_cookie();
        return null;
    }
    if (!hash_equals((string)$row['token_hash'], hash_hmac('sha256', $validator, SECRET_KEY))) {
        // Wrong validator for a known selector: possible theft — revoke all.
        revoke_all_remember_tokens((int)$row['user_id']);
        clear_remember_cookie();
        return null;
    }
    $st = db()->prepare('SELECT * FROM users WHERE id=?');
    $st->execute([(int)$row['user_id']]);
    $u = $st->fetch();
    if (!$u || (int)($u['active'] ?? 1) === 0) {
        $del = db()->prepare('DELETE FROM remember_tokens WHERE id=?');
        $del->execute([(int)$row['id']]);
        clear_remember_cookie();
        return null;
    }
    $del = db()->prepare('DELETE FROM remember_tokens WHERE id=?');
    $del->execute([(int)$row['id']]);
    issue_remember_token((int)$u['id']);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$u['id'];
    return $u;
}
/** Log out everywhere for this session: session + remember token + cookies. */
function logout_user(): void {
    start_session();
    revoke_remember_token();
    $_SESSION = [];
    $expired = time() - 3600;
    // Clear the session cookie on the shared domain and the host-only variant.
    foreach ([cookie_domain(), ''] as $domain) {
        $opts = ['expires' => $expired, 'path' => '/', 'domain' => $domain,
            'secure' => APP_ENV === 'production', 'httponly' => true, 'samesite' => 'Lax'];
        setcookie(session_name(), '', $opts);
    }
    session_destroy();
    start_session();
}
function is_super_admin(?array $u): bool { return $u !== null && ($u['role'] ?? '') === 'teacher' && (int)($u['is_super_admin'] ?? 0) === 1; }

function login_required(): array {
    $u = current_user();
    if (!$u) { flash(t('login_first')); redirect('/login'); }
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

/**
 * Role subdomains: student.redwayacademy.com, parent.redwayacademy.com,
 * admin.redwayacademy.com (admin = teacher role). Returns the role the
 * current host serves, or null on the main domain / localhost.
 * DNS + subdomain creation in Hostinger hPanel is a deploy-time step;
 * this is the app-side routing that makes each subdomain role-only.
 */
function site_host_role(): ?string {
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '', 2)[0]);
    $labels = explode('.', $host);
    if (count($labels) < 2) return null;
    $map = ['student' => 'student', 'parent' => 'parent', 'admin' => 'teacher'];
    if (!isset($map[$labels[0]])) return null;
    // Real subdomains end in redwayacademy.com; allow *.localhost for local tests.
    $rest = implode('.', array_slice($labels, 1));
    if ($rest === 'redwayacademy.com' || $rest === 'localhost') return $map[$labels[0]];
    return null;
}
/** Bilingual label for a role, for subdomain refusal messages. */
function subdomain_role_label(string $role): string {
    return ['student' => 'Student / স্টুডেন্ট', 'parent' => 'Parent / অভিভাবক', 'teacher' => 'Teacher / Admin / শিক্ষক'][ $role ] ?? $role;
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
