<h1>Dashboard — <?= e($user['name']) ?> (<?= e($user['role']) ?><?php if ($is_super_admin): ?>, Super Admin<?php endif; ?>)</h1>
<p>Phone: <?= e($user['phone']) ?><?php if (!empty($user['class_level'])): ?> • Class <?= e($user['class_level']) ?><?php endif; ?></p>
<p><a class="btn" href="/notifications">Notifications<?php if ($unread_count): ?> (<?= (int)$unread_count ?> unread)<?php endif; ?></a>
<a class="btn" href="/homework">Homework</a> <a class="btn" href="/progress">My Progress</a>
<a class="btn" href="/feedback">Feedback</a> <a class="btn" href="/checkin">Check-in</a>
<?php if ($user['role'] === 'teacher'): ?> <a class="btn yellow" href="/admin">Admin Panel</a><?php endif; ?></p>

<?php if ($user['role'] === 'teacher'): ?>
<h2>Teacher overview</h2>
<p>Pending orders: <?= count(array_filter($orders ?? [], fn($o) => $o['status'] === 'pending')) ?> • Retakes needing review: <?= count(array_filter($retakes ?? [], fn($r) => $r['status'] === 'needs_review')) ?> • Homework submissions: <?= count($submissions ?? []) ?></p>
<h3>Recent orders</h3>
<?php foreach (array_slice($orders ?? [], 0, 5) as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['student_name']) ?> (<?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, ৳<?= (int)$o['amount_bdt'] ?>) — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
<h3>Recent retake registrations</h3>
<?php foreach (array_slice($retakes ?? [], 0, 5) as $r): ?><div class="card"><p><strong><?= e($r['student_name']) ?></strong> — <?= e($r['exam']) ?> <?= e($r['exam_year']) ?> — <span class="badge"><?= e($r['status']) ?></span></p></div><?php endforeach; ?>
<h3>Recent homework submissions</h3>
<?php foreach (array_slice($submissions ?? [], 0, 10) as $s): ?><div class="card"><p><strong><?= e($s['student_name']) ?></strong>: <?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div><?php endforeach; ?>
<h3>Add a teacher</h3>
<p><a class="btn" href="/admin/teachers">Admin → Teachers</a> (teachers are added by an admin only).</p>
<?php else: ?>
<h2>My progress</h2>
<?php foreach ($progress ?? [] as $p): ?><div class="card"><p><strong>Score: <?= (int)$p['score'] ?></strong> — <?= e($p['suggestion']) ?> <small>(<?= e($p['created_at']) ?>)</small></p></div><?php endforeach; ?>
<?php if (empty($progress ?? [])): ?><p>No progress notes yet. Your teacher will add scores and suggestions here.</p><?php endif; ?>
<h2>My orders</h2>
<?php foreach ($orders ?? [] as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?> — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
<?php if (empty($orders ?? [])): ?><p>No orders yet. <a href="/courses">Browse courses</a>.</p><?php endif; ?>
<?php if ($user['role'] === 'parent'): ?>
<h2>Check-ins (linked student)</h2>
<?php foreach ($checkins ?? [] as $c): ?><div class="card"><p><?= e($c['student_name']) ?> — Class <?= e($c['class_level']) ?> — <span class="badge"><?= e($c['status']) ?></span> <small>(<?= e($c['created_at']) ?>)</small></p></div><?php endforeach; ?>
<?php endif; ?>
<?php if ($user['role'] === 'student'): ?>
<h2>Homework</h2>
<?php foreach ($homework ?? [] as $h): ?><div class="card"><h3><?= e($h['title']) ?></h3><p><?= e($h['details']) ?> <small>(Class <?= e($h['class_level']) ?>)</small></p></div><?php endforeach; ?>
<h2>My submissions</h2>
<?php foreach ($my_submissions ?? [] as $s): ?><div class="card"><p><?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div><?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

<h2>Latest notifications</h2>
<?php foreach (array_slice($notifications ?? [], 0, 10) as $n): ?><div class="card"><p><?= e($n['message']) ?> <small>(<?= e($n['created_at']) ?>)</small></p></div><?php endforeach; ?>
<?php if (empty($notifications ?? [])): ?><p>No notifications yet.</p><?php endif; ?>
