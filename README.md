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
python3 app.py
```

Open http://127.0.0.1:5000

The SQLite database is created automatically in `instance/redway.db` on first run, and the 11 courses are seeded automatically.

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

## Deploy to Hostinger / redwayacademy.com

This is a standard Flask app. On shared hosting, run it with a WSGI server (e.g. Gunicorn behind a web server, or Hostinger's Python app support), set `SECRET_KEY` in the environment, and point the domain to the app. Static files are in `static/`, templates in `templates/`.

## Limitations

- File uploads are stored locally under `instance/uploads/` (filenames only in the database). They are not checked against the Education Board database — there is no public NCTB / board API for that — so "automatic verification" is the document + form checks above, plus teacher review.
- Notifications are in-app only (no SMS / email gateway is included, to avoid paid services).
- Passwords are hashed with Werkzeug. Change `SECRET_KEY` via the `SECRET_KEY` environment variable in production.
- This is a learning-platform starter, not a payment gateway: bKash / Nagad are manual send-money + Transaction ID verification, as requested.
