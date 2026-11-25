<h1>Admin Panel</h1>
<p>Logged in as <strong><?= e($user['name']) ?></strong><?php if ($is_super_admin): ?> — Super Admin<?php endif; ?></p>
<nav class="adminnav">
  <a href="/admin">Dashboard</a><a href="/admin/courses">Courses</a><a href="/admin/users">Users</a>
  <a href="/admin/teachers">Teachers</a><a href="/admin/homework">Homework</a><a href="/admin/orders">Orders</a>
  <a href="/admin/retakes">Retakes</a><a href="/admin/testimonials">Reviews</a><a href="/admin/progress">Progress</a>
  <a href="/admin/notifications">Notify</a><a href="/admin/checkins">Check-in</a>
</nav>
<?php if (!empty($counts)): ?>
<div class="grid">
  <div class="card"><h3>Users</h3><p><?= (int)$counts['users'] ?> total — <?= (int)$counts['students'] ?> students, <?= (int)$counts['parents'] ?> parents, <?= (int)$counts['teachers'] ?> teachers</p></div>
  <div class="card"><h3>Courses</h3><p><?= (int)$counts['courses'] ?> active</p></div>
  <div class="card"><h3>Orders</h3><p><?= (int)$counts['orders_pending'] ?> pending / <?= (int)$counts['orders_total'] ?> total</p></div>
  <div class="card"><h3>Homework</h3><p><?= (int)$counts['submissions_pending'] ?> submissions waiting</p></div>
  <div class="card"><h3>Retakes</h3><p><?= (int)$counts['retakes_pending'] ?> needing review</p></div>
  <div class="card"><h3>Reviews</h3><p><?= (int)$counts['testimonials_pending'] ?> waiting for approval</p></div>
  <div class="card"><h3>Check-ins</h3><p><?= (int)$counts['checkins_pending'] ?> pending arrivals</p></div>
</div>
<h2>Recent orders</h2>
<?php foreach ($recent_orders as $o): ?><div class="card"><p><?= e($o['course_title']) ?> — <?= e($o['student_name']) ?> — <?= e($o['method']) ?> <?= e($o['trxid']) ?> — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
<h2>Recent retakes</h2>
<?php foreach ($recent_retakes as $r): ?><div class="card"><p><?= e($r['student_name']) ?> — <?= e($r['exam']) ?> <?= e($r['exam_year']) ?> — <span class="badge"><?= e($r['status']) ?></span></p></div><?php endforeach; ?>
<h2>Recent feedback</h2>
<?php foreach ($recent_feedback as $f): ?><div class="card"><p><?= e($f['message']) ?> — <strong><?= e($f['author_name']) ?></strong></p></div><?php endforeach; ?>
<?php endif; ?>
