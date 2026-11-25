<h1 class="page-title"><?= t('status_title') ?></h1>
<div class="card">
  <p><strong><?= e($reg['student_name']) ?></strong> — <?= e($reg['exam']) ?> <?= e($reg['exam_year']) ?></p>
  <p><?= t('roll') ?>: <?= e($reg['roll_no']) ?> • <?= t('registration') ?>: <?= e($reg['reg_no']) ?></p>
  <p><?= t('result') ?>: <?= e($reg['result_text']) ?></p>
  <p><?= t('status') ?>: <span class="badge <?= $reg['status'] === 'auto_verified' ? 'green' : 'yellow' ?>"><?= e(str_replace('_', ' ', $reg['status'])) ?></span></p>
  <p><?= e($reg['reasons']) ?></p>
  <p><small><?= sprintf(t('retake_review_note'), e($reg['created_at'])) ?></small></p>
</div>
<p><a class="btn" href="/"><?= t('back_home') ?></a></p>
