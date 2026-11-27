<?php
/**
 * Redway Academy — configuration.
 * Mistakes Mark the Way / ভুল থেকেই শেখা, ভুল করেই জেতা
 *
 * All secrets come from environment variables (or a local .env file).
 * Never commit a real SECRET_KEY or database password.
 * On Hostinger, set the variables in hPanel / .env — see README.md.
 */

// Load a local .env file (dev / Hostinger) — plain parser, no dependency.
$__envFile = dirname(__DIR__) . '/.env';
if (is_file($__envFile)) {
    foreach (file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        $__line = trim($__line);
        if ($__line === '' || str_starts_with($__line, '#')) continue;
        [$__k, $__v] = array_pad(explode('=', $__line, 2), 2, '');
        $__k = trim($__k); $__v = trim(trim($__v), "\"'");
        if ($__k !== '' && getenv($__k) === false && !isset($_SERVER[$__k])) {
            putenv("$__k=$__v"); $_ENV[$__k] = $__v; $_SERVER[$__k] = $__v;
        }
    }
}

function envv(string $key, string $default = ''): string {
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return (string)$_SERVER[$key];
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return (string)$_ENV[$key];
    return $default;
}

define('APP_ENV', envv('APP_ENV', 'development'));
define('APP_DEBUG', APP_ENV !== 'production');
define('SECRET_KEY', envv('SECRET_KEY', 'redway-dev-secret-change-me'));
define('BASE_URL', rtrim(envv('BASE_URL', 'https://redwayacademy.com'), '/'));

// Database: MySQL on Hostinger via DB_HOST/DB_NAME/DB_USER/DB_PASS,
// or any PDO DSN via DB_DSN. SQLite for local testing:
//   DB_DSN=sqlite:/path/to/redway.db
define('DB_DSN', envv('DB_DSN', ''));
define('DB_HOST', envv('DB_HOST', 'localhost'));
define('DB_NAME', envv('DB_NAME', ''));
define('DB_USER', envv('DB_USER', ''));
define('DB_PASS', envv('DB_PASS', ''));

define('UPLOAD_DIR', envv('UPLOAD_FOLDER', dirname(__DIR__) . '/uploads'));
define('MAX_UPLOAD_MB', (int)envv('MAX_UPLOAD_MB', '8'));
define('SESSION_LIFETIME', 8 * 3600); // 8 hours
define('IDLE_TIMEOUT', 30 * 60); // 30 minutes without activity → auto-logout
define('LAST_PHONE_COOKIE', 'rw_last_phone'); // phone number only, for login prefill

/**
 * Cookie domain shared by the main site and the role subdomains.
 * The apex domain redwayacademy.com must ALSO get '.redwayacademy.com' —
 * a host-only cookie set on the main site is never sent to the subdomains,
 * which is why a login on redwayacademy.com was lost on
 * student./parent./admin.redwayacademy.com (and looked like "login
 * doesn't stay"). Localhost / other hosts keep a host-only cookie.
 */
function cookie_domain(): string {
    $host = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '', 2)[0]);
    if ($host === 'redwayacademy.com' || str_ends_with($host, '.redwayacademy.com')) {
        return '.redwayacademy.com';
    }
    return '';
}

// Secure session cookies.
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);
if (APP_ENV === 'production') ini_set('session.cookie_secure', '1');
if (SECRET_KEY !== '' && SECRET_KEY !== 'redway-dev-secret-change-me') {
    session_name('REDWAYSESSID');
}
// One login across the main domain and the role subdomains
// (student./parent./admin.redwayacademy.com share the session cookie).
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME, 'path' => '/', 'domain' => cookie_domain(),
    'httponly' => true, 'samesite' => 'Lax', 'secure' => APP_ENV === 'production',
]);

// Brand.
define('BRAND_SLOGAN_EN', 'Mistakes Mark the Way');
define('BRAND_SLOGAN_BN', 'ভুল থেকেই শেখা, ভুল করেই জেতা');
// Contact mobile — also the bKash / Nagad payment number.
define('CONTACT_PHONE', '01712506612');
define('CONTACT_ADDRESS', 'Nikunja 2, Khilkhet, Dhaka-1229');
