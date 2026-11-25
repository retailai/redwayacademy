# redwayacademy

Redway Academy — **Mistakes Mark the Way** / ভুল থেকেই শেখা, ভুল করেই জেতা

NCTB English learning website for Bangladesh, for High School (Class 6–10) and College (Class 11–12) only — no Primary.

- English for Today (1st Paper) and English 2nd Paper (Grammar and Composition, NCTB, nctb.gov.bd) for Class 6, 7, 8, 9–10 and 11–12
- Spoken English — Speak with Confidence (৳1499, 24 lessons)
- Homework, student progress dashboard, feedback, testimonials
- 3 phone-number logins: Teacher / Admin / Super Admin, Student, Parent
- Course purchase with bKash and Nagad (send money, submit Transaction ID, teacher verifies)
- Free SSC / HSC Retake (Second Chance) registration with admit card + tabulation sheet upload and automatic verification checks
- Notification system for all 3 login types
- Offline class arrival by teacher-confirmed QR / PIN check-in (no facial recognition / biometric identification)

## Brand

- Slogan: **Mistakes Mark the Way**
- Colours: Google Blue `#4285F4`, Red `#EA4335`, Yellow `#FBBC05`, Green `#34A853`, Grey `#5F6368`
- Font: Poppins
- Red Pen Method: 1. Red Pen Review, 2. Mistake Notebook (ভুলের খাতা), 3. Weekly Retry Test, 4. Red to Green — monthly Green Progress report to parents
- Public wording: do not use "fail" in promotion. Use "ভুল করো, বোঝো, সঠিক করো" — make a mistake, understand it, correct it.

## Run locally

```bash
cd redwayacademy-flask
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env   # then edit .env and set a real SECRET_KEY
python3 app.py         # dev server, http://127.0.0.1:5000
```

Production-style run on your own computer / VPS:

```bash
export SECRET_KEY="$(python3 -c 'import secrets; print(secrets.token_hex(32))')"
export FLASK_ENV=production
gunicorn -w 2 -b 0.0.0.0:8000 wsgi:app
```

The SQLite database is created automatically in `instance/redway.db` on first run, and the 11 courses are seeded automatically. Set `DATABASE_PATH` to keep the database outside the app folder if you prefer.

## Admin Panel — /admin

Log in as a **Teacher**, then open **Admin Panel** (top menu, or `/admin`).

How to log in as admin the first time:

1. Register with role **Teacher** and a phone number + password. The first teacher account becomes **Super Admin**.
2. After that, new teacher accounts can only be added by a logged-in admin from Admin Panel → Teachers (or Dashboard → Add Teacher). Teachers cannot self-claim the role.
3. Students and parents can self-register with their phone number.

What the Admin Panel does:

- **Dashboard** — counts: users by role, active courses, pending / total orders, pending homework submissions, retakes needing review, testimonials waiting for approval, pending check-ins
- **Courses** — full CRUD: create, edit (name, class, paper, price, lessons, description, active), delete
- **Users** — view all users; Super Admin only can change a role or deactivate / activate an account
- **Homework** — view, edit and delete homework; review submissions (checked / needs work + comment)
- **Orders** — verify / reject bKash / Nagad payments by Transaction ID; the student and parent are notified
- **Retakes** — approve / reject / send back for review, and view the uploaded admit card and tabulation sheet
- **Reviews** — approve / hide / delete testimonials (new endorsements stay hidden until approved)
- **Progress** — view and delete progress notes, read student / parent feedback
- **Notify** — send an announcement to everyone, or to students / parents / teachers / one class only
- **Check-in** — create class PINs, deactivate a PIN, see arrivals and confirm them (parent is notified)

The normal teacher Dashboard still works as before; the Admin Panel is the extra pro control centre on top of it.

## Deploy to Hostinger / redwayacademy.com — production

Hostinger shared hosting uses a Python app with Passenger (WSGI):

1. Upload the project files to your Hostinger Python app folder (or push from GitHub, then pull on the server). Do **not** upload `instance/` (database + uploads), `.venv/`, or a real `.env` file.
2. In the Hostinger Python app settings, set:
   - **Startup file:** `passenger_wsgi.py` (already in this project; it imports `application` from `app.py`)
   - **Python version:** 3.11 or newer
   - **Environment variables:**
     - `SECRET_KEY` = a long random secret (generate one: `python3 -c "import secrets; print(secrets.token_hex(32))"`)
     - `FLASK_ENV` = `production`
     - Optional: `DATABASE_PATH`, `MAX_UPLOAD_MB`, `UPLOAD_FOLDER`, `LOG_FILE`
3. Install dependencies on the server: `pip install -r requirements.txt` (Hostinger runs this for the app, or run it in the app's virtualenv).
4. Point the domain **redwayacademy.com** to the Python app in Hostinger (Domains → redwayacademy.com → point to the app), then turn on the free **SSL certificate** so the site is `https://redwayacademy.com`.
5. Restart the Python app. Check `https://redwayacademy.com/health` — it should return `{"status": "ok"}`.
6. First visit: register the first Teacher account — that account becomes Super Admin. Then log in and open `/admin`.

On a VPS instead of shared hosting, run it with gunicorn behind Nginx:

```bash
export SECRET_KEY="your-long-random-secret"
export FLASK_ENV=production
gunicorn -w 2 -b 127.0.0.1:8000 wsgi:app
```

Then point Nginx at `127.0.0.1:8000`, serve `static/` directly, and enable HTTPS (e.g. Certbot) for redwayacademy.com.

Security notes for production:

- `SECRET_KEY` must come from the environment — the app logs a warning if the dev fallback is in use.
- `DEBUG` is off when `FLASK_ENV=production`. Session cookies are HTTP-only, SameSite=Lax, and Secure in production.
- Login is rate-limited (5 wrong attempts per phone number in 15 minutes).
- Admin POST routes require a CSRF token; all pages send security headers (CSP, X-Frame-Options, nosniff).
- Uploads are limited to 8 MB, PDF / JPG / PNG only, saved under `instance/uploads/` with random filenames. Back up `instance/redway.db` regularly — it holds all users, orders and registrations.

## First teacher / Super Admin

1. Register with role **Teacher** and a phone number + password. The first teacher account becomes **Super Admin**.
2. After that, new teacher accounts can only be added by a logged-in admin from Dashboard → Add Teacher. Teachers cannot self-claim the role.
3. Students and parents can self-register with their phone number.

## Features

- **Courses:** `/courses` — filter by 1st Paper / 2nd Paper / Spoken English. Buy with bKash or Nagad, submit the Transaction ID, a teacher verifies the order in the dashboard.
- **Homework:** teachers create homework for a class; students submit written answers (and an optional file link); teachers mark it checked / needs work with a comment.
- **Progress dashboard:** teachers add a score + suggestion per student; students and parents see their own progress, and can give feedback.
- **Testimonials:** happy parents and students can publish an endorsement.
- **Retake registration:** `/retake` — free for SSC / HSC retake students. Upload admit card and tabulation sheet (PDF / JPG / PNG). Automatic checks: all required fields present, roll / registration number present, exam and year present, result declared, both files uploaded and readable by extension/size. If everything matches, status is `auto_verified`. If anything is missing or unreadable, status is `needs_review` — never auto-rejected — and a teacher reviews it in the dashboard.
- **Notifications:** in-app notifications (bell + `/notifications`) for homework, progress, payments, retake results, announcements, and arrivals. Each login type sees only its own notifications.
- **Arrival check-in:** teacher creates a 4-digit PIN for a class (`/checkin`). The student enters the PIN, the teacher confirms, and the linked parent gets a notification that the student has arrived. No facial recognition is used.

## Limitations

- File uploads are stored locally under `instance/uploads/` (filenames only in the database). They are not checked against the Education Board database — there is no public NCTB / board API for that — so "automatic verification" is the document + form checks above, plus teacher review.
- Notifications are in-app only (no SMS / email gateway is included, to avoid paid services).
- Passwords are hashed with Werkzeug. Change `SECRET_KEY` via the `SECRET_KEY` environment variable in production.
- This is a learning-platform starter, not a payment gateway: bKash / Nagad are manual send-money + Transaction ID verification, as requested.
