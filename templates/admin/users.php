<h1>Admin — Users</h1>
<p><a href="/admin">← Admin Panel</a></p>
<?php if (!$is_super_admin): ?><p><em>Only the Super Admin can change roles or deactivate accounts.</em></p><?php endif; ?>
<table class="admintable"><tr><th>ID</th><th>Name</th><th>Phone</th><th>Role</th><th>Class</th><th>Linked student</th><th>Super Admin</th><th>Active</th><th>Actions</th></tr>
<?php foreach ($users as $u): ?><tr>
<td><?= (int)$u['id'] ?></td><td><?= e($u['name']) ?></td><td><?= e($u['phone']) ?></td><td><?= e($u['role']) ?></td>
<td><?= e($u['class_level'] ?? '') ?></td><td><?= e($u['linked_student_phone'] ?? '') ?></td>
<td><?= (int)($u['is_super_admin'] ?? 0) ? 'Yes' : 'No' ?></td><td><?= (int)($u['active'] ?? 1) ? 'Yes' : 'No' ?></td>
<td><?php if ($is_super_admin): ?>
<form class="inline" method="post" action="/admin/users/<?= (int)$u['id'] ?>/role"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
<select name="role"><option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>student</option><option value="parent" <?= $u['role'] === 'parent' ? 'selected' : '' ?>>parent</option><option value="teacher" <?= $u['role'] === 'teacher' ? 'selected' : '' ?>>teacher</option></select>
<button type="submit">Set role</button></form>
<form class="inline" method="post" action="/admin/users/<?= (int)$u['id'] ?>/toggle"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button type="submit"><?= (int)($u['active'] ?? 1) ? 'Deactivate' : 'Activate' ?></button></form>
<?php endif; ?></td>
</tr><?php endforeach; ?></table>
