<h1 class="page-title"><?= t('checkin_title') ?></h1>
<p class="section-lead"><?= t('checkin_sub') ?></p>
<?php if ($current_user['role'] === 'teacher'): ?>
<h2><?= t('create_pin') ?></h2>
<form method="post" action="/checkin">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('class_level') ?> <input name="class_level" placeholder="<?= e(t('class_level_ph')) ?>" required></label>
  <button class="btn" type="submit"><?= t('create_pin') ?></button>
</form>
<h2><?= t('active_pins') ?></h2>
<div class="dash-list">
<?php foreach ($codes as $c): ?><div class="card"><p>PIN <strong><?= e($c['pin']) ?></strong> — <?= t('class') ?> <?= e($c['class_level']) ?> <small>(<?= e($c['created_at']) ?>)</small></p></div><?php endforeach; ?>
</div>
<h2><?= t('pending_arrivals') ?></h2>
<div class="dash-list">
<?php foreach ($pending as $p): ?><div class="card"><p><strong><?= e($p['student_name']) ?></strong> — <?= t('class') ?> <?= e($p['class_level']) ?> — PIN <?= e($p['pin']) ?> <small>(<?= e($p['created_at']) ?>)</small></p>
<form class="inline" method="post" action="/checkin/<?= (int)$p['id'] ?>/confirm"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button class="btn green" type="submit"><?= t('confirm_arrival') ?></button></form></div><?php endforeach; ?>
</div>
<?php if (empty($pending)): ?><p><?= t('no_pending') ?></p><?php endif; ?>
<?php else: ?>
<h2><?= t('enter_pin') ?></h2>
<form method="post" action="/checkin">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('pin_4') ?> <input name="pin" required pattern="\d{4}" maxlength="4" placeholder="1234"></label>
  <button class="btn" type="submit"><?= t('checkin_btn') ?></button>
</form>
<p><?= t('checkin_teacher_note') ?></p>
<?php endif; ?>
<h2><?= t('my_checkins') ?></h2>
<div class="dash-list">
<?php foreach ($mine as $m): ?><div class="card"><p><?= t('class') ?> <?= e($m['class_level']) ?> — <span class="badge <?= $m['status'] === 'confirmed' ? 'green' : 'yellow' ?>"><?= e($m['status']) ?></span> <small>(<?= e($m['created_at']) ?>)</small></p></div><?php endforeach; ?>
</div>
<?php if (empty($mine)): ?><p><?= t('no_checkins') ?></p><?php endif; ?>
