<h1>Admin — Check-in PINs</h1>
<p><a href="/admin">← Admin Panel</a></p>
<h2>Create a class PIN</h2>
<form method="post" action="/admin/checkins">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Class level <input name="class_level" placeholder="e.g. 9-10" required></label>
  <button class="btn" type="submit">Create 4-digit PIN</button>
</form>
<h2>PINs</h2>
<table class="admintable"><tr><th>PIN</th><th>Class</th><th>Teacher</th><th>Active</th><th>Created</th><th>Action</th></tr>
<?php foreach ($codes as $c): ?><tr><td><strong><?= e($c['pin']) ?></strong></td><td><?= e($c['class_level']) ?></td><td><?= e($c['teacher_phone']) ?></td><td><?= (int)($c['is_active'] ?? 1) ? 'Yes' : 'No' ?></td><td><?= e($c['created_at']) ?></td>
<td><?php if ((int)($c['is_active'] ?? 1)): ?><form class="inline" method="post" action="/admin/checkins/code/<?= (int)$c['id'] ?>/deactivate"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button type="submit">Deactivate</button></form><?php endif; ?></td></tr><?php endforeach; ?></table>
<h2>Arrivals (latest 30)</h2>
<?php foreach ($checkins as $c): ?><div class="card"><p><strong><?= e($c['student_name']) ?></strong> — Class <?= e($c['class_level']) ?> — <span class="badge <?= $c['status'] === 'confirmed' ? 'green' : 'yellow' ?>"><?= e($c['status']) ?></span> <small>(<?= e($c['created_at']) ?>)</small></p>
<?php if ($c['status'] === 'pending'): ?><form class="inline" method="post" action="/checkin/<?= (int)$c['id'] ?>/confirm"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button class="btn green" type="submit">Confirm arrival</button></form><?php endif; ?></div><?php endforeach; ?>
