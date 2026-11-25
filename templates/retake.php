<?php // Free retake registration. When the campaign is switched OFF in the
// Admin Panel, $campaign_off is true and visitors see a message, not the form.
?>
<?php if (!empty($campaign_off)): ?>
<h1 class="page-title"><?= t('retake_off_title') ?></h1>
<p class="section-lead"><?= t('retake_off_body') ?></p>
<p><a class="btn btn-primary" href="/courses"><?= t('cta_courses') ?></a></p>
<?php else: ?>
<h1 class="page-title"><?= t('retake_title') ?></h1>
<p class="section-lead"><?= sprintf(t('retake_sub'), MAX_UPLOAD_MB) ?></p>
<form method="post" action="/retake" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('student_name') ?> <input name="student_name" required></label>
  <label><?= t('phone') ?> <input name="phone" type="tel" required></label>
  <label><?= t('exam') ?> <select name="exam"><option>SSC</option><option>HSC</option></select></label>
  <label><?= t('exam_year') ?> <input name="exam_year" required pattern="\d{4}" placeholder="<?= e(t('exam_year_ph')) ?>"></label>
  <label><?= t('roll_no') ?> <input name="roll_no" required></label>
  <label><?= t('reg_no') ?> <input name="reg_no" required></label>
  <label><?= t('result') ?> <input name="result_text" required placeholder="<?= e(t('result_ph')) ?>"></label>
  <label><?= t('admit') ?> <input type="file" name="admit_card" accept=".pdf,.jpg,.jpeg,.png"></label>
  <label><?= t('tabulation') ?> <input type="file" name="tabulation" accept=".pdf,.jpg,.jpeg,.png"></label>
  <button class="btn" type="submit"><?= t('submit_retake') ?></button>
</form>
<?php endif; ?>
