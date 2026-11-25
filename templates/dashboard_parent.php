<h1>Parent Dashboard — <?= e($user['name']) ?></h1>
<p>Phone: <?= e($user['phone']) ?><?php if (!empty($linked_student_name)): ?> • Linked student: <strong><?= e($linked_student_name) ?></strong><?php elseif (!empty($user['linked_student_phone'])): ?> • Linked student phone: <?= e($user['linked_student_phone']) ?><?php else: ?> • No student linked yet — add your child's phone number in your profile.<?php endif; ?></p>
<p class="hero-actions">
  <a class="btn" href="/notifications">Notifications<?php if ($unread_count): ?> (<?= (int)$unread_count ?> unread)<?php endif; ?></a>
  <a class="btn btn-secondary" href="/progress">Progress</a>
  <a class="btn btn-secondary" href="/feedback">Feedback</a>
  <a class="btn btn-secondary" href="/checkin">Check-in</a>
</p>

<section aria-labelledby="my-courses">
  <h2 id="my-courses">My child's courses — সন্তানের কোর্স</h2>
  <?php foreach ($my_courses ?? [] as $mc): ?><div class="card"><p><strong><?= e($mc['title']) ?></strong> — Class <?= e($mc['class_level']) ?> • <?= (int)$mc['lessons'] ?> lessons</p><p class="hero-actions"><a class="btn btn-primary" href="/course/<?= (int)$mc['id'] ?>">Open course</a> <a class="btn btn-secondary" href="/progress">Progress</a></p></div><?php endforeach; ?>
  <?php if (empty($my_courses ?? [])): ?><p>Your linked student is not enrolled in any course yet. <a href="/courses">Browse courses</a> — buy with bKash / Nagad, and a teacher will verify your payment.</p><?php endif; ?>
</section>
<section aria-labelledby="my-progress">
  <h2 id="my-progress">My child's progress</h2>
  <?php foreach ($progress ?? [] as $p): ?><div class="card"><p><strong>Score: <?= (int)$p['score'] ?></strong> — <?= e($p['suggestion']) ?> <small>(<?= e($p['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($progress ?? [])): ?><p>No progress notes yet. The teacher will add scores and suggestions here.</p><?php endif; ?>
</section>
<section aria-labelledby="my-orders">
  <h2 id="my-orders">Payments (linked student)</h2>
  <?php foreach ($orders ?? [] as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, sender <?= e($o['sender_phone'] ?? '') ?> — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
  <?php if (empty($orders ?? [])): ?><p>No orders yet. <a href="/courses">Browse courses</a>.</p><?php endif; ?>
</section>
<section aria-labelledby="checkins">
  <h2 id="checkins">Check-ins (linked student)</h2>
  <?php foreach ($checkins ?? [] as $c): ?><div class="card"><p><?= e($c['student_name']) ?> — Class <?= e($c['class_level']) ?> — <span class="badge"><?= e($c['status']) ?></span> <small>(<?= e($c['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($checkins ?? [])): ?><p>No check-ins yet.</p><?php endif; ?>
</section>

<section aria-labelledby="latest-notifications">
  <h2 id="latest-notifications">Latest notifications</h2>
  <?php foreach (array_slice($notifications ?? [], 0, 10) as $n): ?><div class="card"><p><?= e($n['message']) ?> <small>(<?= e($n['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($notifications ?? [])): ?><p>No notifications yet.</p><?php endif; ?>
</section>
