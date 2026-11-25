<h1>Admin — Homework</h1>
<p><a href="/admin">← Admin Panel</a></p>
<h2>Homework</h2>
<table class="admintable"><tr><th>ID</th><th>Title</th><th>Class</th><th>Details</th><th>Actions</th></tr>
<?php foreach ($homework as $h): ?><tr><td><?= (int)$h['id'] ?></td><td><?= e($h['title']) ?></td><td><?= e($h['class_level']) ?></td><td><?= e($h['details']) ?></td>
<td><a href="/admin/homework/<?= (int)$h['id'] ?>/edit">Edit</a>
<form class="inline" method="post" action="/admin/homework/<?= (int)$h['id'] ?>/delete" onsubmit="return confirm('Delete this homework?')"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button type="submit">Delete</button></form></td></tr><?php endforeach; ?></table>
<h2>Submissions (latest 30)</h2>
<?php foreach ($submissions as $s): ?><div class="card"><p><strong><?= e($s['student_name']) ?></strong> (<?= e($s['student_phone']) ?>) — HW #<?= (int)$s['homework_id'] ?>: <?= e(mb_substr($s['answer'] ?? '', 0, 200)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p>
<form method="post" action="/homework/submission/<?= (int)$s['id'] ?>/review">
<input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
<select name="status"><option value="checked">checked</option><option value="needs work">needs work</option></select>
<input name="teacher_comment" placeholder="Teacher comment">
<button class="btn" type="submit">Review</button></form></div><?php endforeach; ?>
