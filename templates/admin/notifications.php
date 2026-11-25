<h1>Admin — Send Announcement</h1>
<p><a href="/admin">← Admin Panel</a></p>
<form method="post" action="/admin/notifications">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Message <textarea name="message" rows="3" required></textarea></label>
  <label>Audience <select name="audience"><option value="all">Everyone</option><option value="student">Students only</option><option value="parent">Parents only</option><option value="teacher">Teachers only</option><option value="class">One class only</option></select></label>
  <label>Class level (for "one class only") <input name="class_level" placeholder="e.g. 9-10"></label>
  <button class="btn" type="submit">Send announcement</button>
</form>
<h2>Recent announcements</h2>
<?php foreach ($announcements as $a): ?><div class="card"><p><?= e($a['message']) ?></p><small><?= e($a['created_at']) ?></small></div><?php endforeach; ?>
