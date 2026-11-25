<?php
/**
 * Redway Academy — PDO database layer.
 * Same code works with MySQL (Hostinger) and SQLite (local test).
 * Tables are created automatically if missing; the 11 courses are seeded.
 */
require_once __DIR__ . '/config.php';

function db_dsn(): string {
    if (DB_DSN !== '') return DB_DSN;
    if (DB_NAME !== '') {
        return 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    }
    // Local fallback: SQLite file next to the project.
    return 'sqlite:' . dirname(__DIR__) . '/storage/redway.db';
}

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = db_dsn();
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = str_starts_with($dsn, 'sqlite:')
        ? new PDO($dsn, null, null, $opts)
        : new PDO($dsn, DB_USER, DB_PASS, $opts);
    if (str_starts_with($dsn, 'sqlite:')) {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }
    init_db($pdo);
    return $pdo;
}

function db_driver(): string {
    return str_starts_with(db_dsn(), 'sqlite:') ? 'sqlite' : 'mysql';
}

/** The 11 seeded courses: Class 6,7,8,9-10,11-12 x 1st/2nd Paper + Spoken English.
 * 7th element is the track: 'academic' (English for Today + Grammar &
 * Composition together) or 'spoken' (Spoken English — for all). */
function seed_courses(): array {
    return [
        ['English for Today — Class 6 (1st Paper)', '6', '1st Paper', 699, 20, 'NCTB English for Today, Class 6: reading, vocabulary, speaking and writing.', 'academic'],
        ['English 2nd Paper — Class 6 (Grammar and Composition)', '6', '2nd Paper', 699, 20, 'NCTB English Grammar and Composition, Class 6: nouns, verbs, tenses and sentences.', 'academic'],
        ['English for Today — Class 7 (1st Paper)', '7', '1st Paper', 699, 20, 'NCTB English for Today, Class 7: reading, vocabulary, speaking and writing.', 'academic'],
        ['English 2nd Paper — Class 7 (Grammar and Composition)', '7', '2nd Paper', 699, 20, 'NCTB English Grammar and Composition, Class 7: grammar rules, practice and writing.', 'academic'],
        ['English for Today — Class 8 (1st Paper)', '8', '1st Paper', 799, 22, 'NCTB English for Today, Class 8: reading, vocabulary, speaking and writing.', 'academic'],
        ['English 2nd Paper — Class 8 (Grammar and Composition)', '8', '2nd Paper', 799, 22, 'NCTB English Grammar and Composition, Class 8: grammar rules, practice and writing.', 'academic'],
        ['English for Today — Class 9-10 (1st Paper)', '9-10', '1st Paper', 999, 24, 'NCTB English for Today, Classes 9-10 (SSC): seen, unseen, literature and writing.', 'academic'],
        ['English 2nd Paper — Class 9-10 (Grammar and Composition)', '9-10', '2nd Paper', 999, 24, 'NCTB English Grammar and Composition, Classes 9-10 (SSC): The Noun, Tenses, Modals, Conditionals, Passive, Speech, plus Letters, CV, E-mails, Paragraphs, Completing Stories, Graphs & Charts.', 'academic'],
        ['English for Today — Class 11-12 (1st Paper)', '11-12', '1st Paper', 1199, 24, 'NCTB English for Today, Classes 11-12 (HSC, 1st Paper): reading and writing.', 'academic'],
        ['English 2nd Paper — Class 11-12 (Grammar and Composition)', '11-12', '2nd Paper', 1199, 24, 'NCTB Grammar & Composition, Classes 11-12 (HSC, 2nd Paper): grammar and writing.', 'academic'],
        ['Spoken English — Speak with Confidence', '6-12', 'Spoken English', 1499, 24, "Redway's own speaking course: conversation, pronunciation, fluency, group discussion, presentations and interview English, with teacher feedback.", 'spoken'],
    ];
}

function init_db(PDO $pdo): void {
    $mysql = str_starts_with(db_dsn(), 'mysql:');
    $id = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $ai = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $int = 'INTEGER';
    $txt = $mysql ? 'TEXT' : 'TEXT';
    $bool = $mysql ? 'TINYINT(1)' : 'INTEGER';
    $ts = $mysql ? 'VARCHAR(20)' : 'TEXT';
    $vc = fn($n) => $mysql ? "VARCHAR($n)" : 'TEXT';

    $tables = [
        "CREATE TABLE IF NOT EXISTS users (id $ai, name $txt NOT NULL, phone {$vc(20)} NOT NULL UNIQUE, password_hash $txt NOT NULL, role {$vc(20)} NOT NULL, class_level {$vc(20)}, linked_student_phone {$vc(20)}, active $bool NOT NULL DEFAULT 1, is_super_admin $bool NOT NULL DEFAULT 0, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS courses (id $ai, title $txt NOT NULL, class_level {$vc(20)}, paper {$vc(30)}, track {$vc(20)} NOT NULL DEFAULT 'academic', price_bdt $int, lessons $int, description $txt, active $bool NOT NULL DEFAULT 1)",
        "CREATE TABLE IF NOT EXISTS homework (id $ai, title $txt NOT NULL, class_level {$vc(20)}, details $txt, teacher_phone {$vc(20)}, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS submissions (id $ai, homework_id $int, student_phone {$vc(20)}, student_name $txt, answer $txt, file_link $txt, status {$vc(20)} DEFAULT 'submitted', teacher_comment $txt, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS progress_notes (id $ai, student_phone {$vc(20)}, student_name $txt, score $int, suggestion $txt, teacher_phone {$vc(20)}, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS feedback (id $ai, author_phone {$vc(20)}, author_name $txt, message $txt, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS testimonials (id $ai, author_name $txt, role {$vc(20)}, class_level {$vc(20)}, message $txt, status {$vc(20)} DEFAULT 'pending', created_at $ts)",
        "CREATE TABLE IF NOT EXISTS orders (id $ai, course_id $int, course_title $txt, student_phone {$vc(20)}, student_name $txt, method {$vc(20)}, trxid {$vc(40)}, sender_phone {$vc(20)}, amount_bdt $int, status {$vc(20)} DEFAULT 'pending', created_at $ts)",
        "CREATE TABLE IF NOT EXISTS retake_registrations (id $ai, student_name $txt, phone {$vc(20)}, exam {$vc(20)}, exam_year {$vc(10)}, roll_no {$vc(40)}, reg_no {$vc(40)}, result_text $txt, admit_file $txt, tabulation_file $txt, status {$vc(20)} DEFAULT 'needs_review', reasons $txt, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS notifications (id $ai, recipient_phone {$vc(20)}, message $txt, kind {$vc(20)}, is_read $bool NOT NULL DEFAULT 0, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS checkin_codes (id $ai, pin {$vc(10)} NOT NULL, class_level {$vc(20)}, teacher_phone {$vc(20)}, is_active $bool NOT NULL DEFAULT 1, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS checkins (id $ai, student_phone {$vc(20)}, student_name $txt, class_level {$vc(20)}, pin {$vc(10)}, status {$vc(20)} DEFAULT 'pending', created_at $ts)",
        "CREATE TABLE IF NOT EXISTS announcements (id $ai, teacher_phone {$vc(20)}, message $txt, created_at $ts)",
        "CREATE TABLE IF NOT EXISTS lessons (id $ai, course_id $int, lesson_no $int, title $txt NOT NULL, body $txt NOT NULL, created_at $ts)",
        // Login rate-limit: 5 failed attempts per phone in 15 minutes (DB-backed, survives restart).
        "CREATE TABLE IF NOT EXISTS login_attempts (id $ai, phone {$vc(20)}, attempted_at $ts)",
        // Persistent "remember me" logins: selector + hashed validator, 30 days.
        // The validator itself is never stored — only its HMAC-SHA256 hash.
        "CREATE TABLE IF NOT EXISTS remember_tokens (id $ai, user_id $int NOT NULL, selector {$vc(32)} NOT NULL UNIQUE, token_hash $txt NOT NULL, expires_at $int NOT NULL, created_at $int NOT NULL)",
        // Key-value settings: monthly subscription fees per track and the
        // free-retake campaign switch. Editable from the Admin Panel.
        "CREATE TABLE IF NOT EXISTS settings (k {$vc(64)} NOT NULL PRIMARY KEY, v $txt)",
    ];
    foreach ($tables as $sql) $pdo->exec($sql);

    // Light migrations for databases created by an earlier version.
    $cols = ['users' => ['active', 'is_super_admin'], 'courses' => ['active', 'track'], 'testimonials' => ['status'], 'orders' => ['sender_phone', 'plan', 'track', 'valid_until']];
    foreach ($cols as $table => $names) {
        $existing = [];
        if ($mysql) {
            foreach ($pdo->query("SHOW COLUMNS FROM `$table`") as $r) $existing[] = $r['Field'];
        } else {
            foreach ($pdo->query("PRAGMA table_info($table)") as $r) $existing[] = $r['name'];
        }
        foreach ($names as $name) {
            if (!in_array($name, $existing, true)) {
                $def = $name === 'status' ? "TEXT DEFAULT 'approved'" : ($mysql ? 'TINYINT(1) NOT NULL DEFAULT 1' : 'INTEGER NOT NULL DEFAULT 1');
                if ($name === 'is_super_admin') $def = $mysql ? 'TINYINT(1) NOT NULL DEFAULT 0' : 'INTEGER NOT NULL DEFAULT 0';
                if ($name === 'sender_phone') $def = $mysql ? 'VARCHAR(20)' : 'TEXT';
                if ($name === 'track') $def = $mysql ? "VARCHAR(20) NOT NULL DEFAULT 'academic'" : "TEXT NOT NULL DEFAULT 'academic'";
                if ($name === 'plan') $def = $mysql ? "VARCHAR(20) NOT NULL DEFAULT 'once'" : "TEXT NOT NULL DEFAULT 'once'";
                if ($name === 'valid_until') $def = $mysql ? 'VARCHAR(20)' : 'TEXT';
                $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$name` $def");
            }
        }
    }

    // Backfill the track for courses created before the track column existed.
    // Derive it from `paper` unconditionally and idempotently: the ALTER
    // adds the column with DEFAULT 'academic', so on an existing database
    // every old row already has track='academic' (never NULL/empty) and a
    // NULL/empty-only backfill would silently match zero rows — which is
    // exactly how the Spoken course ended up counted as Academic live.
    // Rows with any other paper value are left untouched.
    $pdo->exec("UPDATE courses SET track='spoken' WHERE paper='Spoken English'");
    $pdo->exec("UPDATE courses SET track='academic' WHERE paper IN ('1st Paper','2nd Paper')");

    // Seed the settings defaults (only when the key is missing — an admin's
    // saved values are never overwritten). The live monthly fee is ৳1450
    // (live classes 3 days a week on Google Meet / Zoom); the recorded
    // class fee is not set yet (0 = not set) — the owner fills it in later
    // from the Admin Panel.
    $seed_settings = [
        'subscription_fee_live' => '1450',
        'recorded_fee' => '0',
        'live_days_per_week' => '3',
        'retake_campaign_active' => '1',
    ];
    foreach ($seed_settings as $k => $v) {
        $st = $pdo->prepare('SELECT COUNT(*) FROM settings WHERE k=?');
        $st->execute([$k]);
        if ((int)$st->fetchColumn() === 0) {
            $ins = $pdo->prepare('INSERT INTO settings (k, v) VALUES (?,?)');
            $ins->execute([$k, $v]);
        }
    }

    // Seed courses once.
    $count = (int)$pdo->query('SELECT COUNT(*) FROM courses')->fetchColumn();
    if ($count === 0) {
        $st = $pdo->prepare('INSERT INTO courses (title, class_level, paper, price_bdt, lessons, description, track) VALUES (?,?,?,?,?,?,?)');
        foreach (seed_courses() as $c) $st->execute($c);
    }
    // Seed lessons once (only when the lessons table is empty).
    require_once __DIR__ . '/lessons_seed.php';
    seed_lessons($pdo);
    // Spoken English lecture sheets v2 — refresh old short bodies.
    // seed_lessons() only runs on an empty lessons table, so databases
    // created before v2 still hold the old one-paragraph Spoken bodies.
    // When the version flag is not yet '2', rewrite the Spoken course's
    // 24 lesson bodies (matched by course + lesson_no) and set the flag.
    // Idempotent: running it again writes the same text. Academic
    // lessons are never touched here.
    $flag_st = $pdo->prepare('SELECT v FROM settings WHERE k=?');
    $flag_st->execute(['lessons_content_version']);
    $flag = $flag_st->fetch();
    if (!$flag || (string)$flag['v'] !== '2') {
        $spoken = $pdo->query("SELECT id FROM courses WHERE paper='Spoken English' ORDER BY id LIMIT 1")->fetch();
        if ($spoken) {
            $upd = $pdo->prepare('UPDATE lessons SET body=? WHERE course_id=? AND lesson_no=?');
            for ($n = 1; $n <= 24; $n++) {
                $upd->execute([spoken_lecture_sheet($n), (int)$spoken['id'], $n]);
            }
        }
        $upsert = str_starts_with(db_dsn(), 'mysql:')
            ? "INSERT INTO settings (k, v) VALUES ('lessons_content_version','2') ON DUPLICATE KEY UPDATE v=VALUES(v)"
            : "INSERT INTO settings (k, v) VALUES ('lessons_content_version','2') ON CONFLICT(k) DO UPDATE SET v=excluded.v";
        $pdo->prepare($upsert)->execute();
    }
    // Backfill: the earliest teacher is the Super Admin.
    $first = $pdo->query("SELECT id FROM users WHERE role='teacher' ORDER BY id LIMIT 1")->fetch();
    if ($first) $pdo->exec('UPDATE users SET is_super_admin=1 WHERE id=' . (int)$first['id']);
}
