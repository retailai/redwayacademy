<h1 class="page-title"><?= t('notif_title') ?></h1>
<p class="section-lead"><?= t('notif_sub') ?></p>
<div class="dash-list">
<?php foreach ($notifications as $n): ?><div class="card"><p><span class="badge"><?= e($n['kind']) ?></span> <?= e($n['message']) ?></p><small><?= e($n['created_at']) ?></small></div><?php endforeach; ?>
</div>
<?php if (empty($notifications)): ?><p><?= t('no_notifications') ?></p><?php endif; ?>
