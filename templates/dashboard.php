<h1>Dashboard — <?= e($user['name']) ?> (<?= e($user['role']) ?><?php if ($is_super_admin): ?>, Super Admin<?php endif; ?>)</h1>
<p>Phone: <?= e($user['phone']) ?><?php if (!empty($user['class_level'])): ?> • Class <?= e($user['class_level']) ?><?php endif; ?></p>
<p class="hero-actions">
  <a class="btn" href="/notifications">Notifications<?php if ($unread_count): ?> (<?= (int)$unread_count ?> unread)<?php endif; ?></a>
  <a class="btn btn-secondary" href="/homework">Homework</a>
  <a class="btn btn-secondary" href="/progress">My Progress</a>
  <a class="btn btn-secondary" href="/feedback">Feedback</a>
  <a class="btn btn-secondary" href="/checkin">Check-in</a>
  <?php if ($user['role'] === 'teacher'): ?> <a class="btn btn-yellow" href="/admin">Admin Panel</a><?php endif; ?>
</p>

<?php if ($user['role'] === 'teacher'): ?>
<section aria-labelledby="teacher-overview">
  <h2 id="teacher-overview">Teacher overview</h2>
  <div class="grid grid--2">
    <div class="card stat-card"><p class="stat-value"><?= count(array_filter($orders ?? [], fn($o) => $o['status'] === 'pending')) ?></p><p class="stat-label">Pending orders</p></div>
    <div class="card stat-card"><p class="stat-value"><?= count(array_filter($retakes ?? [], fn($r) => $r['status'] === 'needs_review')) ?></p><p class="stat-label">Retakes needing review</p></div>
    <div class="card stat-card"><p class="stat-value"><?= count($submissions ?? []) ?></p><p class="stat-label">Homework submissions</p></div>
  </div>
  <h3>Recent orders</h3>
  <?php foreach (array_slice($orders ?? [], 0, 5) as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['student_name']) ?> (<?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, ৳<?= (int)$o['amount_bdt'] ?>) — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
  <h3>Recent retake registrations</h3>
  <?php foreach (array_slice($retakes ?? [], 0, 5) as $r): ?><div class="card"><p><strong><?= e($r['student_name']) ?></strong> — <?= e($r['exam']) ?> <?= e($r['exam_year']) ?> — <span class="badge"><?= e($r['status']) ?></span></p></div><?php endforeach; ?>
  <h3>Recent homework submissions</h3>
  <?php foreach (array_slice($submissions ?? [], 0, 10) as $s): ?><div class="card"><p><strong><?= e($s['student_name']) ?></strong>: <?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div><?php endforeach; ?>
  <h3>Add a teacher</h3>
  <p><a class="btn" href="/admin/teachers">Admin → Teachers</a> (teachers are added by an admin only).</p>
</section>
<?php else: ?>
<section aria-labelledby="my-courses">
  <h2 id="my-courses">My Courses — আমার কোর্স</h2>
  <?php foreach ($my_courses ?? [] as $mc): ?><div class="card"><p><strong><?= e($mc['title']) ?></strong> — Class <?= e($mc['class_level']) ?> • <?= (int)$mc['lessons'] ?> lessons</p><p class="hero-actions"><a class="btn btn-primary" href="/course/<?= (int)$mc['id'] ?>">Open course</a> <a class="btn btn-secondary" href="/homework">Homework</a> <a class="btn btn-secondary" href="/progress">My Progress</a></p></div><?php endforeach; ?>
  <?php if (empty($my_courses ?? [])): ?><p><?= $user['role'] === 'parent' ? 'Your linked student is not enrolled in any course yet.' : 'You are not enrolled in any course yet.' ?> <a href="/courses">Browse courses</a> — buy with bKash / Nagad, and a teacher will verify your payment.</p><?php endif; ?>
</section>
<section aria-labelledby="my-progress">
  <h2 id="my-progress">My progress</h2>
  <?php foreach ($progress ?? [] as $p): ?><div class="card"><p><strong>Score: <?= (int)$p['score'] ?></strong> — <?= e($p['suggestion']) ?> <small>(<?= e($p['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($progress ?? [])): ?><p>No progress notes yet. Your teacher will add scores and suggestions here.</p><?php endif; ?>
</section>
<section aria-labelledby="my-orders">
  <h2 id="my-orders">My orders</h2>
  <?php foreach ($orders ?? [] as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?> — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
  <?php if (empty($orders ?? [])): ?><p>No orders yet. <a href="/courses">Browse courses</a>.</p><?php endif; ?>
</section>
<?php if ($user['role'] === 'parent'): ?>
<section aria-labelledby="checkins">
  <h2 id="checkins">Check-ins (linked student)</h2>
  <?php foreach ($checkins ?? [] as $c): ?><div class="card"><p><?= e($c['student_name']) ?> — Class <?= e($c['class_level']) ?> — <span class="badge"><?= e($c['status']) ?></span> <small>(<?= e($c['created_at']) ?>)</small></p></div><?php endforeach; ?>
</section>
<?php endif; ?>
<?php if ($user['role'] === 'student'): ?>
<section aria-labelledby="homework">
  <h2 id="homework">Homework</h2>
  <?php foreach ($homework ?? [] as $h): ?><div class="card"><h3><?= e($h['title']) ?></h3><p><?= e($h['details']) ?> <small>(Class <?= e($h['class_level']) ?>)</small></p></div><?php endforeach; ?>
  <h2>My submissions</h2>
  <?php foreach ($my_submissions ?? [] as $s): ?><div class="card"><p><?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div><?php endforeach; ?>
</section>
<?php endif; ?>
<?php endif; ?>

<section aria-labelledby="latest-notifications">
  <h2 id="latest-notifications">Latest notifications</h2>
  <?php foreach (array_slice($notifications ?? [], 0, 10) as $n): ?><div class="card"><p><?= e($n['message']) ?> <small>(<?= e($n['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($notifications ?? [])): ?><p>No notifications yet.</p><?php endif; ?>
</section>
