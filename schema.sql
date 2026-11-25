-- Redway Academy — MySQL schema (Hostinger).
-- Import in hPanel → Databases → phpMyAdmin, or: mysql -u USER -p DB < schema.sql
-- The PHP app also creates these tables automatically on first run if missing.
-- No facial recognition / biometric data anywhere in this schema.

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name TEXT NOT NULL,
  phone VARCHAR(20) NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role VARCHAR(20) NOT NULL,
  class_level VARCHAR(20),
  linked_student_phone VARCHAR(20),
  active TINYINT(1) NOT NULL DEFAULT 1,
  is_super_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title TEXT NOT NULL,
  class_level VARCHAR(20),
  paper VARCHAR(30),
  track VARCHAR(20) NOT NULL DEFAULT 'academic',
  price_bdt INT,
  lessons INT,
  description TEXT,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS homework (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title TEXT NOT NULL,
  class_level VARCHAR(20),
  details TEXT,
  teacher_phone VARCHAR(20),
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  homework_id INT,
  student_phone VARCHAR(20),
  student_name TEXT,
  answer TEXT,
  file_link TEXT,
  status VARCHAR(20) DEFAULT 'submitted',
  teacher_comment TEXT,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS progress_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_phone VARCHAR(20),
  student_name TEXT,
  score INT,
  suggestion TEXT,
  teacher_phone VARCHAR(20),
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  author_phone VARCHAR(20),
  author_name TEXT,
  message TEXT,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  author_name TEXT,
  role VARCHAR(20),
  class_level VARCHAR(20),
  message TEXT,
  status VARCHAR(20) DEFAULT 'pending',
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT,
  course_title TEXT,
  student_phone VARCHAR(20),
  student_name TEXT,
  method VARCHAR(20),
  trxid VARCHAR(40),
  sender_phone VARCHAR(20),
  amount_bdt INT,
  plan VARCHAR(20) NOT NULL DEFAULT 'once',
  track VARCHAR(20),
  valid_until VARCHAR(20),
  status VARCHAR(20) DEFAULT 'pending',
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS retake_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_name TEXT,
  phone VARCHAR(20),
  exam VARCHAR(20),
  exam_year VARCHAR(10),
  roll_no VARCHAR(40),
  reg_no VARCHAR(40),
  result_text TEXT,
  admit_file TEXT,
  tabulation_file TEXT,
  status VARCHAR(20) DEFAULT 'needs_review',
  reasons TEXT,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_phone VARCHAR(20),
  message TEXT,
  kind VARCHAR(20),
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS checkin_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pin VARCHAR(10) NOT NULL,
  class_level VARCHAR(20),
  teacher_phone VARCHAR(20),
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS checkins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_phone VARCHAR(20),
  student_name TEXT,
  class_level VARCHAR(20),
  pin VARCHAR(10),
  status VARCHAR(20) DEFAULT 'pending',
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_phone VARCHAR(20),
  message TEXT,
  created_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(20),
  attempted_at VARCHAR(20)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Persistent "remember me" logins (30 days): selector + HMAC-SHA256 of the
-- validator. The validator itself is never stored. Auto-created by the app.
CREATE TABLE IF NOT EXISTS remember_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  selector VARCHAR(32) NOT NULL UNIQUE,
  token_hash TEXT NOT NULL,
  expires_at INT NOT NULL,
  created_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Key-value settings: live monthly fee (৳1450), recorded class fee
-- (0 = not set yet), live class days per week (3), retake campaign switch.
-- Editable from the Admin Panel; the app also creates this table itself.
CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(64) NOT NULL PRIMARY KEY,
  v TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (k, v) VALUES
  ('subscription_fee_live', '1450'),
  ('recorded_fee', '0'),
  ('live_days_per_week', '3'),
  ('retake_campaign_active', '1');

-- The 11 seeded courses (also seeded automatically by the app if the table is empty).
-- track: 'academic' (English for Today + Grammar & Composition) or 'spoken'.
INSERT INTO courses (title, class_level, paper, price_bdt, lessons, description, track) VALUES
('English for Today — Class 6 (1st Paper)', '6', '1st Paper', 699, 20, 'NCTB English for Today, Class 6: reading, vocabulary, speaking and writing.', 'academic'),
('English 2nd Paper — Class 6 (Grammar and Composition)', '6', '2nd Paper', 699, 20, 'NCTB English Grammar and Composition, Class 6: nouns, verbs, tenses and sentences.', 'academic'),
('English for Today — Class 7 (1st Paper)', '7', '1st Paper', 699, 20, 'NCTB English for Today, Class 7: reading, vocabulary, speaking and writing.', 'academic'),
('English 2nd Paper — Class 7 (Grammar and Composition)', '7', '2nd Paper', 699, 20, 'NCTB English Grammar and Composition, Class 7: grammar rules, practice and writing.', 'academic'),
('English for Today — Class 8 (1st Paper)', '8', '1st Paper', 799, 22, 'NCTB English for Today, Class 8: reading, vocabulary, speaking and writing.', 'academic'),
('English 2nd Paper — Class 8 (Grammar and Composition)', '8', '2nd Paper', 799, 22, 'NCTB English Grammar and Composition, Class 8: grammar rules, practice and writing.', 'academic'),
('English for Today — Class 9-10 (1st Paper)', '9-10', '1st Paper', 999, 24, 'NCTB English for Today, Classes 9-10 (SSC): seen, unseen, literature and writing.', 'academic'),
('English 2nd Paper — Class 9-10 (Grammar and Composition)', '9-10', '2nd Paper', 999, 24, 'NCTB English Grammar and Composition, Classes 9-10 (SSC): The Noun, Tenses, Modals, Conditionals, Passive, Speech, plus Letters, CV, E-mails, Paragraphs, Completing Stories, Graphs & Charts.', 'academic'),
('English for Today — Class 11-12 (1st Paper)', '11-12', '1st Paper', 1199, 24, 'NCTB English for Today, Classes 11-12 (HSC, 1st Paper): reading and writing.', 'academic'),
('English 2nd Paper — Class 11-12 (Grammar and Composition)', '11-12', '2nd Paper', 1199, 24, 'NCTB Grammar & Composition, Classes 11-12 (HSC, 2nd Paper): grammar and writing.', 'academic'),
('Spoken English — Speak with Confidence', '6-12', 'Spoken English', 1499, 24, 'Redway''s own speaking course: conversation, pronunciation, fluency, group discussion, presentations and interview English, with teacher feedback.', 'spoken');
