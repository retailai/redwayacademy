<h1>Teacher Dashboard — <?= e($user['name']) ?><?php if ($is_super_admin): ?>, Super Admin<?php endif; ?></h1>
<p>Phone: <?= e($user['phone']) ?></p>
<p class="hero-actions">
  <a class="btn" href="/notifications">Notifications<?php if ($unread_count): ?> (<?= (int)$unread_count ?> unread)<?php endif; ?></a>
  <a class="btn btn-secondary" href="/homework">Homework</a>
  <a class="btn btn-secondary" href="/progress">Progress</a>
  <a class="btn btn-secondary" href="/feedback">Feedback</a>
  <a class="btn btn-secondary" href="/checkin">Check-in</a>
  <a class="btn btn-yellow" href="/admin">Admin Panel</a>
</p>

<section aria-labelledby="teacher-overview">
  <h2 id="teacher-overview">Teacher overview</h2>
  <div class="grid grid--2">
    <div class="card stat-card"><p class="stat-value"><?= count(array_filter($orders ?? [], fn($o) => $o['status'] === 'pending')) ?></p><p class="stat-label">Pending orders</p></div>
    <div class="card stat-card"><p class="stat-value"><?= count(array_filter($retakes ?? [], fn($r) => $r['status'] === 'needs_review')) ?></p><p class="stat-label">Retakes needing review</p></div>
    <div class="card stat-card"><p class="stat-value"><?= count($submissions ?? []) ?></p><p class="stat-label">Homework submissions</p></div>
  </div>
  <h3>Recent orders</h3>
  <?php foreach (array_slice($orders ?? [], 0, 5) as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['student_name']) ?> (<?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, sender <?= e($o['sender_phone'] ?? '') ?>, ৳<?= (int)$o['amount_bdt'] ?>) — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
  <h3>Recent retake registrations</h3>
  <?php foreach (array_slice($retakes ?? [], 0, 5) as $r): ?><div class="card"><p><strong><?= e($r['student_name']) ?></strong> — <?= e($r['exam']) ?> <?= e($r['exam_year']) ?> — <span class="badge"><?= e($r['status']) ?></span></p></div><?php endforeach; ?>
  <h3>Recent homework submissions</h3>
  <?php foreach (array_slice($submissions ?? [], 0, 10) as $s): ?><div class="card"><p><strong><?= e($s['student_name']) ?></strong>: <?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div><?php endforeach; ?>
  <h3>Add a teacher</h3>
  <p><a class="btn" href="/admin/teachers">Admin → Teachers</a> (teachers are added by an admin only).</p>
</section>

<section aria-labelledby="latest-notifications">
  <h2 id="latest-notifications">Latest notifications</h2>
  <?php foreach (array_slice($notifications ?? [], 0, 10) as $n): ?><div class="card"><p><?= e($n['message']) ?> <small>(<?= e($n['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($notifications ?? [])): ?><p>No notifications yet.</p><?php endif; ?>
</section>
