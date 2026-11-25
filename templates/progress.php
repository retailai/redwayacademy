<h1 class="page-title"><?= t('progress_title') ?></h1>
<p class="section-lead"><?= t('progress_sub') ?></p>
<?php if ($current_user['role'] === 'teacher'): ?>
<h2><?= t('add_note') ?></h2>
<form method="post" action="/progress">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('student_phone') ?> <input name="student_phone" type="tel" required></label>
  <label><?= t('student_name') ?> <input name="student_name" required></label>
  <label><?= t('score') ?> (0–100) <input name="score" type="number" min="0" max="100" required></label>
  <label><?= t('suggestion') ?> <textarea name="suggestion" rows="3"></textarea></label>
  <button class="btn" type="submit"><?= t('add_btn') ?></button>
</form>
<?php endif; ?>
<h2><?= t('progress_notes') ?></h2>
<div class="dash-list">
<?php foreach ($progress as $p): ?><div class="card"><p><strong><?= e($p['student_name']) ?></strong> — <?= t('score') ?>: <strong><?= num((int)$p['score']) ?></strong></p><p><?= e($p['suggestion']) ?></p><small><?= e($p['created_at']) ?></small></div><?php endforeach; ?>
</div>
<?php if (empty($progress)): ?><p><?= t('no_progress') ?></p><?php endif; ?>
