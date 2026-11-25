<h1>Admin — Teachers</h1>
<p><a href="/admin">← Admin Panel</a></p>
<p>Only an admin can add teachers. The first teacher is the Super Admin.</p>
<h2>Add a teacher</h2>
<form method="post" action="/admin/teachers">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Name <input name="name" required></label>
  <label>Phone <input name="phone" type="tel" required></label>
  <label>Password (4+ characters) <input name="password" type="password" required minlength="4"></label>
  <button class="btn" type="submit">Add teacher</button>
</form>
<h2>Teachers</h2>
<table class="admintable"><tr><th>ID</th><th>Name</th><th>Phone</th><th>Super Admin</th><th>Active</th></tr>
<?php foreach ($teachers as $t): ?><tr><td><?= (int)$t['id'] ?></td><td><?= e($t['name']) ?></td><td><?= e($t['phone']) ?></td><td><?= (int)($t['is_super_admin'] ?? 0) ? 'Yes' : 'No' ?></td><td><?= (int)($t['active'] ?? 1) ? 'Yes' : 'No' ?></td></tr><?php endforeach; ?></table>
