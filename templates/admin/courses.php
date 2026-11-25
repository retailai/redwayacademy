<h1>Admin — Courses</h1>
<p><a href="/admin">← Admin Panel</a> • <a class="btn" href="/admin/courses/new">New course</a></p>
<table class="admintable"><tr><th>ID</th><th>Title</th><th>Class</th><th>Paper</th><th>Track</th><th>Price</th><th>Lessons</th><th>Active</th><th>Actions</th></tr>
<?php foreach ($courses as $c): ?><tr>
<td><?= (int)$c['id'] ?></td><td><?= e($c['title']) ?></td><td><?= e($c['class_level']) ?></td><td><?= e($c['paper']) ?></td><td><?= e(track_label((string)($c['track'] ?? 'academic'))) ?></td>
<td>৳<?= (int)$c['price_bdt'] ?></td><td><?= (int)$c['lessons'] ?></td><td><?= (int)($c['active'] ?? 1) ? 'Yes' : 'No' ?></td>
<td><a href="/admin/courses/<?= (int)$c['id'] ?>/edit">Edit</a>
<form class="inline" method="post" action="/admin/courses/<?= (int)$c['id'] ?>/delete" onsubmit="return confirm('Delete this course?')"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button type="submit">Delete</button></form></td>
</tr><?php endforeach; ?></table>
