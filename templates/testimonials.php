<h1 class="page-title"><?= t('reviews_page_title') ?></h1>
<p class="section-lead"><?= t('reviews_page_sub') ?></p>
<div class="grid">
<?php foreach ($testimonials as $t): ?><div class="card"><p>"<?= e($t['message']) ?>"</p><p><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, <?= t('class') ?> <?= e($t['class_level']) ?><?php endif; ?></p></div><?php endforeach; ?>
</div>
<?php if (empty($testimonials)): ?><p><?= t('no_reviews') ?></p><?php endif; ?>
<h2><?= t('share_title') ?></h2>
<div class="auth-card">
<form method="post" action="/testimonials">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('your_name') ?> <input name="author_name" required></label>
  <label><?= t('i_am') ?> <select name="role"><option><?= t('role_student') ?></option><option><?= t('role_parent') ?></option></select></label>
  <label><?= t('class') ?> <input name="class_level" placeholder="<?= e(t('class_level_ph')) ?>"></label>
  <label><?= t('your_message') ?> <textarea name="message" rows="4" required></textarea></label>
  <button class="btn" type="submit"><?= t('send_approval') ?></button>
</form>
</div>
