<h1>Notifications</h1>
<p>Homework, progress, payments, retake results, announcements and arrivals — in-app only.</p>
<?php foreach ($notifications as $n): ?><div class="card"><p><span class="badge"><?= e($n['kind']) ?></span> <?= e($n['message']) ?></p><small><?= e($n['created_at']) ?></small></div><?php endforeach; ?>
<?php if (empty($notifications)): ?><p>No notifications yet.</p><?php endif; ?>
