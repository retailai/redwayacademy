<h1 class="page-title"><?= t('feedback_title') ?></h1>
<p class="section-lead"><?= t('feedback_sub') ?></p>
<form method="post" action="/feedback">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('your_feedback') ?> <textarea name="message" rows="4" required></textarea></label>
  <button class="btn" type="submit"><?= t('send_feedback') ?></button>
</form>
<h2><?= t('recent_feedback') ?></h2>
<div class="dash-list">
<?php foreach ($feedback as $f): ?><div class="card"><p><?= e($f['message']) ?></p><p><strong><?= e($f['author_name']) ?></strong> <small>(<?= e($f['created_at']) ?>)</small></p></div><?php endforeach; ?>
</div>
<?php if (empty($feedback)): ?><p><?= t('no_feedback') ?></p><?php endif; ?>
