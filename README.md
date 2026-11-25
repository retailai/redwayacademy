# Redway Academy — PHP + MySQL version

**Mistakes Mark the Way** / ভুল থেকেই শেখা, ভুল করেই জেতা

NCTB English learning website for Bangladesh, for High School (Class 6–10) and College (Class 11–12) only — no Primary. Plain PHP 8, no framework, PDO database layer. Built to run on **Hostinger Premium shared hosting (PHP, no Python)** — the same features as the Flask version, rebuilt in PHP.

- English for Today (1st Paper) and English 2nd Paper (Grammar and Composition, NCTB, nctb.gov.bd) for Class 6, 7, 8, 9–10 and 11–12
- Spoken English — Speak with Confidence (৳1499, 24 lessons)
- Homework, student progress dashboard, feedback, testimonials
- 3 phone-number logins: Teacher / Admin / Super Admin, Student, Parent
- Course purchase with bKash and Nagad (send money, submit Transaction ID, teacher verifies)
- Free SSC / HSC Retake (Second Chance) registration with admit card + tabulation sheet upload and automatic verification checks
- Notification system for all 3 login types (in-app bell + list)
- Offline class arrival by teacher-confirmed 4-digit PIN check-in — **no facial recognition / biometric identification anywhere**

## Brand

- Slogan: **Mistakes Mark the Way**
- Colours: Google Blue `#4285F4`, Red `#EA4335`, Yellow `#FBBC05`, Green `#34A853`, Grey `#5F6368`
- Font: Poppins
- Logo: inline SVG only — no image files
- Red Pen Method: 1. Red Pen Review, 2. Mistake Notebook (ভুলের খাতা), 3. Weekly Retry Test, 4. Red to Green — monthly Green Progress report to parents
- Public wording: do not use "fail" in promotion. Use "ভুল করো, বোঝো, সঠিক করো" — make a mistake, understand it, correct it.

## Run locally (SQLite test)

```bash
cd redwayacademy-php
cp .env.example .env   # then edit .env: set a real SECRET_KEY
# Local test DB (SQLite):
#   DB_DSN=sqlite:/absolute/path/to/redwayacademy-php/storage/redway.db
php -S 127.0.0.1:8000 -t public
# Open http://127.0.0.1:8000  — check http://127.0.0.1:8000/health
```

The SQLite database is created automatically in `storage/redway.db` on first run, and the 11 courses are seeded automatically.

## Deploy to Hostinger Premium / redwayacademy.com

Hostinger Premium shared hosting supports PHP + MySQL, but **not Python** — that is why this PHP version exists.

1. **Create the MySQL database** in hPanel → Databases → MySQL Databases. Note the database name, username, password and host (usually `localhost`).
2. **Import the schema:** hPanel → Databases → phpMyAdmin → select your database → Import → choose `schema.sql` from this project. (The app also creates the tables automatically on first run, so this step is a safety net; the seed courses are in `schema.sql` too.)
3. **Upload the files** to `public_html/` (File Manager or FTP): everything in this project — `public/`, `includes/`, `templates/`, `uploads/`, `.htaccess`, `schema.sql`, `.env.example`. The root `.htaccess` routes all requests into `public/`, which is the web root. Do **not** upload a real `.env` file by FTP in plain sight — create it on the server instead (step 4).
4. **Set the environment:** copy `.env.example` to `.env` in the project root (one level above `public_html` if your host allows, otherwise in the project root next to `public/`) and fill in:
   - `APP_ENV=production`
   - `SECRET_KEY=` a long random secret (generate one: `openssl rand -hex 32`)
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` from step 1 (leave `DB_DSN` empty)
   - `BASE_URL=https://redwayacademy.com`
   - `UPLOAD_FOLDER=` — leave empty to use the project's `uploads/` folder
5. **Point the domain** redwayacademy.com to the hosting (it already is, if the domain is on Hostinger) and turn on the free **SSL certificate** so the site is `https://redwayacademy.com`.
6. **Check health:** visit `https://redwayacademy.com/health` — it should return `{"status":"ok"}`.
7. **First visit:** register the first Teacher account — that account becomes Super Admin. Then log in and open `/admin`.

## Admin login (first time)

1. Open `/register`, choose role **Teacher**, enter a name, phone number (11 digits) and password (4+ characters). The first teacher account becomes **Super Admin**.
2. After that, new teacher accounts can only be added by a logged-in admin from Admin Panel → Teachers. Teachers cannot self-claim the role.
3. Students and parents can self-register with their phone number.

## Admin Panel — /admin

Log in as a **Teacher**, then open **Admin Panel** (top menu, or `/admin`).

- **Dashboard** — counts: users by role, active courses, pending / total orders, pending homework submissions, retakes needing review, testimonials waiting for approval, pending check-ins
- **Courses** — full CRUD: create, edit (name, class, paper, track, price, lessons, description, active), delete
- **Settings** — monthly subscription fees and the retake campaign switch (see below)
- **Users** — view all users; Super Admin only can change a role or deactivate / activate an account
- **Teachers** — add new teacher accounts (admin only)
- **Homework** — view, edit and delete homework; review submissions (checked / needs work + comment)
- **Orders** — verify / reject bKash / Nagad payments by Transaction ID; the student and parent are notified
- **Retakes** — approve / needs review / reject, and view the uploaded admit card and tabulation sheet
- **Reviews** — approve / hide / delete testimonials (new endorsements stay hidden until approved)
- **Progress** — view and delete progress notes, read student / parent feedback
- **Notify** — send an announcement to everyone, or to students / parents / teachers / one class only
- **Check-in** — create class PINs, deactivate a PIN, see arrivals and confirm them (parent is notified)

## Routes

| Route | What it does |
|---|---|
| `/` | Home — slogan, Red Pen Method, courses, approved testimonials |
| `/courses`, `/course/{id}` | Course list (filter by paper) and detail |
| `/register`, `/login`, `/logout` | Phone-number auth (3 roles) |
| `/dashboard` | Role dashboard: teacher overview, student/parent progress + orders |
| `/homework`, `/homework/{id}/submit`, `/homework/submission/{id}/review` | Homework create / submit / teacher review |
| `/progress` | Progress notes (teacher adds score + suggestion; students/parents view) |
| `/feedback` | Feedback field (goes to all teachers) |
| `/notifications` | In-app notifications (bell + list, marks read) |
| `/announce` | Teacher announcement to all users |
| `/testimonials` | Approved reviews + submit an endorsement (pending until approved) |
| `/buy/{id}?plan=monthly\|once` | bKash / Nagad send-money + TrxID order (monthly subscription or one-time) |
| `/order/{id}/verify` | Teacher verifies / rejects an order |
| `/retake`, `/retake/{id}` | Free SSC/HSC retake registration + status |
| `/retake/{id}/review` | Teacher reviews a retake registration |
| `/checkin`, `/checkin/{id}/confirm` | PIN check-in (teacher creates PIN, student enters it, teacher confirms) |
| `/uploads/{file}` | Uploaded retake files (login required) |
| `/admin`, `/admin/courses…`, `/admin/users…`, `/admin/teachers`, `/admin/homework…`, `/admin/orders…`, `/admin/retakes…`, `/admin/testimonials…`, `/admin/progress…`, `/admin/notifications`, `/admin/checkins…`, `/admin/settings` | Admin Panel (teacher only; role changes Super Admin only) |
| `/health` | `{"status":"ok"}` health check |
| `/robots.txt`, `/sitemap.xml` | SEO files |

## Courses: two tracks, monthly subscription, settings

- **Tracks** — the 11 courses are split into exactly two tracks: **Academic English** (English for Today 1st Paper + Grammar & Composition 2nd Paper together, Class 6–12) and **Spoken English** (for all). `/courses` first shows the 2 track cards; Academic then asks for the class (6, 7, 8, 9-10, 11-12), Spoken asks for the last education level (SSC / HSC / Honours / Other). Course rows carry a `track` column (auto-migrated; backfilled from the paper).
- **Monthly subscription (live)** — ৳1450/month for live classes, 3 days a week on Google Meet / Zoom, shown as the primary price on track cards, course cards, course detail and the buy page. A verified monthly order enrols the student in **every course of that track for 30 days** from verification (`orders.valid_until`); the one-time course price stays as a secondary option. Buy flow is the same manual bKash / Nagad + TrxID + sender-mobile verification.
- **Settings (Admin Panel → Settings, `settings` table)** — all editable without a code change:
  - `subscription_fee_live` — live monthly fee, default ৳1450
  - `recorded_fee` — recorded class fee, default 0 = not set yet (not shown to students until set)
  - `live_days_per_week` — live class days per week, default 3 (the "3 days a week, Google Meet / Zoom" text on the site comes from this)
  - `retake_campaign_active` — free SSC/HSC retake campaign switch (see below)
- **Free retake = campaign** — when `retake_campaign_active` is OFF, the announcement strip, nav link and home CTA are hidden and `/retake` shows a bilingual "campaign is not running right now" message instead of the form (POSTs are refused too). Flip it back ON in Admin Panel → Settings at any time.

## Security

- `SECRET_KEY` comes from the environment — the app uses a dev fallback only for local testing; set a real one in production.
- Session cookies are HTTP-only, SameSite=Lax, Secure in production, 8-hour lifetime.
- Login is rate-limited: 5 wrong attempts per phone number in 15 minutes (stored in the database, survives restart).
- Admin POST routes require a CSRF token; all pages send security headers (CSP, X-Frame-Options, nosniff).
- Uploads are limited to 8 MB, PDF / JPG / PNG only, saved under `uploads/` with random filenames; `uploads/.htaccess` denies script execution. Uploaded files are served only to logged-in users through the app.
- Passwords are hashed with PHP `password_hash` (bcrypt).

## Limitations

- File uploads are stored locally under `uploads/` (filenames only in the database). They are not checked against the Education Board database — there is no public NCTB / board API for that — so "automatic verification" is the document + form checks (all fields present, 4-digit year, both files uploaded), plus teacher review. There is **no OCR**.
- Notifications are in-app only (no SMS / email gateway is included, to avoid paid services).
- bKash / Nagad are manual send-money + Transaction ID verification, not an automatic payment gateway.
- This is a learning-platform starter, not a payment gateway.
