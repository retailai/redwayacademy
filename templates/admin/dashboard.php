<h1>Admin Panel</h1>
<p>Logged in as <strong><?= e($user['name']) ?></strong><?php if ($is_super_admin): ?> — Super Admin<?php endif; ?></p>
<nav class="adminnav" aria-label="Admin navigation">
  <a href="/admin" aria-current="page">Dashboard</a><a href="/admin/courses">Courses</a><a href="/admin/users">Users</a>
  <a href="/admin/teachers">Teachers</a><a href="/admin/homework">Homework</a><a href="/admin/orders">Orders</a>
  <a href="/admin/retakes">Retakes</a><a href="/admin/testimonials">Reviews</a><a href="/admin/progress">Progress</a>
  <a href="/admin/notifications">Notify</a><a href="/admin/checkins">Check-in</a>
</nav>
<?php if (!empty($counts)): ?>
<div class="grid">
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['users'] ?></p><p class="stat-label">Users — <?= (int)$counts['students'] ?> students, <?= (int)$counts['parents'] ?> parents, <?= (int)$counts['teachers'] ?> teachers</p></div>
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['courses'] ?></p><p class="stat-label">Active courses</p></div>
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['orders_pending'] ?> / <?= (int)$counts['orders_total'] ?></p><p class="stat-label">Orders pending / total</p></div>
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['submissions_pending'] ?></p><p class="stat-label">Homework submissions waiting</p></div>
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['retakes_pending'] ?></p><p class="stat-label">Retakes needing review</p></div>
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['testimonials_pending'] ?></p><p class="stat-label"><a href="/admin/testimonials">Reviews waiting for approval</a></p></div>
  <div class="card stat-card"><p class="stat-value"><?= (int)$counts['checkins_pending'] ?></p><p class="stat-label">Pending arrivals</p></div>
</div>
<section aria-labelledby="recent-orders">
  <h2 id="recent-orders">Recent orders</h2>
  <?php foreach ($recent_orders as $o): ?><div class="card"><p><?= e($o['course_title']) ?> — <?= e($o['student_name']) ?> — <?= e($o['method']) ?> <?= e($o['trxid']) ?> — sender <?= e($o['sender_phone'] ?? '') ?> — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
</section>
<section aria-labelledby="recent-retakes">
  <h2 id="recent-retakes">Recent retakes</h2>
  <?php foreach ($recent_retakes as $r): ?><div class="card"><p><?= e($r['student_name']) ?> — <?= e($r['exam']) ?> <?= e($r['exam_year']) ?> — <span class="badge"><?= e($r['status']) ?></span></p></div><?php endforeach; ?>
</section>
<section aria-labelledby="recent-feedback">
  <h2 id="recent-feedback">Recent feedback</h2>
  <?php foreach ($recent_feedback as $f): ?><div class="card"><p><?= e($f['message']) ?> — <strong><?= e($f['author_name']) ?></strong></p></div><?php endforeach; ?>
</section>
<?php endif; ?>
