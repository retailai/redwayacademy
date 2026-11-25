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

start_session();

// --- Security headers (same policy as the Flask version) --------------------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; script-src 'self'; frame-ancestors 'none'");

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
    render('home', ['title' => 'Redway Academy — Mistakes Mark the Way',
        'courses' => q('SELECT * FROM courses WHERE active=1 ORDER BY id'),
        'testimonials' => q("SELECT * FROM testimonials WHERE status='approved' ORDER BY id DESC LIMIT 6")]);
}
if ($path === '/courses' && $method === 'GET') {
    $paper = $_GET['paper'] ?? 'All';
    $rows = in_array($paper, ['1st Paper', '2nd Paper', 'Spoken English'], true)
        ? q('SELECT * FROM courses WHERE paper=? AND active=1 ORDER BY id', [$paper])
        : q('SELECT * FROM courses WHERE active=1 ORDER BY id');
    render('courses', ['title' => 'Courses — Redway Academy', 'courses' => $rows, 'paper' => $paper]);
}
if (preg_match('#^/course/(\d+)$#', $path, $m) && $method === 'GET') {
    $c = q1('SELECT * FROM courses WHERE id=?', [(int)$m[1]]);
    if (!$c) abort_page(404, 'Sorry — that course does not exist.');
    $u = current_user();
    $enrolled = $u && $u['role'] === 'student' ? is_enrolled($u['phone'], (int)$c['id']) : false;
    $can_view = $enrolled || ($u && $u['role'] === 'teacher');
    render('course_detail', ['title' => $c['title'] . ' — Redway Academy', 'course' => $c,
        'is_enrolled' => $enrolled, 'can_view_lessons' => $can_view,
        'lessons' => $can_view ? q('SELECT lesson_no, title FROM lessons WHERE course_id=? ORDER BY lesson_no', [(int)$c['id']]) : []]);
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
    $total = (int)q1('SELECT COUNT(*) AS n FROM lessons WHERE course_id=?', [(int)$c['id']])['n'];
    render('lesson', ['title' => $lesson['title'] . ' — ' . $c['title'],
        'course' => $c, 'lesson' => $lesson, 'total' => $total]);
}
if ($path === '/testimonials') {
    if ($method === 'POST') {
        run("INSERT INTO testimonials (author_name, role, class_level, message, status, created_at) VALUES (?,?,?,?,'pending',?)",
            [post('author_name'), post('role', 'Student'), post('class_level'), post('message'), now_str()]);
        flash('Thank you! Your endorsement was sent for admin approval and will be published after review.');
        redirect('/testimonials');
    }
    render('testimonials', ['title' => 'Reviews — Redway Academy',
        'testimonials' => q("SELECT * FROM testimonials WHERE status='approved' ORDER BY id DESC")]);
}

// --- Auth -------------------------------------------------------------------
if ($path === '/register') {
    if ($method === 'POST') {
        $name = post('name'); $phone = digits_only(post('phone')); $password = $_POST['password'] ?? '';
        $role = in_array(post('role'), ['student', 'parent', 'teacher'], true) ? post('role') : 'student';
        $class_level = post('class_level'); $linked = digits_only(post('linked_student_phone'));
        if ($name === '' || strlen($phone) < 11 || strlen($password) < 4) {
            flash('Please give your name, a valid phone number (11 digits) and a password of 4+ characters.');
            redirect('/register');
        }
        if ($role === 'teacher') {
            $existing = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn();
            if ($existing > 0) { flash('Teacher accounts are added by an admin. Please ask a teacher / admin to add you.'); redirect('/login'); }
        }
        $is_first_teacher = $role === 'teacher' && (int)db()->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn() === 0;
        try {
            run('INSERT INTO users (name, phone, password_hash, role, class_level, linked_student_phone, is_super_admin, created_at) VALUES (?,?,?,?,?,?,?,?)',
                [$name, $phone, password_hash($password, PASSWORD_DEFAULT), $role, $class_level, $linked ?: null, $is_first_teacher ? 1 : 0, now_str()]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate') || $e->getCode() === '23000') {
                flash('This phone number is already registered. Please log in.'); redirect('/login');
            }
            throw $e;
        }
        flash("Welcome, $name! You are logged in as $role.");
        redirect('/dashboard');
    }
    render('register', ['title' => 'Register — Redway Academy']);
}
if ($path === '/login') {
    if ($method === 'POST') {
        $phone = digits_only(post('phone')); $password = $_POST['password'] ?? '';
        if (login_blocked($phone)) {
            flash('Too many wrong attempts. Please wait 15 minutes and try again.');
            render('login', ['title' => 'Login — Redway Academy']); exit;
        }
        $u = q1('SELECT * FROM users WHERE phone=?', [$phone]);
        if ($u && password_verify($password, $u['password_hash'])) {
            if ((int)($u['active'] ?? 1) === 0) {
                flash('This account has been deactivated. Please contact Redway Academy.');
                render('login', ['title' => 'Login — Redway Academy']); exit;
            }
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            flash('Welcome back, ' . $u['name'] . '!');
            redirect('/dashboard');
        }
        record_failed_login($phone);
        flash('Wrong phone number or password.');
    }
    render('login', ['title' => 'Login — Redway Academy']);
}
if ($path === '/logout' && $method === 'GET') {
    session_destroy(); start_session(); redirect('/');
}

// --- Dashboard / progress / feedback / notifications ------------------------
if ($path === '/dashboard' && $method === 'GET') {
    $u = login_required();
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
        $linked = $u['linked_student_phone'] ? q1('SELECT name FROM users WHERE phone=? AND role=?', [$u['linked_student_phone'], 'student']) : null;
        $ctx += ['progress' => q('SELECT * FROM progress_notes WHERE student_phone=? ORDER BY id DESC', [$phone]),
            'orders' => q('SELECT * FROM orders WHERE student_phone=? ORDER BY id DESC', [$phone]),
            'checkins' => q('SELECT * FROM checkins WHERE student_phone=? ORDER BY id DESC LIMIT 10', [$phone]),
            'my_courses' => enrolled_courses($phone),
            'linked_student_name' => $linked['name'] ?? null];
    } else {
        $ctx += ['progress' => q('SELECT * FROM progress_notes WHERE student_phone=? ORDER BY id DESC', [$u['phone']]),
            'orders' => q('SELECT * FROM orders WHERE student_phone=? ORDER BY id DESC', [$u['phone']]),
            'homework' => q('SELECT * FROM homework ORDER BY id DESC'),
            'my_submissions' => q('SELECT * FROM submissions WHERE student_phone=? ORDER BY id DESC', [$u['phone']]),
            'my_courses' => enrolled_courses($u['phone'])];
    }
    $ctx['notifications'] = q('SELECT * FROM notifications WHERE recipient_phone=? ORDER BY id DESC LIMIT 10', [$u['phone']]);
    render('dashboard', $ctx);
}
if ($path === '/progress') {
    $u = login_required();
    if ($method === 'POST' && $u['role'] === 'teacher') {
        $s_phone = digits_only(post('student_phone')); $s_name = post('student_name'); $score = (int)post('score');
        run('INSERT INTO progress_notes (student_phone, student_name, score, suggestion, teacher_phone, created_at) VALUES (?,?,?,?,?,?)',
            [$s_phone, $s_name, $score, post('suggestion'), $u['phone'], now_str()]);
        notify($s_phone, "New progress update from your teacher: score $score.", 'progress');
        notify_parents_of($s_phone, "New progress update for $s_name: score $score.", 'progress');
        flash('Progress note added and the student / parent were notified.');
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
        flash('Thank you for your feedback!');
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
        flash('Homework created and students were notified.');
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
    flash('Homework submitted. Thank you!');
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
    if ($method === 'POST') {
        $trxid = strtoupper(post('trxid'));
        if ($trxid === '') { flash('Please enter your bKash / Nagad Transaction ID.'); redirect('/buy/' . $course['id']); }
        $method_pay = post('method', 'bKash');
        run('INSERT INTO orders (course_id, course_title, student_phone, student_name, method, trxid, amount_bdt, created_at) VALUES (?,?,?,?,?,?,?,?)',
            [$course['id'], $course['title'], $u['phone'], $u['name'], $method_pay, $trxid, $course['price_bdt'], now_str()]);
        foreach (q("SELECT phone FROM users WHERE role='teacher'") as $t) {
            notify($t['phone'], "New $method_pay payment to verify: {$course['title']}, TrxID $trxid, ৳{$course['price_bdt']}.", 'payment');
        }
        flash('Order submitted! A teacher will verify your payment soon.');
        redirect('/dashboard');
    }
    render('buy', ['title' => 'Buy — ' . $course['title'], 'course' => $course]);
}
if (preg_match('#^/order/(\d+)/verify$#', $path, $m) && $method === 'POST') {
    teacher_required();
    $status = post('status', 'verified');
    run('UPDATE orders SET status=? WHERE id=?', [$status, (int)$m[1]]);
    $row = q1('SELECT * FROM orders WHERE id=?', [(int)$m[1]]);
    if ($row) {
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
        flash('Registration submitted — status: ' . str_replace('_', ' ', $status) . ". $reasons");
        redirect('/retake/' . $id);
    }
    render('retake', ['title' => 'Free Retake Registration — Redway Academy']);
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
        flash("Check-in PIN created: $pin. Share it in class — students enter it, you confirm, parents are notified.");
        redirect('/checkin');
    }
    if ($method === 'POST') {
        $pin = post('pin');
        $code = q1('SELECT * FROM checkin_codes WHERE pin=? AND is_active=1', [$pin]);
        if (!$code) { flash('Wrong or expired PIN. Ask your teacher for the class PIN.'); redirect('/checkin'); }
        run('INSERT INTO checkins (student_phone, student_name, class_level, pin, created_at) VALUES (?,?,?,?,?)',
            [$u['phone'], $u['name'], $code['class_level'], $pin, now_str()]);
        flash('Check-in requested. Your teacher will confirm your arrival.');
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
    flash('Arrival confirmed and the parent was notified.');
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
if ($path === '/admin/courses' && $method === 'GET') {
    admin_required();
    render('admin/courses', ['title' => 'Admin — Courses', 'courses' => q('SELECT * FROM courses ORDER BY id')]);
}
if ($path === '/admin/courses/new') {
    admin_required();
    if ($method === 'POST') {
        run('INSERT INTO courses (title, class_level, paper, price_bdt, lessons, description, active) VALUES (?,?,?,?,?,?,1)',
            [post('title'), post('class_level'), post('paper', '1st Paper'), (int)post('price_bdt'), (int)post('lessons'), post('description')]);
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
        run('UPDATE courses SET title=?, class_level=?, paper=?, price_bdt=?, lessons=?, description=?, active=? WHERE id=?',
            [post('title'), post('class_level'), post('paper', '1st Paper'), (int)post('price_bdt'), (int)post('lessons'), post('description'), isset($_POST['active']) ? 1 : 0, (int)$m[1]]);
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
    $rows = in_array($status, ['pending', 'verified', 'rejected'], true)
        ? q('SELECT * FROM orders WHERE status=? ORDER BY id DESC', [$status])
        : q('SELECT * FROM orders ORDER BY id DESC');
    render('admin/orders', ['title' => 'Admin — Orders', 'orders' => $rows, 'status' => $status]);
}
if (preg_match('#^/admin/orders/(\d+)/verify$#', $path, $m) && $method === 'POST') {
    admin_required();
    $status = post('status', 'verified');
    if (!in_array($status, ['verified', 'rejected'], true)) abort_page(400, 'Invalid status.');
    run('UPDATE orders SET status=? WHERE id=?', [$status, (int)$m[1]]);
    $row = q1('SELECT * FROM orders WHERE id=?', [(int)$m[1]]);
    if ($row) {
        notify($row['student_phone'], "Your payment for {$row['course_title']} was $status (TrxID {$row['trxid']}).", 'payment');
        notify_parents_of($row['student_phone'], "Payment for {$row['student_name']} — {$row['course_title']} — was $status.", 'payment');
    }
    flash("Order marked $status.");
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
