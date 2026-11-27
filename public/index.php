<?php
/**
 * Redway Academy — PHP front controller (Hostinger Premium shared hosting).
 * Plain PHP 8, no framework. PDO: MySQL on Hostinger, SQLite for local tests.
 * No facial recognition / biometric identification anywhere.
 * Arrival check-in is a teacher-confirmed 4-digit PIN.
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/i18n.php';

start_session();

// --- Idle auto-logout: 30 minutes without activity ------------------------
// A logged-in user whose last request was more than IDLE_TIMEOUT ago is
// logged out and sent to /login with the idle message (their phone number
// stays prefilled via the last-phone cookie). Otherwise the activity clock
// restarts on every request. No session = no-op (login/logout/health).
if (!empty($_SESSION['user_id'])) {
    if (!empty($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > IDLE_TIMEOUT) {
        $idle_phone = q1('SELECT phone FROM users WHERE id=?', [(int)$_SESSION['user_id']])['phone'] ?? '';
        logout_user();
        if ($idle_phone !== '') set_last_phone_cookie((string)$idle_phone);
        flash(t('logout_idle'));
        redirect('/login');
    }
    $_SESSION['last_activity'] = time();
}

// --- Security headers (same policy as the Flask version) --------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; script-src 'self'; frame-src https://www.youtube-nocookie.com https://www.youtube.com; frame-ancestors 'none'");

// --- Routing ----------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

// CSRF: admin POSTs require a valid token; other POSTs reject a wrong token if present.
if ($method === 'POST') {
    if (str_starts_with($path, '/admin')) {
        require_csrf();
    } elseif (!empty($_POST['csrf_token']) && !csrf_valid($_POST['csrf_token'])) {
        abort_page(403, 'Security check failed (CSRF). Please go back and try again.');
    }
}

function q(string $sql, array $params = []): array {
    $st = db()->prepare($sql); $st->execute($params); return $st->fetchAll();
}
function q1(string $sql, array $params = []): ?array {
    $st = db()->prepare($sql); $st->execute($params); $r = $st->fetch(); return $r ?: null;
}
function run(string $sql, array $params = []): PDOStatement {
    $st = db()->prepare($sql); $st->execute($params); return $st;
}
function post(string $k, string $d = ''): string { return trim($_POST[$k] ?? $d); }

// --- Role subdomains --------------------------------------------------------
// student./parent./admin.redwayacademy.com each serve only that role's login
// + dashboard. Public marketing pages on a subdomain go to the dashboard
// (login if logged out); /admin on a non-teacher subdomain does the same.
// The language choice (?lang=) is honoured before any redirect.
$sub_role = site_host_role();
if ($sub_role !== null) {
    $is_public_page = $path === '/' || $path === '/courses' || str_starts_with($path, '/course/')
        || $path === '/testimonials' || $path === '/retake' || str_starts_with($path, '/retake/');
    $is_admin_page = str_starts_with($path, '/admin');
    if ($is_public_page || ($is_admin_page && $sub_role !== 'teacher')) {
        redirect('/dashboard');
    }
}

// --- /health, /robots.txt, /sitemap.xml --------------------------------------
if ($path === '/health') {
    header('Content-Type: application/json');
    try { db()->query('SELECT 1'); echo json_encode(['status' => 'ok', 'app' => 'redway-academy']); }
    catch (Throwable $e) { http_response_code(500); echo json_encode(['status' => 'error']); }
    exit;
}
if ($path === '/robots.txt') {
    header('Content-Type: text/plain');
    echo "User-agent: *\nAllow: /\nSitemap: https://redwayacademy.com/sitemap.xml\n"; exit;
}
if ($path === '/sitemap.xml') {
    header('Content-Type: application/xml');
    $urls = ['https://redwayacademy.com/', 'https://redwayacademy.com/courses', 'https://redwayacademy.com/retake', 'https://redwayacademy.com/testimonials', 'https://redwayacademy.com/login', 'https://redwayacademy.com/register'];
    foreach (q('SELECT id FROM courses') as $c) $urls[] = 'https://redwayacademy.com/course/' . $c['id'];
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as $u) echo '  <url><loc>' . e($u) . "</loc></url>\n";
    echo '</urlset>'; exit;
}

// Static assets under /assets/ are served by the web server; the PHP
// built-in server falls through to them via the router below.
if (str_starts_with($path, '/assets/')) {
    $file = __DIR__ . $path;
    if (is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        header('Content-Type: ' . ['css' => 'text/css', 'js' => 'application/javascript', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'][$ext] ?? 'application/octet-stream');
        readfile($file); exit;
    }
    abort_page(404, 'Sorry — that page does not exist.');
}

// Uploaded files: login required, no PHP execution (see uploads/.htaccess).
if (str_starts_with($path, '/uploads/')) {
    $u = login_required();
    $name = basename($path);
    $file = UPLOAD_DIR . '/' . $name;
    if (!is_file($file) || str_contains($name, '..')) abort_page(404, 'File not found.');
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'][$ext] ?? 'application/octet-stream');
    header('Content-Disposition: inline; filename="' . $name . '"');
    readfile($file); exit;
}

// --- Public pages -----------------------------------------------------------
if ($path === '/' && $method === 'GET') {
    $me = current_user();
    $resume = null;
    if ($me && $me['role'] === 'student') {
        $enrolled = enrolled_courses($me['phone'], student_class_bucket($me));
        $resume = $enrolled[0] ?? null;
    }
    render('home', ['title' => 'Redway Academy — Mistakes Mark the Way',
        'courses' => q('SELECT * FROM courses WHERE active=1 ORDER BY id'),
        'total_lessons' => (int)db()->query('SELECT COUNT(*) FROM lessons')->fetchColumn(),
        'testimonials' => q("SELECT * FROM testimonials WHERE status='approved' ORDER BY id DESC LIMIT 6"),
        'resume_course' => $resume]);
}
if ($path === '/courses' && $method === 'GET') {
    // Only 2 courses: Spoken English and Academic English.
    // Academic: track -> class picker -> one class page (1st + 2nd Paper together).
    // Spoken: track -> last-education picker -> the Spoken course page.
    // Search (?q=) jumps straight to results. Steps: tracks | pick | class | list.
    $qtext = trim($_GET['q'] ?? '');
    // Old paper links (?paper=) map onto tracks for backwards compatibility.
    $track = in_array($_GET['track'] ?? '', ['academic', 'spoken'], true) ? $_GET['track'] : null;
    if ($track === null && isset($_GET['paper'])) {
        $track = $_GET['paper'] === 'Spoken English' ? 'spoken' : 'academic';
    }
    $class = $_GET['class'] ?? '';
    $edu = in_array($_GET['edu'] ?? '', ['SSC', 'HSC', 'Honours', 'Other'], true) ? $_GET['edu'] : '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 6;
    $step = 'tracks';
    $conds = ['active=1']; $params = [];
    $classes = ['6', '7', '8', '9-10', '11-12'];
    $me = current_user();
    if ($qtext !== '') {
        $step = 'list';
        $conds[] = '(title LIKE ? OR description LIKE ?)';
        $params[] = '%' . $qtext . '%'; $params[] = '%' . $qtext . '%';
    } elseif ($track === null) {
        $step = 'tracks';
    } elseif ($track === 'academic' && !in_array($class, $classes, true)) {
        $step = 'pick';
    } elseif ($track === 'spoken' && $edu === '') {
        $step = 'pick';
    } elseif ($track === 'spoken') {
        // Spoken English is one course for everyone: go to its page.
        $sp = q1("SELECT id FROM courses WHERE track='spoken' AND active=1 ORDER BY id LIMIT 1");
        if ($sp) redirect('/course/' . (int)$sp['id']);
        $step = 'tracks';
    } else {
        // Academic class page: a logged-in student only ever sees their own class.
        $own_bucket = student_class_bucket($me);
        if ($own_bucket !== null && $own_bucket !== $class) {
            flash(sprintf(t('class_gate'), $own_bucket, $own_bucket));
            redirect('/courses?track=academic&class=' . urlencode($own_bucket));
        }
        $step = 'class';
    }
    $rows = []; $total = 0; $pages = 1;
    $counts = []; $lesson_counts = [];
    foreach (tracks() as $tr) {
        $counts[$tr] = (int)db()->query("SELECT COUNT(*) FROM courses WHERE active=1 AND track='" . $tr . "'")->fetchColumn();
        $lesson_counts[$tr] = (int)db()->query("SELECT COUNT(*) FROM lessons l JOIN courses c ON c.id=l.course_id WHERE c.active=1 AND c.track='" . $tr . "'")->fetchColumn();
    }
    $academic_classes = (int)db()->query("SELECT COUNT(DISTINCT class_level) FROM courses WHERE active=1 AND track='academic'")->fetchColumn();
    $papers = []; $class_lessons_total = 0; $class_subscribed = false; $class_paper_enrolled = [];
    if ($step === 'class') {
        $papers = q('SELECT * FROM courses WHERE active=1 AND track=? AND class_level=? ORDER BY id', ['academic', $class]);
        foreach ($papers as &$p) {
            $p['lessons_list'] = q('SELECT lesson_no, title FROM lessons WHERE course_id=? ORDER BY lesson_no', [(int)$p['id']]);
            $class_lessons_total += count($p['lessons_list']);
            if ($me && $me['role'] === 'student') {
                $class_paper_enrolled[(int)$p['id']] = is_enrolled($me['phone'], (int)$p['id']);
            }
        }
        unset($p);
        $class_subscribed = $me && $me['role'] === 'student' ? is_subscribed($me['phone'], 'academic') : false;
    }
    if ($step === 'list') {
        $where = implode(' AND ', $conds);
        $st = db()->prepare("SELECT COUNT(*) FROM courses WHERE $where");
        $st->execute($params);
        $total = (int)$st->fetchColumn();
        $pages = max(1, (int)ceil($total / $per_page));
        $page = min($page, $pages);
        $st = db()->prepare("SELECT * FROM courses WHERE $where ORDER BY id LIMIT $per_page OFFSET " . (($page - 1) * $per_page));
        $st->execute($params);
        $rows = $st->fetchAll();
    }
    $list_url = function (array $over) use ($track, $class, $edu, $qtext): string {
        $f = [];
        if ($track) $f['track'] = $track;
        if ($class !== '') $f['class'] = $class;
        if ($edu !== '') $f['edu'] = $edu;
        if ($qtext !== '') $f['q'] = $qtext;
        foreach ($over as $k => $v) $f[$k] = $v;
        return '/courses' . ($f ? '?' . http_build_query($f) : '');
    };
    render('courses', ['title' => 'Courses — Redway Academy', 'courses' => $rows,
        'step' => $step, 'track' => $track, 'class' => $class, 'edu' => $edu, 'q' => $qtext,
        'classes' => $classes, 'counts' => $counts, 'lesson_counts' => $lesson_counts,
        'academic_classes' => $academic_classes, 'page' => $page, 'pages' => $pages,
        'total' => $total, 'list_url' => $list_url, 'papers' => $papers,
        'class_lessons_total' => $class_lessons_total, 'class_subscribed' => $class_subscribed,
        'class_paper_enrolled' => $class_paper_enrolled, 'own_class' => student_class_bucket($me),
        'me' => $me]);
}
if (preg_match('#^/course/(\d+)$#', $path, $m) && $method === 'GET') {
    $c = q1('SELECT * FROM courses WHERE id=?', [(int)$m[1]]);
    if (!$c) abort_page(404, 'Sorry — that course does not exist.');
    $u = current_user();
    // Class gate: an Academic student only opens their own registered class.
    $own_url = own_class_url($u);
    if ($own_url !== null && ($c['track'] ?? '') === 'academic'
        && class_bucket((string)($c['class_level'] ?? '')) !== student_class_bucket($u)) {
        $b = student_class_bucket($u);
        flash(sprintf(t('class_gate'), $b, $b));
        redirect($own_url);
    }
    $enrolled = $u && $u['role'] === 'student' ? is_enrolled($u['phone'], (int)$c['id']) : false;
    $can_view = $enrolled || ($u && $u['role'] === 'teacher');
    $track = (string)($c['track'] ?? 'academic');
    $subscribed = $u && $u['role'] === 'student' ? is_subscribed($u['phone'], $track) : false;
    render('course_detail', ['title' => $c['title'] . ' — Redway Academy', 'course' => $c,
        'is_enrolled' => $enrolled, 'can_view_lessons' => $can_view,
        'track_fee' => track_monthly_fee($track), 'is_subscribed' => $subscribed,
        'lessons' => $can_view ? q('SELECT lesson_no, title FROM lessons WHERE course_id=? ORDER BY lesson_no', [(int)$c['id']]) : [],
        'done_lessons' => $u && $u['role'] === 'student' ? lesson_progress((int)$u['id'], (int)$c['id']) : [],
        'my_rating' => $u && $u['role'] === 'student' ? course_rating_get((int)$u['id'], (int)$c['id']) : 0]);
}
// --- Lesson player actions: mark done / save note / rate -------------------
if (preg_match('#^/course/(\d+)/lesson/(\d+)/(done|note|rate)$#', $path, $m) && $method === 'POST') {
    $u = login_required();
    require_csrf();
    $c = q1('SELECT * FROM courses WHERE id=?', [(int)$m[1]]);
    $lesson = $c ? q1('SELECT * FROM lessons WHERE course_id=? AND lesson_no=?', [(int)$m[1], (int)$m[2]]) : null;
    if (!$c || !$lesson) abort_page(404, 'Sorry — that lesson does not exist.');
    $allowed = ($u['role'] === 'teacher') || ($u['role'] === 'student' && is_enrolled($u['phone'], (int)$c['id']));
    // Class gate applies here too: only the student's own class.
    $own_url = own_class_url($u);
    if ($allowed && $own_url !== null && ($c['track'] ?? '') === 'academic'
        && class_bucket((string)($c['class_level'] ?? '')) !== student_class_bucket($u)) {
        $allowed = false;
    }
    if (!$allowed) {
        flash('Please enrol in this course first — buy it with bKash / Nagad and a teacher will verify your payment.');
        redirect('/course/' . (int)$c['id']);
    }
    if ($m[3] === 'done') {
        lesson_mark_done((int)$u['id'], (int)$c['id'], (int)$m[2]);
    } elseif ($m[3] === 'note') {
        lesson_note_save((int)$u['id'], (int)$c['id'], (int)$m[2], mb_substr(trim($_POST['note'] ?? ''), 0, 4000));
        flash(t('lp_note_saved'));
    } else {
        $stars = max(1, min(5, (int)($_POST['stars'] ?? 0)));
        if ($stars > 0) { course_rating_save((int)$u['id'], (int)$c['id'], $stars); flash(t('lp_rated')); }
    }
    redirect('/course/' . (int)$c['id'] . '/lesson/' . (int)$m[2]);
}
// --- Lesson view: enrolled students and teachers only -----------------------
if (preg_match('#^/course/(\d+)/lesson/(\d+)$#', $path, $m) && $method === 'GET') {
    $u = login_required();
    $c = q1('SELECT * FROM courses WHERE id=?', [(int)$m[1]]);
    if (!$c) abort_page(404, 'Sorry — that course does not exist.');
    $lesson = q1('SELECT * FROM lessons WHERE course_id=? AND lesson_no=?', [(int)$m[1], (int)$m[2]]);
    if (!$lesson) abort_page(404, 'Sorry — that lesson does not exist.');
    $allowed = ($u['role'] === 'teacher') || ($u['role'] === 'student' && is_enrolled($u['phone'], (int)$c['id']));
    if (!$allowed) {
        flash('Please enrol in this course first — buy it with bKash / Nagad and a teacher will verify your payment.');
        redirect('/course/' . (int)$c['id']);
    }
    // Class gate: an Academic student who opens another class's lesson is
    // sent back to their own class page with a bilingual message.
    $own_url = own_class_url($u);
    if ($own_url !== null && ($c['track'] ?? '') === 'academic'
        && class_bucket((string)($c['class_level'] ?? '')) !== student_class_bucket($u)) {
        $b = student_class_bucket($u);
        flash(sprintf(t('class_gate'), $b, $b));
        redirect($own_url);
    }
    $total = (int)q1('SELECT COUNT(*) AS n FROM lessons WHERE course_id=?', [(int)$c['id']])['n'];
    render('lesson', ['title' => $lesson['title'] . ' — ' . $c['title'],
        'course' => $c, 'lesson' => $lesson, 'total' => $total,
        'all_lessons' => q('SELECT lesson_no, title FROM lessons WHERE course_id=? ORDER BY lesson_no', [(int)$c['id']]),
        'done_lessons' => $u['role'] === 'student' ? lesson_progress((int)$u['id'], (int)$c['id']) : [],
        'my_note' => $u['role'] === 'student' ? lesson_note_get((int)$u['id'], (int)$c['id'], (int)$m[2]) : '',
        'my_rating' => $u['role'] === 'student' ? course_rating_get((int)$u['id'], (int)$c['id']) : 0]);
}
if ($path === '/testimonials') {
    if ($method === 'POST') {
        run("INSERT INTO testimonials (author_name, role, class_level, message, status, created_at) VALUES (?,?,?,?,'pending',?)",
            [post('author_name'), post('role', 'Student'), post('class_level'), post('message'), now_str()]);
        foreach (q("SELECT phone FROM users WHERE role='teacher'") as $t) {
            notify($t['phone'], "New endorsement to review from " . post('author_name') . " (" . post('role', 'Student') . ").", 'testimonial');
        }
        flash(t('review_sent'));
        redirect('/testimonials');
    }
    render('testimonials', ['title' => 'Reviews — Redway Academy',
        'testimonials' => q("SELECT * FROM testimonials WHERE status='approved' ORDER BY id DESC")]);
}
if ($path === '/community' && $method === 'GET') {
    render('community', ['title' => t('nav_community') . ' — Redway Academy']);
}
if ($path === '/terms' && $method === 'GET') {
    render('terms', ['title' => t('terms_title') . ' — Redway Academy']);
}
if ($path === '/privacy' && $method === 'GET') {
    render('privacy', ['title' => t('priv_title') . ' — Redway Academy']);
}
if ($path === '/refund' && $method === 'GET') {
    render('refund', ['title' => t('ref_title') . ' — Redway Academy']);
}

// --- Auth -------------------------------------------------------------------
if ($path === '/register') {
    if ($method === 'POST') {
        $name = post('name'); $phone = digits_only(post('phone')); $password = $_POST['password'] ?? '';
        // On a role subdomain, registration is for that role only.
        $role = $sub_role ?? (in_array(post('role'), ['student', 'parent', 'teacher'], true) ? post('role') : 'student');
        $class_level = post('class_level');
        // Role-conditional fields (server-side is the real gate — the form's
        // JS only shows/hides them). Class is required for students and
        // parents; parents also give their child's name, a 13-digit bKash
        // payment number (01 + 11 digits) and the payment Trxn ID.
        $student_name = trim($_POST['student_name'] ?? '');
        $bkash_no = digits_only(post('bkash_payment_no'));
        $trxn_id = trim($_POST['payment_trxn_id'] ?? '');
        if ($name === '' || strlen($phone) < 11 || strlen($password) < 4) {
            flash(t('fill_all'));
            redirect('/register');
        }
        $valid_classes = ['6', '7', '8', '9-10', '11-12'];
        if ($role === 'student') {
            if (!in_array($class_level, $valid_classes, true)) { flash(t('reg_class_required')); redirect('/register'); }
        } elseif ($role === 'parent') {
            if ($student_name === '' || !in_array($class_level, $valid_classes, true)) { flash(t('reg_parent_required')); redirect('/register'); }
            if (!preg_match('/^01\d{11}$/', $bkash_no)) { flash(t('reg_bkash_invalid')); redirect('/register'); }
            if ($trxn_id === '') { flash(t('reg_trxn_required')); redirect('/register'); }
        }
        if ($role === 'teacher') {
            $existing = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
            if ($existing > 0) { flash(t('teacher_add_only')); redirect('/login'); }
        }
        $is_first_teacher = $role === 'teacher' && (int)db()->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn() === 0;
        try {
            run('INSERT INTO users (name, phone, password_hash, role, class_level, student_name, bkash_payment_no, payment_trxn_id, is_super_admin, created_at) VALUES (?,?,?,?,?,?,?,?,?,?)',
                [$name, $phone, password_hash($password, PASSWORD_DEFAULT), $role, $class_level ?: null, $role === 'parent' ? $student_name : null, $role === 'parent' ? $bkash_no : null, $role === 'parent' ? $trxn_id : null, $is_first_teacher ? 1 : 0, now_str()]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            $_SESSION['last_activity'] = time();
            set_last_phone_cookie($phone);
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || $e->getCode() === '23000') {
                flash(t('phone_registered')); redirect('/login');
            }
            throw $e;
        }
        flash(sprintf(t('welcome'), $name, t('role_' . $role)));
        redirect($role === 'student' ? '/my-courses' : '/dashboard');
    }
    render('register', ['title' => 'Register — Redway Academy']);
}
if ($path === '/login') {
    // Already logged in? Go to the student's My Courses, or own dashboard — never show the login form again.
    if ($method === 'GET') {
        $me = current_user();
        if ($me) redirect($me['role'] === 'student' ? '/my-courses' : dashboard_url($me));
    }
    if ($method === 'POST') {
        $phone = digits_only(post('phone')); $password = $_POST['password'] ?? '';
        if (login_blocked($phone)) {
            flash(t('login_locked'));
            render('login', ['title' => 'Login — Redway Academy']); exit;
        }
        $u = q1('SELECT * FROM users WHERE phone=?', [$phone]);
        if ($u && password_verify($password, $u['password_hash'])) {
            if ((int)($u['active'] ?? 1) === 0) {
                flash(t('account_deactivated'));
                render('login', ['title' => 'Login — Redway Academy']); exit;
            }
            // A role subdomain only accepts that role's accounts.
            if ($sub_role !== null && $u['role'] !== $sub_role) {
                flash(sprintf(t('subdomain_role'), subdomain_role_label($sub_role)));
                redirect('/login');
            }
            $selected_role = post('role');
            if (!in_array($selected_role, ['student', 'parent', 'teacher'], true)) {
                flash(t('select_role'));
                redirect('/login');
            }
            if ($selected_role !== $u['role']) {
                $label = t('role_' . $u['role']);
                flash(sprintf(t('role_mismatch'), $label, $label));
                redirect('/login');
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['last_activity'] = time();
            set_last_phone_cookie($phone);
            flash(sprintf(t('welcome_back'), $u['name']));
            redirect($u['role'] === 'student' ? '/my-courses' : '/dashboard');
        }
        record_failed_login($phone);
        flash(t('wrong_login'));
    }
    render('login', ['title' => 'Login — Redway Academy']);
}
if ($path === '/logout' && $method === 'GET') {
    logout_user();
    if (($_GET['idle'] ?? '') === '1') flash(t('logout_idle'));
    redirect('/login');
}

// --- Dashboard / progress / feedback / notifications ------------------------
// --- Role dashboards: each role has its own page ---------------------------
// /dashboard is only an entry point — it sends each role to its own page.
function build_dashboard_ctx(array $u): array {
    $ctx = ['title' => 'Dashboard — Redway Academy', 'user' => $u];
    if ($u['role'] === 'teacher') {
        $ctx += ['orders' => q('SELECT * FROM orders ORDER BY id DESC'),
            'retakes' => q('SELECT * FROM retake_registrations ORDER BY id DESC'),
            'submissions' => q('SELECT * FROM submissions ORDER BY id DESC LIMIT 20'),
            'homework' => q('SELECT * FROM homework ORDER BY id DESC'),
            'teachers' => q("SELECT * FROM users WHERE role='teacher' ORDER BY id"),
            'students' => q("SELECT * FROM users WHERE role='student' ORDER BY id DESC LIMIT 50")];
    } elseif ($u['role'] === 'parent') {
        $phone = $u['linked_student_phone'] ?: $u['phone'];
        $linked = $u['linked_student_phone'] ? q1('SELECT name, class_level FROM users WHERE phone=? AND role=?', [$u['linked_student_phone'], 'student']) : null;
        $linked_bucket = ($linked && trim((string)($linked['class_level'] ?? '')) !== '') ? class_bucket((string)$linked['class_level']) : null;
        $ctx += ['progress' => q('SELECT * FROM progress_notes WHERE student_phone=? ORDER BY id DESC', [$phone]),
            'orders' => q('SELECT * FROM orders WHERE student_phone=? ORDER BY id DESC', [$phone]),
            'checkins' => q('SELECT * FROM checkins WHERE student_phone=? ORDER BY id DESC LIMIT 10', [$phone]),
            'my_courses' => enrolled_courses($phone, $linked_bucket),
            'linked_student_name' => $linked['name'] ?? null];
    } else {
        $ctx += ['progress' => q('SELECT * FROM progress_notes WHERE student_phone=? ORDER BY id DESC', [$u['phone']]),
            'orders' => q('SELECT * FROM orders WHERE student_phone=? ORDER BY id DESC', [$u['phone']]),
            'homework' => q('SELECT * FROM homework ORDER BY id DESC'),
            'my_submissions' => q('SELECT * FROM submissions WHERE student_phone=? ORDER BY id DESC', [$u['phone']]),
            'my_courses' => enrolled_courses($u['phone'], student_class_bucket($u))];
    }
    $ctx['notifications'] = q('SELECT * FROM notifications WHERE recipient_phone=? ORDER BY id DESC LIMIT 10', [$u['phone']]);
    return $ctx;
}
if ($path === '/dashboard' && $method === 'GET') {
    $u = login_required();
    redirect(dashboard_url($u));
}
if ($path === '/dashboard/teacher' && $method === 'GET') {
    $u = role_required('teacher');
    render('dashboard_teacher', build_dashboard_ctx($u));
}
if ($path === '/dashboard/student' && $method === 'GET') {
    $u = role_required('student');
    render('dashboard_student', build_dashboard_ctx($u));
}
if ($path === '/dashboard/parent' && $method === 'GET') {
    $u = role_required('parent');
    render('dashboard_parent', build_dashboard_ctx($u));
}
// --- My Courses: the student's two course previews ------------------------
// Students only — a teacher/parent is sent to their own dashboard, same as
// role_required() on the dashboard pages. Two cards only: Spoken English
// (one course for all) and Academic English (the student's own class:
// 1st + 2nd Paper together). Progress comes from lesson_progress; a course
// the student is not enrolled in shows its not-enrolled state honestly.
if ($path === '/my-courses' && $method === 'GET') {
    $u = role_required('student');
    $bucket = student_class_bucket($u);
    $cards = [];
    // Spoken English — one course for all.
    $spoken = q1("SELECT * FROM courses WHERE track='spoken' AND active=1 ORDER BY id LIMIT 1");
    if ($spoken) {
        $sid = (int)$spoken['id'];
        $total = (int)(q1('SELECT COUNT(*) AS n FROM lessons WHERE course_id=?', [$sid])['n'] ?? 0);
        $done = lesson_progress((int)$u['id'], $sid);
        $enrolled = is_enrolled($u['phone'], $sid);
        $cards[] = [
            'track' => 'spoken', 'title' => t('track_spoken_full'), 'desc' => t('track_spoken_d'),
            'banner' => '/assets/img/banner-spoken.png', 'banner_alt' => 'Spoken English — Speak with Confidence',
            'total' => $total, 'done_count' => count($done), 'enrolled' => $enrolled,
            'course_url' => '/course/' . $sid,
            'buy_url' => '/buy/' . $sid,
            'continue_url' => $enrolled ? '/course/' . $sid . '/lesson/' . next_lesson_no((int)$u['id'], $sid) : null,
            'btn_class' => 'btn-green',
        ];
    }
    // Academic English — the student's own class (1st + 2nd Paper together).
    $papers = $bucket !== null ? q('SELECT * FROM courses WHERE active=1 AND track=? AND class_level=? ORDER BY id', ['academic', $bucket]) : [];
    if (!empty($papers)) {
        $total = 0; $done_count = 0; $enrolled = false; $continue_url = null;
        foreach ($papers as $p) {
            $pid = (int)$p['id'];
            $pt = (int)(q1('SELECT COUNT(*) AS n FROM lessons WHERE course_id=?', [$pid])['n'] ?? 0);
            $pd = lesson_progress((int)$u['id'], $pid);
            $total += $pt; $done_count += count($pd);
            if (is_enrolled($u['phone'], $pid)) $enrolled = true;
            // First uncompleted lesson across the papers, in order.
            if ($continue_url === null && count($pd) < $pt) {
                $continue_url = '/course/' . $pid . '/lesson/' . next_lesson_no((int)$u['id'], $pid);
            }
        }
        if ($continue_url === null) $continue_url = '/course/' . (int)$papers[0]['id'] . '/lesson/1';
        $cards[] = [
            'track' => 'academic', 'title' => sprintf(t('academic_class'), num($bucket)), 'desc' => t('track_academic_d'),
            'banner' => '/assets/img/banner-academic.png', 'banner_alt' => 'Academic English — NCTB English for Today, Grammar & Composition',
            'total' => $total, 'done_count' => $done_count, 'enrolled' => $enrolled,
            'course_url' => '/courses?track=academic&class=' . urlencode($bucket),
            'buy_url' => '/buy/' . (int)$papers[0]['id'],
            'continue_url' => $enrolled ? $continue_url : null,
            'btn_class' => 'btn-primary',
        ];
    }
    render('my_courses', ['title' => t('my_courses_title') . ' — Redway Academy', 'cards' => $cards, 'class_bucket' => $bucket]);
}
if ($path === '/progress') {
    $u = login_required();
    if ($method === 'POST' && $u['role'] === 'teacher') {
        $s_phone = digits_only(post('student_phone')); $s_name = post('student_name'); $score = (int)post('score');
        run('INSERT INTO progress_notes (student_phone, student_name, score, suggestion, teacher_phone, created_at) VALUES (?,?,?,?,?,?)',
            [$s_phone, $s_name, $score, post('suggestion'), $u['phone'], now_str()]);
        notify($s_phone, "New progress update from your teacher: score $score.", 'progress');
        notify_parents_of($s_phone, "New progress update for $s_name: score $score.", 'progress');
        flash(t('progress_added'));
        redirect('/dashboard');
    }
    $phone = ($u['role'] === 'parent' && $u['linked_student_phone']) ? $u['linked_student_phone'] : $u['phone'];
    render('progress', ['title' => 'Progress — Redway Academy',
        'progress' => q('SELECT * FROM progress_notes WHERE student_phone=? ORDER BY id DESC', [$phone])]);
}
if ($path === '/feedback') {
    $u = login_required();
    if ($method === 'POST') {
        run('INSERT INTO feedback (author_phone, author_name, message, created_at) VALUES (?,?,?,?)',
            [$u['phone'], $u['name'], post('message'), now_str()]);
        foreach (q("SELECT phone FROM users WHERE role='teacher'") as $t) {
            notify($t['phone'], "New feedback from {$u['name']} ({$u['role']}).", 'feedback');
        }
        flash(t('feedback_sent'));
        redirect('/dashboard');
    }
    render('feedback', ['title' => 'Feedback — Redway Academy', 'feedback' => q('SELECT * FROM feedback ORDER BY id DESC LIMIT 20')]);
}
if ($path === '/notifications' && $method === 'GET') {
    $u = login_required();
    run('UPDATE notifications SET is_read=1 WHERE recipient_phone=?', [$u['phone']]);
    render('notifications', ['title' => 'Notifications — Redway Academy',
        'notifications' => q('SELECT * FROM notifications WHERE recipient_phone=? ORDER BY id DESC', [$u['phone']])]);
}
if ($path === '/announce' && $method === 'POST') {
    $u = teacher_required();
    $msg = post('message');
    if ($msg !== '') {
        run('INSERT INTO announcements (teacher_phone, message, created_at) VALUES (?,?,?)', [$u['phone'], $msg, now_str()]);
        foreach (q('SELECT phone FROM users') as $row) notify($row['phone'], "Announcement from Redway Academy: $msg", 'announcement');
        flash('Announcement sent to all students and parents.');
    }
    redirect('/dashboard');
}

// --- Homework ---------------------------------------------------------------
if ($path === '/homework') {
    $u = login_required();
    if ($method === 'POST' && $u['role'] === 'teacher') {
        run('INSERT INTO homework (title, class_level, details, teacher_phone, created_at) VALUES (?,?,?,?,?)',
            [post('title'), post('class_level'), post('details'), $u['phone'], now_str()]);
        foreach (q("SELECT phone FROM users WHERE role='student'") as $s) {
            notify($s['phone'], 'New homework: ' . post('title') . '.', 'homework');
        }
        flash(t('homework_created'));
        redirect('/homework');
    }
    render('homework', ['title' => 'Homework — Redway Academy', 'homework' => q('SELECT * FROM homework ORDER BY id DESC')]);
}
if (preg_match('#^/homework/(\d+)/submit$#', $path, $m) && $method === 'POST') {
    $u = login_required();
    run('INSERT INTO submissions (homework_id, student_phone, student_name, answer, file_link, created_at) VALUES (?,?,?,?,?,?)',
        [(int)$m[1], $u['phone'], $u['name'], post('answer'), post('file_link'), now_str()]);
    foreach (q("SELECT phone FROM users WHERE role='teacher'") as $t) {
        notify($t['phone'], "Homework submitted by {$u['name']}.", 'homework');
    }
    flash(t('homework_sent'));
    redirect('/homework');
}
if (preg_match('#^/homework/submission/(\d+)/review$#', $path, $m) && $method === 'POST') {
    teacher_required();
    $status = post('status', 'checked'); $comment = post('teacher_comment');
    run('UPDATE submissions SET status=?, teacher_comment=? WHERE id=?', [$status, $comment, (int)$m[1]]);
    $row = q1('SELECT * FROM submissions WHERE id=?', [(int)$m[1]]);
    if ($row) {
        notify($row['student_phone'], "Your homework was reviewed: $status. $comment", 'homework');
        notify_parents_of($row['student_phone'], "Homework review for {$row['student_name']}: $status.", 'homework');
    }
    flash('Submission reviewed.');
    redirect('/dashboard');
}

// --- Course purchase: bKash / Nagad ----------------------------------------
if (preg_match('#^/buy/(\d+)$#', $path, $m)) {
    $u = login_required();
    $course = q1('SELECT * FROM courses WHERE id=?', [(int)$m[1]]);
    if (!$course) abort_page(404, 'Sorry — that course does not exist.');
    $track = (string)($course['track'] ?? 'academic');
    $fee = track_monthly_fee($track);
    // Plan: 'monthly' (whole track, 1 month — primary) or 'once' (this course).
    $plan = in_array($_GET['plan'] ?? ($_POST['plan'] ?? ''), ['monthly', 'once'], true)
        ? ($_GET['plan'] ?? $_POST['plan']) : 'monthly';
    $amount = $plan === 'monthly' ? $fee : (int)$course['price_bdt'];
    if ($method === 'POST') {
        $trxid = strtoupper(post('trxid'));
        if ($trxid === '') { flash(t('need_trxid')); redirect('/buy/' . $course['id'] . '?plan=' . $plan); }
        $sender_phone = digits_only(post('sender_phone'));
        if (!preg_match('/^01\d{9}$/', $sender_phone)) { flash(t('need_sender')); redirect('/buy/' . $course['id'] . '?plan=' . $plan); }
        $method_pay = post('method', 'bKash');
        $title = $plan === 'monthly'
            ? sprintf('%s — %s', t('monthly'), track_label($track))
            : $course['title'];
        run('INSERT INTO orders (course_id, course_title, student_phone, student_name, method, trxid, sender_phone, amount_bdt, plan, track, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)',
            [$course['id'], $title, $u['phone'], $u['name'], $method_pay, $trxid, $sender_phone, $amount, $plan, $track, now_str()]);
        foreach (q("SELECT phone FROM users WHERE role='teacher'") as $t) {
            notify($t['phone'], "New $method_pay payment to verify: $title, TrxID $trxid, sender $sender_phone, ৳$amount.", 'payment');
        }
        flash(t('order_sent'));
        redirect('/dashboard');
    }
    render('buy', ['title' => 'Buy — ' . $course['title'], 'course' => $course,
        'plan' => $plan, 'track' => $track, 'fee' => $fee, 'amount' => $amount]);
}
if (preg_match('#^/order/(\d+)/verify$#', $path, $m) && $method === 'POST') {
    teacher_required();
    $status = post('status', 'verified');
    run('UPDATE orders SET status=? WHERE id=?', [$status, (int)$m[1]]);
    $row = q1('SELECT * FROM orders WHERE id=?', [(int)$m[1]]);
    if ($row) {
        // A verified monthly subscription runs 30 days from verification.
        if ($status === 'verified' && ($row['plan'] ?? 'once') === 'monthly' && empty($row['valid_until'])) {
            run('UPDATE orders SET valid_until=? WHERE id=?', [date('Y-m-d', strtotime('+30 days')), (int)$m[1]]);
        }
        notify($row['student_phone'], "Your payment for {$row['course_title']} was $status (TrxID {$row['trxid']}).", 'payment');
        notify_parents_of($row['student_phone'], "Payment for {$row['student_name']} — {$row['course_title']} — was $status.", 'payment');
    }
    flash("Order marked $status.");
    redirect('/dashboard');
}

// --- Free SSC / HSC Retake --------------------------------------------------
function verify_retake(array $form, string $admit, string $tab): array {
    // Automatic checks only. Never auto-rejects: missing/unreadable -> needs_review.
    $reasons = [];
    foreach (['student_name' => 'name', 'phone' => 'phone number', 'exam' => 'exam (SSC/HSC)', 'exam_year' => 'exam year', 'roll_no' => 'roll number', 'reg_no' => 'registration number', 'result_text' => 'result'] as $f => $label) {
        if (trim($form[$f] ?? '') === '') $reasons[] = "Missing $label.";
    }
    if (!preg_match('/^\d{4}$/', trim($form['exam_year'] ?? ''))) $reasons[] = 'Exam year must be 4 digits.';
    if ($admit === '') $reasons[] = 'Admit card file was not uploaded.';
    if ($tab === '') $reasons[] = 'Tabulation sheet file was not uploaded.';
    return $reasons ? ['needs_review', implode(' ', $reasons)]
        : ['auto_verified', 'All required fields and both files were present and matched the form.'];
}
if ($path === '/retake') {
    // The free retake is a campaign: when the admin switches it off, the
    // form closes and visitors see a "not running right now" message.
    if ($method === 'POST' && !retake_campaign_active()) {
        flash(t('retake_off_title'));
        redirect('/retake');
    }
    if ($method === 'POST') {
        $admit = store_upload($_FILES['admit_card'] ?? []);
        $tab = store_upload($_FILES['tabulation'] ?? []);
        $form = [];
        foreach (['student_name', 'phone', 'exam', 'exam_year', 'roll_no', 'reg_no', 'result_text'] as $k) $form[$k] = post($k);
        [$status, $reasons] = verify_retake($form, $admit, $tab);
        run('INSERT INTO retake_registrations (student_name, phone, exam, exam_year, roll_no, reg_no, result_text, admit_file, tabulation_file, status, reasons, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [$form['student_name'], digits_only($form['phone']), $form['exam'], $form['exam_year'], $form['roll_no'], $form['reg_no'], $form['result_text'], $admit, $tab, $status, $reasons, now_str()]);
        $id = (int)db()->lastInsertId();
        notify($form['phone'], 'Retake registration received: ' . str_replace('_', ' ', $status) . ". $reasons", 'retake');
        foreach (q("SELECT phone FROM users WHERE role='teacher'") as $t) {
            notify($t['phone'], "New retake registration: {$form['student_name']} ({$form['exam']} {$form['exam_year']}) — $status.", 'retake');
        }
        flash(sprintf(t('retake_sent'), str_replace('_', ' ', $status), $reasons));
        redirect('/retake/' . $id);
    }
    render('retake', ['title' => 'Free Retake Registration — Redway Academy',
        'campaign_off' => !retake_campaign_active()]);
}
if (preg_match('#^/retake/(\d+)$#', $path, $m) && $method === 'GET') {
    $row = q1('SELECT * FROM retake_registrations WHERE id=?', [(int)$m[1]]);
    if (!$row) abort_page(404, 'Sorry — that registration does not exist.');
    render('retake_status', ['title' => 'Retake Status — Redway Academy', 'reg' => $row]);
}
if (preg_match('#^/retake/(\d+)/review$#', $path, $m) && $method === 'POST') {
    teacher_required();
    $status = post('status', 'auto_verified');
    run('UPDATE retake_registrations SET status=? WHERE id=?', [$status, (int)$m[1]]);
    $row = q1('SELECT * FROM retake_registrations WHERE id=?', [(int)$m[1]]);
    if ($row) notify($row['phone'], 'Your retake registration was ' . str_replace('_', ' ', $status) . ' by a teacher.', 'retake');
    flash("Retake registration marked $status.");
    redirect('/dashboard');
}

// --- Arrival check-in: teacher-confirmed PIN (no facial recognition) -------
if ($path === '/checkin') {
    $u = login_required();
    if ($method === 'POST' && $u['role'] === 'teacher') {
        $pin = (string)random_int(1000, 9999);
        run('INSERT INTO checkin_codes (pin, class_level, teacher_phone, created_at) VALUES (?,?,?,?)',
            [$pin, post('class_level'), $u['phone'], now_str()]);
        flash(sprintf(t('pin_created'), $pin));
        redirect('/checkin');
    }
    if ($method === 'POST') {
        $pin = post('pin');
        $code = q1('SELECT * FROM checkin_codes WHERE pin=? AND is_active=1', [$pin]);
        if (!$code) { flash(t('pin_wrong')); redirect('/checkin'); }
        run('INSERT INTO checkins (student_phone, student_name, class_level, pin, created_at) VALUES (?,?,?,?,?)',
            [$u['phone'], $u['name'], $code['class_level'], $pin, now_str()]);
        flash(t('checkin_requested'));
        redirect('/checkin');
    }
    render('checkin', ['title' => 'Check-in — Redway Academy',
        'codes' => q('SELECT * FROM checkin_codes WHERE is_active=1 ORDER BY id DESC'),
        'pending' => q("SELECT * FROM checkins WHERE status='pending' ORDER BY id DESC"),
        'mine' => q('SELECT * FROM checkins WHERE student_phone=? ORDER BY id DESC LIMIT 10', [$u['phone']])]);
}
if (preg_match('#^/checkin/(\d+)/confirm$#', $path, $m) && $method === 'POST') {
    teacher_required();
    run("UPDATE checkins SET status='confirmed' WHERE id=?", [(int)$m[1]]);
    $row = q1('SELECT * FROM checkins WHERE id=?', [(int)$m[1]]);
    if ($row) {
        $msg = "✅ {$row['student_name']} has arrived at Redway Academy (Class {$row['class_level']}) — confirmed by teacher.";
        notify_parents_of($row['student_phone'], $msg, 'arrival');
        notify($row['student_phone'], $msg, 'arrival');
    }
    flash(t('arrival_confirmed'));
    redirect('/checkin');
}

// --- Admin: add teachers ----------------------------------------------------
if ($path === '/admin/teachers') {
    $u = teacher_required();
    if ($method === 'POST') {
        $name = post('name'); $phone = digits_only(post('phone')); $password = $_POST['password'] ?? '';
        if ($name === '' || strlen($phone) < 11 || strlen($password) < 4) {
            flash('Give a name, a valid phone number and a password of 4+ characters.');
            redirect('/admin/teachers');
        }
        try {
            run("INSERT INTO users (name, phone, password_hash, role, created_at) VALUES (?,?,?,'teacher',?)",
                [$name, $phone, password_hash($password, PASSWORD_DEFAULT), now_str()]);
            flash("Teacher $name added.");
        } catch (PDOException $e) {
            flash('This phone number is already registered.');
        }
        redirect('/admin/teachers');
    }
    render('admin/teachers', ['title' => 'Admin — Teachers', 'teachers' => q("SELECT * FROM users WHERE role='teacher' ORDER BY id")]);
}

// --- Admin Panel ------------------------------------------------------------
if ($path === '/admin' && $method === 'GET') {
    $u = admin_required();
    $c = fn($sql) => (int)db()->query($sql)->fetchColumn();
    $counts = [
        'users' => $c('SELECT COUNT(*) FROM users'),
        'students' => $c("SELECT COUNT(*) FROM users WHERE role='student'"),
        'parents' => $c("SELECT COUNT(*) FROM users WHERE role='parent'"),
        'teachers' => $c("SELECT COUNT(*) FROM users WHERE role='teacher'"),
        'courses' => $c('SELECT COUNT(*) FROM courses WHERE active=1'),
        'orders_pending' => $c("SELECT COUNT(*) FROM orders WHERE status='pending'"),
        'orders_total' => $c('SELECT COUNT(*) FROM orders'),
        'submissions_pending' => $c("SELECT COUNT(*) FROM submissions WHERE status='submitted'"),
        'retakes_pending' => $c("SELECT COUNT(*) FROM retake_registrations WHERE status='needs_review'"),
        'testimonials_pending' => $c("SELECT COUNT(*) FROM testimonials WHERE status='pending'"),
        'checkins_pending' => $c("SELECT COUNT(*) FROM checkins WHERE status='pending'"),
    ];
    render('admin/dashboard', ['title' => 'Admin Panel — Redway Academy', 'user' => $u, 'counts' => $counts,
        'recent_orders' => q('SELECT * FROM orders ORDER BY id DESC LIMIT 5'),
        'recent_retakes' => q('SELECT * FROM retake_registrations ORDER BY id DESC LIMIT 5'),
        'recent_feedback' => q('SELECT * FROM feedback ORDER BY id DESC LIMIT 5')]);
}
if ($path === '/admin/settings') {
    admin_required();
    if ($method === 'POST') {
        setting_set('subscription_fee_live', (string)max(0, (int)post('fee_live')));
        setting_set('recorded_fee', (string)max(0, (int)post('fee_recorded')));
        setting_set('live_days_per_week', (string)min(7, max(1, (int)post('live_days'))));
        setting_set('retake_campaign_active', isset($_POST['retake_campaign']) ? '1' : '0');
        flash(t('settings_saved'));
        redirect('/admin/settings');
    }
    render('admin/settings', ['title' => 'Admin — Settings',
        'fee_live' => track_monthly_fee('academic'),
        'fee_recorded' => recorded_fee(),
        'live_days' => live_days_per_week(),
        'retake_active' => retake_campaign_active()]);
}
if ($path === '/admin/courses' && $method === 'GET') {
    admin_required();
    render('admin/courses', ['title' => 'Admin — Courses', 'courses' => q('SELECT * FROM courses ORDER BY id')]);
}
if ($path === '/admin/courses/new') {
    admin_required();
    if ($method === 'POST') {
        $track = in_array(post('track'), ['academic', 'spoken'], true) ? post('track') : 'academic';
        run('INSERT INTO courses (title, class_level, paper, track, price_bdt, lessons, description, active) VALUES (?,?,?,?,?,?,?,1)',
            [post('title'), post('class_level'), post('paper', '1st Paper'), $track, (int)post('price_bdt'), (int)post('lessons'), post('description')]);
        flash('Course created.');
        redirect('/admin/courses');
    }
    render('admin/course_form', ['title' => 'New course — Admin', 'course' => null]);
}
if (preg_match('#^/admin/courses/(\d+)/edit$#', $path, $m)) {
    admin_required();
    $course = q1('SELECT * FROM courses WHERE id=?', [(int)$m[1]]);
    if (!$course) abort_page(404, 'Course not found.');
    if ($method === 'POST') {
        $track = in_array(post('track'), ['academic', 'spoken'], true) ? post('track') : 'academic';
        run('UPDATE courses SET title=?, class_level=?, paper=?, track=?, price_bdt=?, lessons=?, description=?, active=? WHERE id=?',
            [post('title'), post('class_level'), post('paper', '1st Paper'), $track, (int)post('price_bdt'), (int)post('lessons'), post('description'), isset($_POST['active']) ? 1 : 0, (int)$m[1]]);
        flash('Course updated.');
        redirect('/admin/courses');
    }
    render('admin/course_form', ['title' => 'Edit course — Admin', 'course' => $course]);
}
if (preg_match('#^/admin/courses/(\d+)/delete$#', $path, $m) && $method === 'POST') {
    admin_required();
    run('DELETE FROM courses WHERE id=?', [(int)$m[1]]);
    flash('Course deleted.');
    redirect('/admin/courses');
}
if ($path === '/admin/users' && $method === 'GET') {
    admin_required();
    render('admin/users', ['title' => 'Admin — Users', 'users' => q('SELECT * FROM users ORDER BY id DESC')]);
}
if (preg_match('#^/admin/users/(\d+)/role$#', $path, $m) && $method === 'POST') {
    super_admin_required();
    $role = post('role', 'student');
    if (!in_array($role, ['student', 'parent', 'teacher'], true)) abort_page(400, 'Invalid role.');
    run('UPDATE users SET role=? WHERE id=?', [$role, (int)$m[1]]);
    flash('User role updated.');
    redirect('/admin/users');
}
if (preg_match('#^/admin/users/(\d+)/toggle$#', $path, $m) && $method === 'POST') {
    $me = super_admin_required();
    $target = q1('SELECT * FROM users WHERE id=?', [(int)$m[1]]);
    if (!$target) abort_page(404, 'User not found.');
    if ((int)$target['id'] === (int)$me['id']) { flash('You cannot deactivate your own account.'); redirect('/admin/users'); }
    run('UPDATE users SET active=? WHERE id=?', [(int)($target['active'] ?? 1) ? 0 : 1, (int)$m[1]]);
    flash('User ' . ((int)($target['active'] ?? 1) ? 'deactivated' : 'activated') . '.');
    redirect('/admin/users');
}
if ($path === '/admin/homework' && $method === 'GET') {
    admin_required();
    render('admin/homework', ['title' => 'Admin — Homework',
        'homework' => q('SELECT * FROM homework ORDER BY id DESC'),
        'submissions' => q('SELECT * FROM submissions ORDER BY id DESC LIMIT 30')]);
}
if (preg_match('#^/admin/homework/(\d+)/edit$#', $path, $m)) {
    admin_required();
    $hw = q1('SELECT * FROM homework WHERE id=?', [(int)$m[1]]);
    if (!$hw) abort_page(404, 'Homework not found.');
    if ($method === 'POST') {
        run('UPDATE homework SET title=?, class_level=?, details=? WHERE id=?',
            [post('title'), post('class_level'), post('details'), (int)$m[1]]);
        flash('Homework updated.');
        redirect('/admin/homework');
    }
    render('admin/homework_form', ['title' => 'Edit homework — Admin', 'hw' => $hw]);
}
if (preg_match('#^/admin/homework/(\d+)/delete$#', $path, $m) && $method === 'POST') {
    admin_required();
    run('DELETE FROM homework WHERE id=?', [(int)$m[1]]);
    flash('Homework deleted.');
    redirect('/admin/homework');
}
if ($path === '/admin/orders' && $method === 'GET') {
    admin_required();
    $status = $_GET['status'] ?? 'all';
    $rows = in_array($status, ['pending', 'verified'], true)
        ? q('SELECT * FROM orders WHERE status=? ORDER BY id DESC', [$status])
        : q('SELECT * FROM orders ORDER BY id DESC');
    render('admin/orders', ['title' => 'Admin — Orders', 'orders' => $rows, 'status' => $status]);
}
if (preg_match('#^/admin/orders/(\d+)/verify$#', $path, $m) && $method === 'POST') {
    admin_required();
    $status = post('status', 'verified');
    if (!in_array($status, ['verified', 'rejected'], true)) abort_page(400, 'Invalid status.');
    $row = q1('SELECT * FROM orders WHERE id=?', [(int)$m[1]]);
    if (!$row) abort_page(404, 'Order not found.');
    if ($status === 'rejected') {
        // Rejected orders are removed from the panel (owner decision 2026-11-27):
        // notify the student + parents first, then delete the row so it no
        // longer stays in the orders list. The notifications stay.
        notify($row['student_phone'], "Your payment for {$row['course_title']} was rejected (TrxID {$row['trxid']}).", 'payment');
        notify_parents_of($row['student_phone'], "Payment for {$row['student_name']} — {$row['course_title']} — was rejected.", 'payment');
        run('DELETE FROM orders WHERE id=?', [(int)$m[1]]);
        flash('Order rejected and removed.');
        redirect('/admin/orders');
    }
    run('UPDATE orders SET status=? WHERE id=?', [$status, (int)$m[1]]);
    // A verified monthly subscription runs 30 days from verification.
    if (($row['plan'] ?? 'once') === 'monthly' && empty($row['valid_until'])) {
        run('UPDATE orders SET valid_until=? WHERE id=?', [date('Y-m-d', strtotime('+30 days')), (int)$m[1]]);
    }
    notify($row['student_phone'], "Your payment for {$row['course_title']} was verified (TrxID {$row['trxid']}).", 'payment');
    notify_parents_of($row['student_phone'], "Payment for {$row['student_name']} — {$row['course_title']} — was verified.", 'payment');
    flash('Order marked verified.');
    redirect('/admin/orders');
}
if ($path === '/admin/retakes' && $method === 'GET') {
    admin_required();
    render('admin/retakes', ['title' => 'Admin — Retakes', 'retakes' => q('SELECT * FROM retake_registrations ORDER BY id DESC')]);
}
if (preg_match('#^/admin/retakes/(\d+)/review$#', $path, $m) && $method === 'POST') {
    admin_required();
    $status = post('status', 'auto_verified');
    if (!in_array($status, ['auto_verified', 'needs_review', 'rejected'], true)) abort_page(400, 'Invalid status.');
    run('UPDATE retake_registrations SET status=? WHERE id=?', [$status, (int)$m[1]]);
    $row = q1('SELECT * FROM retake_registrations WHERE id=?', [(int)$m[1]]);
    if ($row) notify($row['phone'], 'Your retake registration was ' . str_replace('_', ' ', $status) . ' by a teacher.', 'retake');
    flash("Retake registration marked $status.");
    redirect('/admin/retakes');
}
if ($path === '/admin/testimonials' && $method === 'GET') {
    admin_required();
    render('admin/testimonials', ['title' => 'Admin — Reviews', 'testimonials' => q('SELECT * FROM testimonials ORDER BY id DESC')]);
}
if (preg_match('#^/admin/testimonials/(\d+)/status$#', $path, $m) && $method === 'POST') {
    admin_required();
    $status = post('status', 'approved');
    if (!in_array($status, ['approved', 'hidden', 'pending'], true)) abort_page(400, 'Invalid status.');
    run('UPDATE testimonials SET status=? WHERE id=?', [$status, (int)$m[1]]);
    flash("Testimonial $status.");
    redirect('/admin/testimonials');
}
if (preg_match('#^/admin/testimonials/(\d+)/delete$#', $path, $m) && $method === 'POST') {
    admin_required();
    run('DELETE FROM testimonials WHERE id=?', [(int)$m[1]]);
    flash('Testimonial deleted.');
    redirect('/admin/testimonials');
}
if ($path === '/admin/progress' && $method === 'GET') {
    admin_required();
    render('admin/progress', ['title' => 'Admin — Progress',
        'progress' => q('SELECT * FROM progress_notes ORDER BY id DESC LIMIT 100'),
        'feedback' => q('SELECT * FROM feedback ORDER BY id DESC LIMIT 30')]);
}
if (preg_match('#^/admin/progress/(\d+)/delete$#', $path, $m) && $method === 'POST') {
    admin_required();
    run('DELETE FROM progress_notes WHERE id=?', [(int)$m[1]]);
    flash('Progress note deleted.');
    redirect('/admin/progress');
}
if ($path === '/admin/notifications') {
    $u = admin_required();
    if ($method === 'POST') {
        $msg = post('message'); $audience = post('audience', 'all'); $class_level = post('class_level');
        if ($msg === '') { flash('Write a message first.'); redirect('/admin/notifications'); }
        run('INSERT INTO announcements (teacher_phone, message, created_at) VALUES (?,?,?)', [$u['phone'], $msg, now_str()]);
        if ($audience === 'all') $users = q('SELECT phone FROM users');
        elseif (in_array($audience, ['teacher', 'student', 'parent'], true)) $users = q('SELECT phone FROM users WHERE role=?', [$audience]);
        elseif ($audience === 'class' && $class_level !== '') $users = q('SELECT phone FROM users WHERE class_level=?', [$class_level]);
        else $users = [];
        foreach ($users as $row) notify($row['phone'], "Announcement from Redway Academy: $msg", 'announcement');
        flash('Announcement sent to ' . count($users) . ' user(s).');
        redirect('/admin/notifications');
    }
    render('admin/notifications', ['title' => 'Admin — Notify', 'announcements' => q('SELECT * FROM announcements ORDER BY id DESC LIMIT 10')]);
}
if ($path === '/admin/checkins') {
    $u = admin_required();
    if ($method === 'POST') {
        $pin = (string)random_int(1000, 9999);
        run('INSERT INTO checkin_codes (pin, class_level, teacher_phone, created_at) VALUES (?,?,?,?)',
            [$pin, post('class_level'), $u['phone'], now_str()]);
        flash("Check-in PIN created: $pin");
        redirect('/admin/checkins');
    }
    render('admin/checkins', ['title' => 'Admin — Check-in',
        'codes' => q('SELECT * FROM checkin_codes ORDER BY id DESC LIMIT 20'),
        'checkins' => q('SELECT * FROM checkins ORDER BY id DESC LIMIT 30')]);
}
if (preg_match('#^/admin/checkins/code/(\d+)/deactivate$#', $path, $m) && $method === 'POST') {
    admin_required();
    run('UPDATE checkin_codes SET is_active=0 WHERE id=?', [(int)$m[1]]);
    flash('Check-in PIN deactivated.');
    redirect('/admin/checkins');
}

// --- 404 --------------------------------------------------------------------
abort_page(404, 'Sorry — that page does not exist.');
