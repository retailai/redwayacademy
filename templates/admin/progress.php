<h1>Admin — Progress &amp; Feedback</h1>
<p><a href="/admin">← Admin Panel</a></p>
<h2>Progress notes</h2>
<?php foreach ($progress as $p): ?><div class="card"><p><strong><?= e($p['student_name']) ?></strong> (<?= e($p['student_phone']) ?>) — Score: <strong><?= (int)$p['score'] ?></strong></p><p><?= e($p['suggestion']) ?></p><small><?= e($p['created_at']) ?></small>
<form class="inline" method="post" action="/admin/progress/<?= (int)$p['id'] ?>/delete" onsubmit="return confirm('Delete this note?')"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button type="submit">Delete</button></form></div><?php endforeach; ?>
<?php if (empty($progress)): ?><p>No progress notes yet.</p><?php endif; ?>
<h2>Feedback from students / parents</h2>
<?php foreach ($feedback as $f): ?><div class="card"><p><?= e($f['message']) ?></p><p><strong><?= e($f['author_name']) ?></strong> (<?= e($f['author_phone']) ?>) <small><?= e($f['created_at']) ?></small></p></div><?php endforeach; ?>
