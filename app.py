"""
Redway Academy — Flask app
Mistakes Mark the Way / ভুল থেকেই শেখা, ভুল করেই জেতা
NCTB English for High School (Class 6-10) and College (Class 11-12) only.
SQLite, Flask only. No facial recognition / biometric identification.
Offline arrival uses a teacher-confirmed QR / PIN check-in.
"""
import os
import re
import secrets
import sqlite3
from datetime import datetime
from functools import wraps

from flask import (
    Flask, g, redirect, render_template, request, session,
    url_for, flash, abort, send_from_directory,
)
from werkzeug.security import generate_password_hash, check_password_hash
from werkzeug.utils import secure_filename

app = Flask(__name__)
app.config["SECRET_KEY"] = os.environ.get("SECRET_KEY", "redway-dev-secret-change-me")
app.config["MAX_CONTENT_LENGTH"] = 8 * 1024 * 1024  # 8 MB uploads
UPLOAD_FOLDER = os.path.join(app.instance_path, "uploads")
ALLOWED_EXTENSIONS = {"pdf", "jpg", "jpeg", "png"}

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
        os.makedirs(app.instance_path, exist_ok=True)
        g.db = sqlite3.connect(os.path.join(app.instance_path, "redway.db"))
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
    db.commit()

with app.app_context():
    init_db()

# --- Helpers ----------------------------------------------------------------
def now():
    return datetime.now().strftime("%Y-%m-%d %H:%M")

def current_user():
    if "user_id" not in session:
        return None
    return get_db().execute("SELECT * FROM users WHERE id = ?", (session["user_id"],)).fetchone()

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
    return {"current_user": u, "unread_count": unread}

# --- Public routes ----------------------------------------------------------
@app.route("/")
def index():
    db = get_db()
    courses = db.execute("SELECT * FROM courses ORDER BY id").fetchall()
    testimonials = db.execute(
        "SELECT * FROM testimonials ORDER BY id DESC LIMIT 6").fetchall()
    return render_template("index.html", courses=courses, testimonials=testimonials)

@app.route("/courses")
def courses():
    paper = request.args.get("paper", "All")
    db = get_db()
    if paper in ("1st Paper", "2nd Paper", "Spoken English"):
        rows = db.execute("SELECT * FROM courses WHERE paper = ? ORDER BY id", (paper,)).fetchall()
    else:
        rows = db.execute("SELECT * FROM courses ORDER BY id").fetchall()
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
            "INSERT INTO testimonials (author_name, role, class_level, message, created_at) "
            "VALUES (?, ?, ?, ?, ?)",
            (request.form.get("author_name", "").strip(),
             request.form.get("role", "Student"),
             request.form.get("class_level", ""),
             request.form.get("message", "").strip(), now()))
        db.commit()
        flash("Thank you! Your endorsement has been published.")
        return redirect(url_for("testimonials"))
    rows = db.execute("SELECT * FROM testimonials ORDER BY id DESC").fetchall()
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
            cur = db.execute(
                "INSERT INTO users (name, phone, password_hash, role, class_level, linked_student_phone, created_at) "
                "VALUES (?, ?, ?, ?, ?, ?, ?)",
                (name, phone, generate_password_hash(password), role, class_level,
                 linked or None, now()))
            db.commit()
        except sqlite3.IntegrityError:
            flash("This phone number is already registered. Please log in.")
            return redirect(url_for("login"))
        session["user_id"] = cur.lastrowid
        flash(f"Welcome, {name}! You are logged in as {role}.")
        return redirect(url_for("dashboard"))
    return render_template("register.html")

@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        phone = re.sub(r"\D", "", request.form.get("phone", ""))
        password = request.form.get("password", "")
        user = get_db().execute("SELECT * FROM users WHERE phone = ?", (phone,)).fetchone()
        if user and check_password_hash(user["password_hash"], password):
            session["user_id"] = user["id"]
            flash(f"Welcome back, {user['name']}!")
            return redirect(url_for("dashboard"))
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

if __name__ == "__main__":
    app.run(debug=True)
