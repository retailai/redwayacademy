<h1 class="page-title"><?= t('homework_title') ?></h1>
<?php if ($current_user['role'] === 'teacher'): ?>
<h2><?= t('create_homework') ?></h2>
<form method="post" action="/homework">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('hw_title') ?> <input name="title" required></label>
  <label><?= t('class_level') ?> <input name="class_level" placeholder="<?= e(t('class_level_ph')) ?>"></label>
  <label><?= t('hw_details') ?> <textarea name="details" rows="4"></textarea></label>
  <button class="btn" type="submit"><?= t('create_homework') ?></button>
</form>
<?php endif; ?>
<h2><?= t('all_homework') ?></h2>
<div class="dash-list">
<?php foreach ($homework as $h): ?>
<div class="card">
  <h3><?= e($h['title']) ?> <small>(<?= t('class') ?> <?= e($h['class_level']) ?>)</small></h3>
  <p><?= e($h['details']) ?></p>
  <?php if ($current_user['role'] === 'student'): ?>
  <form method="post" action="/homework/<?= (int)$h['id'] ?>/submit">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label><?= t('your_answer') ?> <textarea name="answer" rows="3" required></textarea></label>
    <label><?= t('file_link') ?> <input name="file_link" placeholder="<?= e(t('file_link_ph')) ?>"></label>
    <button class="btn" type="submit"><?= t('submit_homework') ?></button>
  </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<?php if (empty($homework)): ?><p><?= t('no_homework') ?></p><?php endif; ?>
