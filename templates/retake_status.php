<h1>Retake Registration — Status</h1>
<div class="card">
  <p><strong><?= e($reg['student_name']) ?></strong> — <?= e($reg['exam']) ?> <?= e($reg['exam_year']) ?></p>
  <p>Roll: <?= e($reg['roll_no']) ?> • Registration: <?= e($reg['reg_no']) ?></p>
  <p>Result: <?= e($reg['result_text']) ?></p>
  <p>Status: <span class="badge <?= $reg['status'] === 'auto_verified' ? 'green' : 'yellow' ?>"><?= e(str_replace('_', ' ', $reg['status'])) ?></span></p>
  <p><?= e($reg['reasons']) ?></p>
  <p><small>Submitted <?= e($reg['created_at']) ?>. A teacher reviews registrations marked "needs review".</small></p>
</div>
<p><a class="btn" href="/">Back to home</a></p>
