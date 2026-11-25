"""
Redway Academy — Flask app
Mistakes Mark the Way / ভুল থেকেই শেখা, ভুল করেই জেতা
NCTB English for High School (Class 6-10) and College (Class 11-12) only.
SQLite, Flask only. No facial recognition / biometric identification.
Offline arrival uses a teacher-confirmed QR / PIN check-in.
"""
import logging
import os
import re
import secrets
import sqlite3
import time
from datetime import datetime, timedelta
from functools import wraps

from flask import (
    Flask, g, redirect, render_template, request, session,
    url_for, flash, abort, send_from_directory, Response,
)
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename

from config import Config

app = Flask(__name__)
app.config.from_object(Config)
if app.config["SECRET_KEY"] == "redway-dev-secret-change-me":
    app.logger.warning(
        "SECRET_KEY is the dev fallback — set a real SECRET_KEY in production!")
app.permanent_session_lifetime = app.config["PERMANENT_SESSION_LIFETIME"]
app.config["MAX_CONTENT_LENGTH"] = app.config["MAX_CONTENT_LENGTH"]
UPLOAD_FOLDER = app.config["UPLOAD_FOLDER"]
DATABASE_PATH = app.config["DATABASE"]
ALLOWED_EXTENSIONS = {"pdf", "jpg", "jpeg", "png"}

# --- Logging ---------------------------------------------------------------
# Log to instance/logs/redway.log (and to the console). Never log passwords.
try:
    os.makedirs(os.path.dirname(app.config["LOG_FILE"]), exist_ok=True)
    _handler = logging.FileHandler(app.config["LOG_FILE"])
    _handler.setFormatter(logging.Formatter(
        "%(asctime)s %(levelname)s %(message)s"))
    _handler.setLevel(logging.INFO)
    app.logger.addHandler(_handler)
    app.logger.setLevel(logging.INFO)
except OSError:
    pass  # read-only filesystem in some hosts; console logging still works.

# --- Security headers -------------------------------------------------------
@app.after_request
def security_headers(response):
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
    response.headers["Permissions-Policy"] = "camera=(), microphone=(), geolocation=()"
    # Allow our own site, Google Fonts, and inline styles used by templates.
    response.headers["Content-Security-Policy"] = (
        "default-src 'self'; "
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        "font-src 'self' https://fonts.gstatic.com; "
        "img-src 'self' data:; script-src 'self'; "
        "frame-ancestors 'none'"
    )
    return response

# --- CSRF-style tokens ------------------------------------------------------
# A per-session token. Admin POST routes require it strictly. Other POST
# routes accept a request without a token (backwards compatible) but
# reject a request that carries a wrong token. The token is added to
# every form automatically by static/js/app.js, and admin templates
# include it as a hidden field as well.
def _csrf_token():
    if "_csrf_token" not in session:
        session["_csrf_token"] = secrets.token_hex(16)
    return session["_csrf_token"]

def _csrf_valid():
    token = request.form.get("csrf_token", "")
    return bool(token) and token == session.get("_csrf_token")

@app.before_request
def _csrf_check():
    if request.method != "POST":
        return None
    if request.path.startswith("/admin"):
        if not _csrf_valid():
            app.logger.warning("CSRF check failed on %s", request.path)
            abort(403)
    elif request.form.get("csrf_token") and not _csrf_valid():
        app.logger.warning("CSRF check failed on %s", request.path)
        abort(403)
    return None

# --- Simple login rate limiting --------------------------------------------
# Max 5 failed logins per phone number in 15 minutes. In-memory only;
# it resets on restart, which is fine for a small school site.
_LOGIN_ATTEMPTS = {}
_LOGIN_LIMIT = 5
_LOGIN_WINDOW = 15 * 60

def _login_blocked(phone):
    now = time.time()
    attempts = [t for t in _LOGIN_ATTEMPTS.get(phone, []) if now - t < _LOGIN_WINDOW]
    _LOGIN_ATTEMPTS[phone] = attempts
    return len(attempts) >= _LOGIN_LIMIT

def _record_failed_login(phone):
    _LOGIN_ATTEMPTS.setdefault(phone, []).append(time.time())

# --- Error pages ------------------------------------------------------------
@app.errorhandler(404)
def page_not_found(error):
    return render_template("error.html", code=404,
                           message="Sorry — that page does not exist."), 404

@app.errorhandler(403)
def forbidden(error):
    return render_template("error.html", code=403,
                           message="You are not allowed to do that. Please log in as a teacher / admin."), 403

@app.errorhandler(413)
def too_large(error):
    return render_template("error.html", code=413,
                           message="That file is too large. Uploads must be under 8 MB."), 413

@app.errorhandler(500)
def server_error(error):
    app.logger.exception("Server error on %s", request.path)
    return render_template("error.html", code=500,
                           message="Something went wrong on our side. Please try again later."), 500

# --- Health check / robots / sitemap ----------------------------------------
@app.route("/health")
def health():
    try:
        get_db().execute("SELECT 1").fetchone()
        return {"status": "ok", "app": "redway-academy"}, 200
    except Exception:  # noqa: BLE001 - health check must report failure
        app.logger.exception("Health check failed")
        return {"status": "error"}, 500

@app.route("/robots.txt")
def robots_txt():
    return Response("User-agent: *\nAllow: /\nSitemap: https://redwayacademy.com/sitemap.xml\n",
                    mimetype="text/plain")

@app.route("/sitemap.xml")
def sitemap():
    db = get_db()
    urls = ["https://redwayacademy.com/",
            "https://redwayacademy.com/courses",
            "https://redwayacademy.com/retake",
            "https://redwayacademy.com/testimonials",
            "https://redwayacademy.com/login",
            "https://redwayacademy.com/register"]
    for c in db.execute("SELECT id FROM courses").fetchall():
        urls.append(f"https://redwayacademy.com/course/{c['id']}")
    body = "\n".join(
        f"  <url><loc>{u}</loc></url>" for u in urls)
    return Response(
        f'<?xml version="1.0" encoding="UTF-8"?>\n'
        f'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n{body}\n</urlset>',
        mimetype="application/xml")


# --- Brand / courses -------------------------------------------------------
COURSES = [
    # (title, class_level, paper, price_bdt, lessons, description)
    ("English for Today — Class 6 (1st Paper)", "6", "1st Paper", 699, 20,
     "NCTB English for Today, Class 6: reading, vocabulary, speaking and writing."),
    ("English 2nd Paper — Class 6 (Grammar and Composition)", "6", "2nd Paper", 699, 20,
     "NCTB English Grammar and Composition, Class 6: nouns, verbs, tenses and sentences."),
    ("English for Today — Class 7 (1st Paper)", "7", "1st Paper", 699, 20,
     "NCTB English for Today, Class 7: reading, vocabulary, speaking and writing."),
    ("English 2nd Paper — Class 7 (Grammar and Composition)", "7", "2nd Paper", 699, 20,
     "NCTB English Grammar and Composition, Class 7: grammar rules, practice and writing."),
    ("English for Today — Class 8 (1st Paper)", "8", "1st Paper", 799, 22,
     "NCTB English for Today, Class 8: reading, vocabulary, speaking and writing."),
    ("English 2nd Paper — Class 8 (Grammar and Composition)", "8", "2nd Paper", 799, 22,
     "NCTB English Grammar and Composition, Class 8: grammar rules, practice and writing."),
    ("English for Today — Class 9-10 (1st Paper)", "9-10", "1st Paper", 999, 24,
     "NCTB English for Today, Classes 9-10 (SSC): seen, unseen, literature and writing."),
    ("English 2nd Paper — Class 9-10 (Grammar and Composition)", "9-10", "2nd Paper", 999, 24,
     "NCTB English Grammar and Composition, Classes 9-10 (SSC): The Noun, Tenses, Modals, "
     "Conditionals, Passive, Speech, plus Letters, CV, E-mails, Paragraphs, Completing Stories, "
     "Graphs & Charts."),
    ("English for Today — Class 11-12 (1st Paper)", "11-12", "1st Paper", 1199, 24,
     "NCTB English for Today, Classes 11-12 (HSC, 1st Paper): reading and writing."),
    ("English 2nd Paper — Class 11-12 (Grammar and Composition)", "11-12", "2nd Paper", 1199, 24,
     "NCTB Grammar & Composition, Classes 11-12 (HSC, 2nd Paper): grammar and writing."),
    ("Spoken English — Speak with Confidence", "6-12", "Spoken English", 1499, 24,
     "Redway's own speaking course: conversation, pronunciation, fluency, group discussion, "
     "presentations and interview English, with teacher feedback."),
]

# --- Database ---------------------------------------------------------------
def get_db():
    if "db" not in g:
        os.makedirs(os.path.dirname(DATABASE_PATH), exist_ok=True)
        g.db = sqlite3.connect(DATABASE_PATH)
        g.db.row_factory = sqlite3.Row
    return g.db

@app.teardown_appcontext
def close_db(exc=None):
    db = g.pop("db", None)
    if db is not None:
        db.close()

def init_db():
    db = get_db()
    db.executescript("""
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL, phone TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL, role TEXT NOT NULL,
        class_level TEXT, linked_student_phone TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS courses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL, class_level TEXT, paper TEXT,
        price_bdt INTEGER, lessons INTEGER, description TEXT
    );
    CREATE TABLE IF NOT EXISTS homework (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL, class_level TEXT, details TEXT,
        teacher_phone TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS submissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        homework_id INTEGER, student_phone TEXT, student_name TEXT,
        answer TEXT, file_link TEXT, status TEXT DEFAULT 'submitted',
        teacher_comment TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS progress_notes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_phone TEXT, student_name TEXT, score INTEGER,
        suggestion TEXT, teacher_phone TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS feedback (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        author_phone TEXT, author_name TEXT, message TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS testimonials (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        author_name TEXT, role TEXT, class_level TEXT, message TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        course_id INTEGER, course_title TEXT, student_phone TEXT, student_name TEXT,
        method TEXT, trxid TEXT, amount_bdt INTEGER, status TEXT DEFAULT 'pending',
        created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS retake_registrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_name TEXT, phone TEXT, exam TEXT, exam_year TEXT,
        roll_no TEXT, reg_no TEXT, result_text TEXT,
        admit_file TEXT, tabulation_file TEXT,
        status TEXT DEFAULT 'needs_review', reasons TEXT, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipient_phone TEXT, message TEXT, kind TEXT,
        is_read INTEGER DEFAULT 0, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS checkin_codes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        pin TEXT NOT NULL, class_level TEXT, teacher_phone TEXT,
        is_active INTEGER DEFAULT 1, created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS checkins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_phone TEXT, student_name TEXT, class_level TEXT,
        pin TEXT, status TEXT DEFAULT 'pending', created_at TEXT
    );
    CREATE TABLE IF NOT EXISTS announcements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        teacher_phone TEXT, message TEXT, created_at TEXT
    );
    """)
    if db.execute("SELECT COUNT(*) FROM courses").fetchone()[0] == 0:
        db.executemany(
            "INSERT INTO courses (title, class_level, paper, price_bdt, lessons, description) "
            "VALUES (?, ?, ?, ?, ?, ?)", COURSES)
    # --- Light migrations for databases created by earlier versions --------
    # Add columns if they do not exist yet (SQLite has no ADD COLUMN IF NOT EXISTS).
    _cols = {
        "users": ["active", "is_super_admin"],
        "courses": ["active"],
        "testimonials": ["status"],
    }
    for _table, _names in _cols.items():
        _existing = {r[1] for r in db.execute(f"PRAGMA table_info({_table})")}
        for _name in _names:
            if _name not in _existing:
                _default = "1" if _name in ("active", "is_super_admin") else "'approved'"
                # is_super_admin must default to 0, not 1.
                if _name == "is_super_admin":
                    _default = "0"
                db.execute(f"ALTER TABLE {_table} ADD COLUMN {_name} "
                           f"{'INTEGER' if _name != 'status' else 'TEXT'} DEFAULT {_default}")
    # Backfill: the earliest teacher is the Super Admin.
    _first_teacher = db.execute(
        "SELECT id FROM users WHERE role = 'teacher' ORDER BY id LIMIT 1").fetchone()
    if _first_teacher:
        db.execute("UPDATE users SET is_super_admin = 1 WHERE id = ?",
                   (_first_teacher["id"],))
    db.commit()

with app.app_context():
    init_db()

# --- Helpers ----------------------------------------------------------------
def now():
    return datetime.now().strftime("%Y-%m-%d %H:%M")

def current_user():
    if "user_id" not in session:
        return None
    user = get_db().execute("SELECT * FROM users WHERE id = ?",
                            (session["user_id"],)).fetchone()
    if user is None:
        return None
    # A deactivated account cannot stay logged in.
    try:
        if user["active"] == 0:
            session.clear()
            return None
    except (KeyError, IndexError):
        pass
    return user

def login_required(f):
    @wraps(f)
    def wrapper(*args, **kwargs):
        if not current_user():
            flash("Please log in first.")
            return redirect(url_for("login"))
        return f(*args, **kwargs)
    return wrapper

def teacher_required(f):
    @wraps(f)
    def wrapper(*args, **kwargs):
        u = current_user()
        if not u or u["role"] != "teacher":
            flash("Teacher login required.")
            return redirect(url_for("login"))
        return f(*args, **kwargs)
    return wrapper

def admin_required(f):
    """Admin Panel access: Teacher / Admin / Super Admin only."""
    @wraps(f)
    def wrapper(*args, **kwargs):
        u = current_user()
        if not u or u["role"] != "teacher":
            flash("Admin Panel needs a teacher / admin login.")
            return redirect(url_for("login"))
        return f(*args, **kwargs)
    return wrapper

def super_admin_required(f):
    """Only the Super Admin (the first teacher) can do this."""
    @wraps(f)
    def wrapper(*args, **kwargs):
        u = current_user()
        if not u or u["role"] != "teacher":
            flash("Admin Panel needs a teacher / admin login.")
            return redirect(url_for("login"))
        try:
            is_super = u["is_super_admin"] == 1
        except (KeyError, IndexError):
            is_super = False
        if not is_super:
            flash("Only the Super Admin can do that.")
            return redirect(url_for("admin_dashboard"))
        return f(*args, **kwargs)
    return wrapper

def is_super_admin(user):
    if not user:
        return False
    try:
        return user["is_super_admin"] == 1
    except (KeyError, IndexError):
        return False

def notify(recipient_phone, message, kind="info"):
    if not recipient_phone:
        return
    get_db().execute(
        "INSERT INTO notifications (recipient_phone, message, kind, created_at) VALUES (?, ?, ?, ?)",
        (recipient_phone, message, kind, now()))

def notify_parents_of(student_phone, message, kind="arrival"):
    """Notify every parent linked to this student, plus the student."""
    db = get_db()
    notify(student_phone, message, kind)
    parents = db.execute(
        "SELECT phone FROM users WHERE role = 'parent' AND linked_student_phone = ?",
        (student_phone,)).fetchall()
    for p in parents:
        notify(p["phone"], message, kind)

def allowed_file(name):
    return "." in name and name.rsplit(".", 1)[1].lower() in ALLOWED_EXTENSIONS

@app.context_processor
def inject_globals():
    u = current_user()
    unread = 0
    if u:
        unread = get_db().execute(
            "SELECT COUNT(*) FROM notifications WHERE recipient_phone = ? AND is_read = 0",
            (u["phone"],)).fetchone()[0]
    return {"current_user": u, "unread_count": unread,
            "csrf_token": _csrf_token(), "is_super_admin": is_super_admin(u)}

# --- Public routes ----------------------------------------------------------
@app.route("/")
def index():
    db = get_db()
    courses = db.execute(
        "SELECT * FROM courses WHERE active = 1 ORDER BY id").fetchall()
    testimonials = db.execute(
        "SELECT * FROM testimonials WHERE status = 'approved' "
        "ORDER BY id DESC LIMIT 6").fetchall()
    return render_template("index.html", courses=courses, testimonials=testimonials)

@app.route("/courses")
def courses():
    paper = request.args.get("paper", "All")
    db = get_db()
    if paper in ("1st Paper", "2nd Paper", "Spoken English"):
        rows = db.execute(
            "SELECT * FROM courses WHERE paper = ? AND active = 1 ORDER BY id",
            (paper,)).fetchall()
    else:
        rows = db.execute(
            "SELECT * FROM courses WHERE active = 1 ORDER BY id").fetchall()
    return render_template("courses.html", courses=rows, paper=paper)

@app.route("/course/<int:course_id>")
def course_detail(course_id):
    course = get_db().execute("SELECT * FROM courses WHERE id = ?", (course_id,)).fetchone()
    if not course:
        abort(404)
    return render_template("course_detail.html", course=course)

@app.route("/testimonials", methods=["GET", "POST"])
def testimonials():
    db = get_db()
    if request.method == "POST":
        db.execute(
            "INSERT INTO testimonials (author_name, role, class_level, message, status, created_at) "
            "VALUES (?, ?, ?, ?, 'pending', ?)",
            (request.form.get("author_name", "").strip(),
             request.form.get("role", "Student"),
             request.form.get("class_level", ""),
             request.form.get("message", "").strip(), now()))
        db.commit()
        flash("Thank you! Your endorsement was sent for admin approval and will be published after review.")
        return redirect(url_for("testimonials"))
    rows = db.execute(
        "SELECT * FROM testimonials WHERE status = 'approved' ORDER BY id DESC").fetchall()
    return render_template("testimonials.html", testimonials=rows)

# --- Auth: phone-number login ----------------------------------------------
@app.route("/register", methods=["GET", "POST"])
def register():
    db = get_db()
    if request.method == "POST":
        name = request.form.get("name", "").strip()
        phone = re.sub(r"\D", "", request.form.get("phone", ""))
        password = request.form.get("password", "")
        role = request.form.get("role", "student")
        class_level = request.form.get("class_level", "")
        linked = re.sub(r"\D", "", request.form.get("linked_student_phone", ""))
        if not name or len(phone) < 11 or len(password) < 4:
            flash("Please give your name, a valid phone number (11 digits) and a password of 4+ characters.")
            return redirect(url_for("register"))
        if role == "teacher":
            # First teacher becomes Super Admin; afterwards only an admin can add teachers.
            existing = db.execute("SELECT COUNT(*) FROM users WHERE role = 'teacher'").fetchone()[0]
            if existing > 0:
                flash("Teacher accounts are added by an admin. Please ask a teacher / admin to add you.")
                return redirect(url_for("login"))
        try:
            is_first_teacher = (
                role == "teacher"
                and db.execute("SELECT COUNT(*) FROM users WHERE role = 'teacher'").fetchone()[0] == 0)
            cur = db.execute(
                "INSERT INTO users (name, phone, password_hash, role, class_level, linked_student_phone, is_super_admin, created_at) "
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                (name, phone, generate_password_hash(password), role, class_level,
                 linked or None, 1 if is_first_teacher else 0, now()))
            db.commit()
        except sqlite3.IntegrityError:
            flash("This phone number is already registered. Please log in.")
            return redirect(url_for("login"))
        session.permanent = True
        session["user_id"] = cur.lastrowid
        flash(f"Welcome, {name}! You are logged in as {role}.")
        return redirect(url_for("dashboard"))
    return render_template("register.html")

@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        phone = re.sub(r"\D", "", request.form.get("phone", ""))
        password = request.form.get("password", "")
        if _login_blocked(phone):
            app.logger.warning("Login rate-limited for phone ending %s", phone[-4:] if phone else "?")
            flash("Too many wrong attempts. Please wait 15 minutes and try again.")
            return render_template("login.html")
        user = get_db().execute("SELECT * FROM users WHERE phone = ?", (phone,)).fetchone()
        if user and check_password_hash(user["password_hash"], password):
            try:
                if user["active"] == 0:
                    flash("This account has been deactivated. Please contact Redway Academy.")
                    return render_template("login.html")
            except (KeyError, IndexError):
                pass
            session.permanent = True
            session["user_id"] = user["id"]
            flash(f"Welcome back, {user['name']}!")
            return redirect(url_for("dashboard"))
        _record_failed_login(phone)
        flash("Wrong phone number or password.")
    return render_template("login.html")

@app.route("/logout")
def logout():
    session.clear()
    return redirect(url_for("index"))

# --- Dashboard / progress / feedback ---------------------------------------
@app.route("/dashboard")
@login_required
def dashboard():
    db = get_db()
    u = current_user()
    ctx = {"user": u}
    if u["role"] == "teacher":
        ctx["orders"] = db.execute("SELECT * FROM orders ORDER BY id DESC").fetchall()
        ctx["retakes"] = db.execute("SELECT * FROM retake_registrations ORDER BY id DESC").fetchall()
        ctx["submissions"] = db.execute("SELECT * FROM submissions ORDER BY id DESC LIMIT 20").fetchall()
        ctx["homework"] = db.execute("SELECT * FROM homework ORDER BY id DESC").fetchall()
        ctx["teachers"] = db.execute("SELECT * FROM users WHERE role = 'teacher' ORDER BY id").fetchall()
        ctx["students"] = db.execute("SELECT * FROM users WHERE role = 'student' ORDER BY id DESC LIMIT 50").fetchall()
    elif u["role"] == "parent":
        phone = u["linked_student_phone"] or u["phone"]
        ctx["progress"] = db.execute(
            "SELECT * FROM progress_notes WHERE student_phone = ? ORDER BY id DESC", (phone,)).fetchall()
        ctx["orders"] = db.execute(
            "SELECT * FROM orders WHERE student_phone = ? ORDER BY id DESC", (phone,)).fetchall()
        ctx["checkins"] = db.execute(
            "SELECT * FROM checkins WHERE student_phone = ? ORDER BY id DESC LIMIT 10", (phone,)).fetchall()
    else:
        ctx["progress"] = db.execute(
            "SELECT * FROM progress_notes WHERE student_phone = ? ORDER BY id DESC", (u["phone"],)).fetchall()
        ctx["orders"] = db.execute(
            "SELECT * FROM orders WHERE student_phone = ? ORDER BY id DESC", (u["phone"],)).fetchall()
        ctx["homework"] = db.execute("SELECT * FROM homework ORDER BY id DESC").fetchall()
        ctx["my_submissions"] = db.execute(
            "SELECT * FROM submissions WHERE student_phone = ? ORDER BY id DESC", (u["phone"],)).fetchall()
    ctx["notifications"] = db.execute(
        "SELECT * FROM notifications WHERE recipient_phone = ? ORDER BY id DESC LIMIT 10",
        (u["phone"],)).fetchall()
    return render_template("dashboard.html", **ctx)

@app.route("/progress", methods=["GET", "POST"])
@login_required
def progress():
    db = get_db()
    u = current_user()
    if request.method == "POST" and u["role"] == "teacher":
        s_phone = re.sub(r"\D", "", request.form.get("student_phone", ""))
        s_name = request.form.get("student_name", "").strip()
        db.execute(
            "INSERT INTO progress_notes (student_phone, student_name, score, suggestion, teacher_phone, created_at) "
            "VALUES (?, ?, ?, ?, ?, ?)",
            (s_phone, s_name, request.form.get("score"), request.form.get("suggestion", ""), u["phone"], now()))
        db.commit()
        notify(s_phone, f"New progress update from your teacher: score {request.form.get('score')}.", "progress")
        notify_parents_of(s_phone, f"New progress update for {s_name}: score {request.form.get('score')}.", "progress")
        flash("Progress note added and the student / parent were notified.")
        return redirect(url_for("dashboard"))
    phone = u["linked_student_phone"] if u["role"] == "parent" else u["phone"]
    rows = db.execute(
        "SELECT * FROM progress_notes WHERE student_phone = ? ORDER BY id DESC", (phone,)).fetchall()
    return render_template("progress.html", progress=rows)

@app.route("/feedback", methods=["GET", "POST"])
@login_required
def feedback():
    db = get_db()
    u = current_user()
    if request.method == "POST":
        db.execute(
            "INSERT INTO feedback (author_phone, author_name, message, created_at) VALUES (?, ?, ?, ?)",
            (u["phone"], u["name"], request.form.get("message", "").strip(), now()))
        db.commit()
        teachers = db.execute("SELECT phone FROM users WHERE role = 'teacher'").fetchall()
        for t in teachers:
            notify(t["phone"], f"New feedback from {u['name']} ({u['role']}).", "feedback")
        flash("Thank you for your feedback!")
        return redirect(url_for("dashboard"))
    rows = db.execute("SELECT * FROM feedback ORDER BY id DESC LIMIT 20").fetchall()
    return render_template("feedback.html", feedback=rows)

@app.route("/notifications")
@login_required
def notifications():
    db = get_db()
    u = current_user()
    db.execute("UPDATE notifications SET is_read = 1 WHERE recipient_phone = ?", (u["phone"],))
    db.commit()
    rows = db.execute(
        "SELECT * FROM notifications WHERE recipient_phone = ? ORDER BY id DESC", (u["phone"],)).fetchall()
    return render_template("notifications.html", notifications=rows)

@app.route("/announce", methods=["POST"])
@teacher_required
def announce():
    db = get_db()
    u = current_user()
    msg = request.form.get("message", "").strip()
    if msg:
        db.execute("INSERT INTO announcements (teacher_phone, message, created_at) VALUES (?, ?, ?)",
                   (u["phone"], msg, now()))
        users = db.execute("SELECT phone FROM users").fetchall()
        for row in users:
            notify(row["phone"], f"Announcement from Redway Academy: {msg}", "announcement")
        db.commit()
        flash("Announcement sent to all students and parents.")
    return redirect(url_for("dashboard"))

# --- Homework ---------------------------------------------------------------
@app.route("/homework", methods=["GET", "POST"])
@login_required
def homework():
    db = get_db()
    u = current_user()
    if request.method == "POST" and u["role"] == "teacher":
        db.execute(
            "INSERT INTO homework (title, class_level, details, teacher_phone, created_at) VALUES (?, ?, ?, ?, ?)",
            (request.form.get("title", "").strip(), request.form.get("class_level", ""),
             request.form.get("details", ""), u["phone"], now()))
        db.commit()
        students = db.execute("SELECT phone FROM users WHERE role = 'student'").fetchall()
        for s in students:
            notify(s["phone"], f"New homework: {request.form.get('title', '')}.", "homework")
        flash("Homework created and students were notified.")
        return redirect(url_for("homework"))
    rows = db.execute("SELECT * FROM homework ORDER BY id DESC").fetchall()
    return render_template("homework.html", homework=rows)

@app.route("/homework/<int:hw_id>/submit", methods=["POST"])
@login_required
def submit_homework(hw_id):
    db = get_db()
    u = current_user()
    db.execute(
        "INSERT INTO submissions (homework_id, student_phone, student_name, answer, file_link, created_at) "
        "VALUES (?, ?, ?, ?, ?, ?)",
        (hw_id, u["phone"], u["name"], request.form.get("answer", "").strip(),
         request.form.get("file_link", ""), now()))
    db.commit()
    teachers = db.execute("SELECT phone FROM users WHERE role = 'teacher'").fetchall()
    for t in teachers:
        notify(t["phone"], f"Homework submitted by {u['name']}.", "homework")
    flash("Homework submitted. Thank you!")
    return redirect(url_for("homework"))

@app.route("/homework/submission/<int:sub_id>/review", methods=["POST"])
@teacher_required
def review_submission(sub_id):
    db = get_db()
    u = current_user()
    status = request.form.get("status", "checked")
    comment = request.form.get("teacher_comment", "")
    db.execute("UPDATE submissions SET status = ?, teacher_comment = ? WHERE id = ?",
               (status, comment, sub_id))
    row = db.execute("SELECT * FROM submissions WHERE id = ?", (sub_id,)).fetchone()
    db.commit()
    if row:
        notify(row["student_phone"],
               f"Your homework was reviewed: {status}. {comment}", "homework")
        notify_parents_of(row["student_phone"],
                          f"Homework review for {row['student_name']}: {status}.", "homework")
    flash("Submission reviewed.")
    return redirect(url_for("dashboard"))

# --- Course purchase: bKash / Nagad ----------------------------------------
@app.route("/buy/<int:course_id>", methods=["GET", "POST"])
@login_required
def buy(course_id):
    db = get_db()
    u = current_user()
    course = db.execute("SELECT * FROM courses WHERE id = ?", (course_id,)).fetchone()
    if not course:
        abort(404)
    if request.method == "POST":
        method = request.form.get("method", "bKash")
        trxid = request.form.get("trxid", "").strip().upper()
        if not trxid:
            flash("Please enter your bKash / Nagad Transaction ID.")
            return redirect(url_for("buy", course_id=course_id))
        db.execute(
            "INSERT INTO orders (course_id, course_title, student_phone, student_name, method, trxid, amount_bdt, created_at) "
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            (course_id, course["title"], u["phone"], u["name"], method, trxid, course["price_bdt"], now()))
        db.commit()
        teachers = db.execute("SELECT phone FROM users WHERE role = 'teacher'").fetchall()
        for t in teachers:
            notify(t["phone"], f"New {method} payment to verify: {course['title']}, TrxID {trxid}, ৳{course['price_bdt']}.", "payment")
        flash("Order submitted! A teacher will verify your payment soon.")
        return redirect(url_for("dashboard"))
    return render_template("buy.html", course=course)

@app.route("/order/<int:order_id>/verify", methods=["POST"])
@teacher_required
def verify_order(order_id):
    db = get_db()
    status = request.form.get("status", "verified")
    db.execute("UPDATE orders SET status = ? WHERE id = ?", (status, order_id))
    row = db.execute("SELECT * FROM orders WHERE id = ?", (order_id,)).fetchone()
    db.commit()
    if row:
        notify(row["student_phone"],
               f"Your payment for {row['course_title']} was {status} (TrxID {row['trxid']}).", "payment")
        notify_parents_of(row["student_phone"],
                          f"Payment for {row['student_name']} — {row['course_title']} — was {status}.", "payment")
    flash(f"Order marked {status}.")
    return redirect(url_for("dashboard"))

# --- Free SSC / HSC Retake (Second Chance) registration --------------------
def verify_retake(form, admit_path, tab_path):
    """Automatic checks only. Never auto-rejects: missing/unreadable -> needs_review."""
    reasons = []
    for field, label in [("student_name", "name"), ("phone", "phone number"),
                         ("exam", "exam (SSC/HSC)"), ("exam_year", "exam year"),
                         ("roll_no", "roll number"), ("reg_no", "registration number"),
                         ("result_text", "result")]:
        if not (form.get(field) or "").strip():
            reasons.append(f"Missing {label}.")
    if not re.fullmatch(r"\d{4}", (form.get("exam_year") or "").strip()):
        reasons.append("Exam year must be 4 digits.")
    if not admit_path:
        reasons.append("Admit card file was not uploaded.")
    if not tab_path:
        reasons.append("Tabulation sheet file was not uploaded.")
    # Files are stored; their content cannot be read without OCR, so a teacher
    # reviews the files when anything is unclear. A full match of the form
    # fields + both files present = auto_verified.
    if reasons:
        return "needs_review", " ".join(reasons)
    return "auto_verified", "All required fields and both files were present and matched the form."

@app.route("/retake", methods=["GET", "POST"])
def retake():
    db = get_db()
    if request.method == "POST":
        os.makedirs(UPLOAD_FOLDER, exist_ok=True)
        admit_file = request.files.get("admit_card")
        tab_file = request.files.get("tabulation")
        admit_name, tab_name = "", ""
        for f, slot in [(admit_file, "admit"), (tab_file, "tab")]:
            if f and f.filename and allowed_file(f.filename):
                safe = secure_filename(f"{secrets.token_hex(4)}_{f.filename}")
                f.save(os.path.join(UPLOAD_FOLDER, safe))
                if slot == "admit":
                    admit_name = safe
                else:
                    tab_name = safe
        form = {k: request.form.get(k, "").strip() for k in
                ("student_name", "phone", "exam", "exam_year", "roll_no", "reg_no", "result_text")}
        status, reasons = verify_retake(form, admit_name, tab_name)
        cur = db.execute(
            "INSERT INTO retake_registrations (student_name, phone, exam, exam_year, roll_no, reg_no, "
            "result_text, admit_file, tabulation_file, status, reasons, created_at) "
            "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            (form["student_name"], re.sub(r"\D", "", form["phone"]), form["exam"], form["exam_year"],
             form["roll_no"], form["reg_no"], form["result_text"],
             admit_name, tab_name, status, reasons, now()))
        db.commit()
        notify(form["phone"],
               f"Retake registration received: {status.replace('_', ' ')}. {reasons}", "retake")
        teachers = db.execute("SELECT phone FROM users WHERE role = 'teacher'").fetchall()
        for t in teachers:
            notify(t["phone"], f"New retake registration: {form['student_name']} ({form['exam']} {form['exam_year']}) — {status}.", "retake")
        flash(f"Registration submitted — status: {status.replace('_', ' ')}. {reasons}")
        return redirect(url_for("retake_status", reg_id=cur.lastrowid))
    return render_template("retake.html")

@app.route("/retake/<int:reg_id>")
def retake_status(reg_id):
    row = get_db().execute(
        "SELECT * FROM retake_registrations WHERE id = ?", (reg_id,)).fetchone()
    if not row:
        abort(404)
    return render_template("retake_status.html", reg=row)

@app.route("/retake/<int:reg_id>/review", methods=["POST"])
@teacher_required
def review_retake(reg_id):
    db = get_db()
    status = request.form.get("status", "auto_verified")
    db.execute("UPDATE retake_registrations SET status = ? WHERE id = ?", (status, reg_id))
    row = db.execute("SELECT * FROM retake_registrations WHERE id = ?", (reg_id,)).fetchone()
    db.commit()
    if row:
        notify(row["phone"], f"Your retake registration was {status.replace('_', ' ')} by a teacher.", "retake")
    flash(f"Retake registration marked {status}.")
    return redirect(url_for("dashboard"))

@app.route("/uploads/<filename>")
@login_required
def uploaded_file(filename):
    return send_from_directory(UPLOAD_FOLDER, filename)

# --- Offline arrival: teacher-confirmed QR / PIN check-in ------------------
@app.route("/checkin", methods=["GET", "POST"])
@login_required
def checkin():
    db = get_db()
    u = current_user()
    if request.method == "POST" and u["role"] == "teacher":
        pin = f"{secrets.randbelow(9000) + 1000}"
        db.execute(
            "INSERT INTO checkin_codes (pin, class_level, teacher_phone, created_at) VALUES (?, ?, ?, ?)",
            (pin, request.form.get("class_level", ""), u["phone"], now()))
        db.commit()
        flash(f"Check-in PIN created: {pin}. Share it in class — students enter it, you confirm, parents are notified.")
        return redirect(url_for("checkin"))
    if request.method == "POST":  # student enters PIN
        pin = request.form.get("pin", "").strip()
        code = db.execute(
            "SELECT * FROM checkin_codes WHERE pin = ? AND is_active = 1", (pin,)).fetchone()
        if not code:
            flash("Wrong or expired PIN. Ask your teacher for the class PIN.")
            return redirect(url_for("checkin"))
        db.execute(
            "INSERT INTO checkins (student_phone, student_name, class_level, pin, created_at) "
            "VALUES (?, ?, ?, ?, ?)",
            (u["phone"], u["name"], code["class_level"], pin, now()))
        db.commit()
        flash("Check-in requested. Your teacher will confirm your arrival.")
        return redirect(url_for("checkin"))
    codes = db.execute("SELECT * FROM checkin_codes WHERE is_active = 1 ORDER BY id DESC").fetchall()
    pending = db.execute("SELECT * FROM checkins WHERE status = 'pending' ORDER BY id DESC").fetchall()
    mine = db.execute("SELECT * FROM checkins WHERE student_phone = ? ORDER BY id DESC LIMIT 10",
                      (u["phone"],)).fetchall()
    return render_template("checkin.html", codes=codes, pending=pending, mine=mine)

@app.route("/checkin/<int:checkin_id>/confirm", methods=["POST"])
@teacher_required
def confirm_checkin(checkin_id):
    db = get_db()
    db.execute("UPDATE checkins SET status = 'confirmed' WHERE id = ?", (checkin_id,))
    row = db.execute("SELECT * FROM checkins WHERE id = ?", (checkin_id,)).fetchone()
    db.commit()
    if row:
        msg = f"✅ {row['student_name']} has arrived at Redway Academy (Class {row['class_level']}) — confirmed by teacher."
        notify_parents_of(row["student_phone"], msg, "arrival")
        notify(row["student_phone"], msg, "arrival")
    flash("Arrival confirmed and the parent was notified.")
    return redirect(url_for("checkin"))

# --- Admin: add teachers ----------------------------------------------------
@app.route("/admin/teachers", methods=["GET", "POST"])
@teacher_required
def admin_teachers():
    db = get_db()
    u = current_user()
    if request.method == "POST":
        name = request.form.get("name", "").strip()
        phone = re.sub(r"\D", "", request.form.get("phone", ""))
        password = request.form.get("password", "")
        if not name or len(phone) < 11 or len(password) < 4:
            flash("Give a name, a valid phone number and a password of 4+ characters.")
            return redirect(url_for("admin_teachers"))
        try:
            db.execute(
                "INSERT INTO users (name, phone, password_hash, role, created_at) VALUES (?, ?, ?, 'teacher', ?)",
                (name, phone, generate_password_hash(password), now()))
            db.commit()
            flash(f"Teacher {name} added.")
        except sqlite3.IntegrityError:
            flash("This phone number is already registered.")
        return redirect(url_for("admin_teachers"))
    teachers = db.execute("SELECT * FROM users WHERE role = 'teacher' ORDER BY id").fetchall()
    return render_template("admin_teachers.html", teachers=teachers)

# --- Admin Panel ------------------------------------------------------------
# Pro control centre for teachers / admins. Super Admin (the first
# teacher) can manage everything, including user roles. All admin
# POST routes require a valid CSRF token (see _csrf_check above).
@app.route("/admin")
@admin_required
def admin_dashboard():
    db = get_db()
    u = current_user()
    counts = {
        "users": db.execute("SELECT COUNT(*) FROM users").fetchone()[0],
        "students": db.execute("SELECT COUNT(*) FROM users WHERE role = 'student'").fetchone()[0],
        "parents": db.execute("SELECT COUNT(*) FROM users WHERE role = 'parent'").fetchone()[0],
        "teachers": db.execute("SELECT COUNT(*) FROM users WHERE role = 'teacher'").fetchone()[0],
        "courses": db.execute("SELECT COUNT(*) FROM courses WHERE active = 1").fetchone()[0],
        "orders_pending": db.execute("SELECT COUNT(*) FROM orders WHERE status = 'pending'").fetchone()[0],
        "orders_total": db.execute("SELECT COUNT(*) FROM orders").fetchone()[0],
        "submissions_pending": db.execute("SELECT COUNT(*) FROM submissions WHERE status = 'submitted'").fetchone()[0],
        "retakes_pending": db.execute("SELECT COUNT(*) FROM retake_registrations WHERE status = 'needs_review'").fetchone()[0],
        "testimonials_pending": db.execute("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'").fetchone()[0],
        "checkins_pending": db.execute("SELECT COUNT(*) FROM checkins WHERE status = 'pending'").fetchone()[0],
    }
    recent_orders = db.execute("SELECT * FROM orders ORDER BY id DESC LIMIT 5").fetchall()
    recent_retakes = db.execute("SELECT * FROM retake_registrations ORDER BY id DESC LIMIT 5").fetchall()
    recent_feedback = db.execute("SELECT * FROM feedback ORDER BY id DESC LIMIT 5").fetchall()
    return render_template("admin/dashboard.html", user=u, counts=counts,
                           recent_orders=recent_orders, recent_retakes=recent_retakes,
                           recent_feedback=recent_feedback)

# --- Admin: Courses (full CRUD) ---------------------------------------------
@app.route("/admin/courses")
@admin_required
def admin_courses():
    rows = get_db().execute("SELECT * FROM courses ORDER BY id").fetchall()
    return render_template("admin/courses.html", courses=rows)

@app.route("/admin/courses/new", methods=["GET", "POST"])
@admin_required
def admin_course_new():
    if request.method == "POST":
        db = get_db()
        db.execute(
            "INSERT INTO courses (title, class_level, paper, price_bdt, lessons, description, active) "
            "VALUES (?, ?, ?, ?, ?, ?, 1)",
            (request.form.get("title", "").strip(), request.form.get("class_level", ""),
             request.form.get("paper", "1st Paper"), request.form.get("price_bdt") or 0,
             request.form.get("lessons") or 0, request.form.get("description", "").strip()))
        db.commit()
        app.logger.info("Admin %s created course %s", current_user()["phone"],
                        request.form.get("title", ""))
        flash("Course created.")
        return redirect(url_for("admin_courses"))
    return render_template("admin/course_form.html", course=None)

@app.route("/admin/courses/<int:course_id>/edit", methods=["GET", "POST"])
@admin_required
def admin_course_edit(course_id):
    db = get_db()
    course = db.execute("SELECT * FROM courses WHERE id = ?", (course_id,)).fetchone()
    if not course:
        abort(404)
    if request.method == "POST":
        db.execute(
            "UPDATE courses SET title = ?, class_level = ?, paper = ?, price_bdt = ?, "
            "lessons = ?, description = ?, active = ? WHERE id = ?",
            (request.form.get("title", "").strip(), request.form.get("class_level", ""),
             request.form.get("paper", "1st Paper"), request.form.get("price_bdt") or 0,
             request.form.get("lessons") or 0, request.form.get("description", "").strip(),
             1 if request.form.get("active") else 0, course_id))
        db.commit()
        flash("Course updated.")
        return redirect(url_for("admin_courses"))
    return render_template("admin/course_form.html", course=course)

@app.route("/admin/courses/<int:course_id>/delete", methods=["POST"])
@admin_required
def admin_course_delete(course_id):
    db = get_db()
    db.execute("DELETE FROM courses WHERE id = ?", (course_id,))
    db.commit()
    flash("Course deleted.")
    return redirect(url_for("admin_courses"))

# --- Admin: Users ------------------------------------------------------------
@app.route("/admin/users")
@admin_required
def admin_users():
    rows = get_db().execute("SELECT * FROM users ORDER BY id DESC").fetchall()
    return render_template("admin/users.html", users=rows)

@app.route("/admin/users/<int:user_id>/role", methods=["POST"])
@super_admin_required
def admin_user_role(user_id):
    db = get_db()
    role = request.form.get("role", "student")
    if role not in ("student", "parent", "teacher"):
        abort(400)
    db.execute("UPDATE users SET role = ? WHERE id = ?", (role, user_id))
    db.commit()
    flash("User role updated.")
    return redirect(url_for("admin_users"))

@app.route("/admin/users/<int:user_id>/toggle", methods=["POST"])
@super_admin_required
def admin_user_toggle(user_id):
    db = get_db()
    user = db.execute("SELECT * FROM users WHERE id = ?", (user_id,)).fetchone()
    if not user:
        abort(404)
    if user["id"] == current_user()["id"]:
        flash("You cannot deactivate your own account.")
        return redirect(url_for("admin_users"))
    new_active = 0 if user["active"] else 1
    db.execute("UPDATE users SET active = ? WHERE id = ?", (new_active, user_id))
    db.commit()
    flash(f"User {'activated' if new_active else 'deactivated'}.")
    return redirect(url_for("admin_users"))

# --- Admin: Homework ---------------------------------------------------------
@app.route("/admin/homework")
@admin_required
def admin_homework():
    db = get_db()
    rows = db.execute("SELECT * FROM homework ORDER BY id DESC").fetchall()
    subs = db.execute("SELECT * FROM submissions ORDER BY id DESC LIMIT 30").fetchall()
    return render_template("admin/homework.html", homework=rows, submissions=subs)

@app.route("/admin/homework/<int:hw_id>/edit", methods=["GET", "POST"])
@admin_required
def admin_homework_edit(hw_id):
    db = get_db()
    hw = db.execute("SELECT * FROM homework WHERE id = ?", (hw_id,)).fetchone()
    if not hw:
        abort(404)
    if request.method == "POST":
        db.execute("UPDATE homework SET title = ?, class_level = ?, details = ? WHERE id = ?",
                   (request.form.get("title", "").strip(), request.form.get("class_level", ""),
                    request.form.get("details", ""), hw_id))
        db.commit()
        flash("Homework updated.")
        return redirect(url_for("admin_homework"))
    return render_template("admin/homework_form.html", hw=hw)

@app.route("/admin/homework/<int:hw_id>/delete", methods=["POST"])
@admin_required
def admin_homework_delete(hw_id):
    db = get_db()
    db.execute("DELETE FROM homework WHERE id = ?", (hw_id,))
    db.commit()
    flash("Homework deleted.")
    return redirect(url_for("admin_homework"))

# --- Admin: Orders (bKash / Nagad) -------------------------------------------
@app.route("/admin/orders")
@admin_required
def admin_orders():
    status = request.args.get("status", "all")
    db = get_db()
    if status in ("pending", "verified", "rejected"):
        rows = db.execute("SELECT * FROM orders WHERE status = ? ORDER BY id DESC", (status,)).fetchall()
    else:
        rows = db.execute("SELECT * FROM orders ORDER BY id DESC").fetchall()
    return render_template("admin/orders.html", orders=rows, status=status)

@app.route("/admin/orders/<int:order_id>/verify", methods=["POST"])
@admin_required
def admin_verify_order(order_id):
    db = get_db()
    status = request.form.get("status", "verified")
    if status not in ("verified", "rejected"):
        abort(400)
    db.execute("UPDATE orders SET status = ? WHERE id = ?", (status, order_id))
    row = db.execute("SELECT * FROM orders WHERE id = ?", (order_id,)).fetchone()
    db.commit()
    if row:
        notify(row["student_phone"],
               f"Your payment for {row['course_title']} was {status} (TrxID {row['trxid']}).", "payment")
        notify_parents_of(row["student_phone"],
                          f"Payment for {row['student_name']} — {row['course_title']} — was {status}.", "payment")
    flash(f"Order marked {status}.")
    return redirect(url_for("admin_orders"))

# --- Admin: Retake registrations ---------------------------------------------
@app.route("/admin/retakes")
@admin_required
def admin_retakes():
    rows = get_db().execute(
        "SELECT * FROM retake_registrations ORDER BY id DESC").fetchall()
    return render_template("admin/retakes.html", retakes=rows)

@app.route("/admin/retakes/<int:reg_id>/review", methods=["POST"])
@admin_required
def admin_review_retake(reg_id):
    db = get_db()
    status = request.form.get("status", "auto_verified")
    if status not in ("auto_verified", "needs_review", "rejected"):
        abort(400)
    db.execute("UPDATE retake_registrations SET status = ? WHERE id = ?", (status, reg_id))
    row = db.execute("SELECT * FROM retake_registrations WHERE id = ?", (reg_id,)).fetchone()
    db.commit()
    if row:
        notify(row["phone"], f"Your retake registration was {status.replace('_', ' ')} by a teacher.", "retake")
    flash(f"Retake registration marked {status}.")
    return redirect(url_for("admin_retakes"))

# --- Admin: Testimonials ------------------------------------------------------
@app.route("/admin/testimonials")
@admin_required
def admin_testimonials():
    rows = get_db().execute(
        "SELECT * FROM testimonials ORDER BY id DESC").fetchall()
    return render_template("admin/testimonials.html", testimonials=rows)

@app.route("/admin/testimonials/<int:t_id>/status", methods=["POST"])
@admin_required
def admin_testimonial_status(t_id):
    db = get_db()
    status = request.form.get("status", "approved")
    if status not in ("approved", "hidden", "pending"):
        abort(400)
    db.execute("UPDATE testimonials SET status = ? WHERE id = ?", (status, t_id))
    db.commit()
    flash(f"Testimonial {status}.")
    return redirect(url_for("admin_testimonials"))

@app.route("/admin/testimonials/<int:t_id>/delete", methods=["POST"])
@admin_required
def admin_testimonial_delete(t_id):
    db = get_db()
    db.execute("DELETE FROM testimonials WHERE id = ?", (t_id,))
    db.commit()
    flash("Testimonial deleted.")
    return redirect(url_for("admin_testimonials"))

# --- Admin: Progress notes + feedback -----------------------------------------
@app.route("/admin/progress")
@admin_required
def admin_progress():
    db = get_db()
    rows = db.execute("SELECT * FROM progress_notes ORDER BY id DESC LIMIT 100").fetchall()
    feedback_rows = db.execute("SELECT * FROM feedback ORDER BY id DESC LIMIT 30").fetchall()
    return render_template("admin/progress.html", progress=rows, feedback=feedback_rows)

@app.route("/admin/progress/<int:note_id>/delete", methods=["POST"])
@admin_required
def admin_progress_delete(note_id):
    db = get_db()
    db.execute("DELETE FROM progress_notes WHERE id = ?", (note_id,))
    db.commit()
    flash("Progress note deleted.")
    return redirect(url_for("admin_progress"))

# --- Admin: Notifications / announcements --------------------------------------
@app.route("/admin/notifications", methods=["GET", "POST"])
@admin_required
def admin_notifications():
    db = get_db()
    u = current_user()
    if request.method == "POST":
        msg = request.form.get("message", "").strip()
        audience = request.form.get("audience", "all")
        class_level = request.form.get("class_level", "").strip()
        if not msg:
            flash("Write a message first.")
            return redirect(url_for("admin_notifications"))
        db.execute("INSERT INTO announcements (teacher_phone, message, created_at) VALUES (?, ?, ?)",
                   (u["phone"], msg, now()))
        if audience == "all":
            users = db.execute("SELECT phone FROM users").fetchall()
        elif audience in ("teacher", "student", "parent"):
            users = db.execute("SELECT phone FROM users WHERE role = ?", (audience,)).fetchall()
        elif audience == "class" and class_level:
            users = db.execute("SELECT phone FROM users WHERE class_level = ?", (class_level,)).fetchall()
        else:
            users = []
        for row in users:
            notify(row["phone"], f"Announcement from Redway Academy: {msg}", "announcement")
        db.commit()
        flash(f"Announcement sent to {len(users)} user(s).")
        return redirect(url_for("admin_notifications"))
    recent = db.execute("SELECT * FROM announcements ORDER BY id DESC LIMIT 10").fetchall()
    return render_template("admin/notifications.html", announcements=recent)

# --- Admin: Check-in codes -----------------------------------------------------
@app.route("/admin/checkins", methods=["GET", "POST"])
@admin_required
def admin_checkins():
    db = get_db()
    u = current_user()
    if request.method == "POST":
        pin = f"{secrets.randbelow(9000) + 1000}"
        db.execute(
            "INSERT INTO checkin_codes (pin, class_level, teacher_phone, created_at) VALUES (?, ?, ?, ?)",
            (pin, request.form.get("class_level", ""), u["phone"], now()))
        db.commit()
        flash(f"Check-in PIN created: {pin}")
        return redirect(url_for("admin_checkins"))
    codes = db.execute("SELECT * FROM checkin_codes ORDER BY id DESC LIMIT 20").fetchall()
    checkins = db.execute("SELECT * FROM checkins ORDER BY id DESC LIMIT 30").fetchall()
    return render_template("admin/checkins.html", codes=codes, checkins=checkins)

@app.route("/admin/checkins/code/<int:code_id>/deactivate", methods=["POST"])
@admin_required
def admin_checkin_deactivate(code_id):
    db = get_db()
    db.execute("UPDATE checkin_codes SET is_active = 0 WHERE id = ?", (code_id,))
    db.commit()
    flash("Check-in PIN deactivated.")
    return redirect(url_for("admin_checkins"))

if __name__ == "__main__":
    # Production: use gunicorn (see wsgi.py) — this dev server is for local use only.
    app.run(debug=app.config["DEBUG"], host="127.0.0.1", port=5000)
