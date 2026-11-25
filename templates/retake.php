<h1>Free SSC / HSC Retake Registration</h1>
<p>Free registration for SSC / HSC retake students. Upload your admit card and tabulation sheet (PDF / JPG / PNG, max <?= MAX_UPLOAD_MB ?> MB each). We never auto-reject: if anything is missing or unreadable, a teacher reviews it.</p>
<form method="post" action="/retake" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Student name <input name="student_name" required></label>
  <label>Phone <input name="phone" type="tel" required></label>
  <label>Exam <select name="exam"><option>SSC</option><option>HSC</option></select></label>
  <label>Exam year <input name="exam_year" required pattern="\d{4}" placeholder="e.g. 2026"></label>
  <label>Roll number <input name="roll_no" required></label>
  <label>Registration number <input name="reg_no" required></label>
  <label>Result <input name="result_text" required placeholder="e.g. GPA 4.50 / Failed in English"></label>
  <label>Admit card (PDF / JPG / PNG) <input type="file" name="admit_card" accept=".pdf,.jpg,.jpeg,.png"></label>
  <label>Tabulation sheet (PDF / JPG / PNG) <input type="file" name="tabulation" accept=".pdf,.jpg,.jpeg,.png"></label>
  <button class="btn" type="submit">Submit free registration</button>
</form>
