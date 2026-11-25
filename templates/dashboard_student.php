<h1>Student Dashboard — <?= e($user['name']) ?></h1>
<p>Phone: <?= e($user['phone']) ?><?php if (!empty($user['class_level'])): ?> • Class <?= e($user['class_level']) ?><?php endif; ?></p>
<p class="hero-actions">
  <a class="btn" href="/notifications">Notifications<?php if ($unread_count): ?> (<?= (int)$unread_count ?> unread)<?php endif; ?></a>
  <a class="btn btn-secondary" href="/homework">Homework</a>
  <a class="btn btn-secondary" href="/progress">My Progress</a>
  <a class="btn btn-secondary" href="/feedback">Feedback</a>
  <a class="btn btn-secondary" href="/checkin">Check-in</a>
</p>

<section aria-labelledby="my-courses">
  <h2 id="my-courses">My Courses — আমার কোর্স</h2>
  <?php foreach ($my_courses ?? [] as $mc): ?><div class="card"><p><strong><?= e($mc['title']) ?></strong> — Class <?= e($mc['class_level']) ?> • <?= (int)$mc['lessons'] ?> lessons</p><p class="hero-actions"><a class="btn btn-primary" href="/course/<?= (int)$mc['id'] ?>">Open course</a> <a class="btn btn-secondary" href="/homework">Homework</a> <a class="btn btn-secondary" href="/progress">My Progress</a></p></div><?php endforeach; ?>
  <?php if (empty($my_courses ?? [])): ?><p>You are not enrolled in any course yet. <a href="/courses">Browse courses</a> — buy with bKash / Nagad, and a teacher will verify your payment.</p><?php endif; ?>
</section>
<section aria-labelledby="my-progress">
  <h2 id="my-progress">My progress</h2>
  <?php foreach ($progress ?? [] as $p): ?><div class="card"><p><strong>Score: <?= (int)$p['score'] ?></strong> — <?= e($p['suggestion']) ?> <small>(<?= e($p['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($progress ?? [])): ?><p>No progress notes yet. Your teacher will add scores and suggestions here.</p><?php endif; ?>
</section>
<section aria-labelledby="my-orders">
  <h2 id="my-orders">My orders</h2>
  <?php foreach ($orders ?? [] as $o): ?><div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, sender <?= e($o['sender_phone'] ?? '') ?> — <span class="badge"><?= e($o['status']) ?></span></p></div><?php endforeach; ?>
  <?php if (empty($orders ?? [])): ?><p>No orders yet. <a href="/courses">Browse courses</a>.</p><?php endif; ?>
</section>
<section aria-labelledby="homework">
  <h2 id="homework">Homework</h2>
  <?php foreach ($homework ?? [] as $h): ?><div class="card"><h3><?= e($h['title']) ?></h3><p><?= e($h['details']) ?> <small>(Class <?= e($h['class_level']) ?>)</small></p></div><?php endforeach; ?>
  <h2>My submissions</h2>
  <?php foreach ($my_submissions ?? [] as $s): ?><div class="card"><p><?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div><?php endforeach; ?>
</section>

<section aria-labelledby="latest-notifications">
  <h2 id="latest-notifications">Latest notifications</h2>
  <?php foreach (array_slice($notifications ?? [], 0, 10) as $n): ?><div class="card"><p><?= e($n['message']) ?> <small>(<?= e($n['created_at']) ?>)</small></p></div><?php endforeach; ?>
  <?php if (empty($notifications ?? [])): ?><p>No notifications yet.</p><?php endif; ?>
</section>
