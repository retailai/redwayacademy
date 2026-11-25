<?php
// Parent dashboard — green accent, child-focused.
$dash_role = 'parent';
$dash_active = '/dashboard/parent';
$dash_links = [
    ['/dashboard/parent', t('side_dashboard')],
    ['/dashboard/parent#my-courses', t('side_courses'), count($my_courses ?? [])],
    ['/progress', t('side_progress')],
    ['/dashboard/parent#my-orders', t('side_orders'), count($orders ?? [])],
    ['/dashboard/parent#checkins', t('side_checkin'), count($checkins ?? [])],
    ['/notifications', t('side_notifications'), $unread_count],
    ['/feedback', t('side_feedback')],
];
?>
<div class="dash-layout">
  <?php include __DIR__ . '/partials/dash_sidebar.php'; ?>
  <div class="dash-main">
    <h1><?= t('dash_parent') ?> — <?= e($user['name']) ?></h1>
    <p class="dash-sub"><?= t('dash_parent_sub') ?></p>
    <p><?php if (!empty($linked_student_name)): ?><?= t('linked_student') ?>: <strong><?= e($linked_student_name) ?></strong><?php elseif (!empty($user['linked_student_phone'])): ?><?= t('linked_student') ?>: <?= e($user['linked_student_phone']) ?><?php else: ?><?= t('no_student_linked') ?><?php endif; ?></p>

    <section aria-labelledby="my-courses" id="my-courses">
      <h2 id="my-courses"><?= t('my_child_courses') ?></h2>
      <div class="dash-list">
      <?php foreach ($my_courses ?? [] as $mc): ?>
        <div class="card"><p><strong><?= e($mc['title']) ?></strong> — <?= t('class') ?> <?= e($mc['class_level']) ?> • <?= num((int)$mc['lessons']) ?> <?= t('lessons') ?></p>
        <p class="hero-actions"><a class="btn btn-primary" href="/course/<?= (int)$mc['id'] ?>"><?= t('open_course') ?></a><a class="btn btn-secondary" href="/progress"><?= t('side_progress') ?></a></p></div>
      <?php endforeach; ?>
      <?php if (empty($my_courses ?? [])): ?>
        <div class="card"><p><strong><?= t('no_child_courses') ?></strong></p><p><?= t('no_courses_yet_d') ?></p><p><a class="btn btn-primary" href="/courses"><?= t('browse_courses') ?></a></p></div>
      <?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="my-progress" id="my-progress">
      <h2 id="my-progress"><?= t('my_child_progress') ?></h2>
      <div class="dash-list">
      <?php foreach ($progress ?? [] as $p): ?>
        <div class="card"><p><strong><?= t('score') ?>: <?= num((int)$p['score']) ?></strong> — <?= e($p['suggestion']) ?> <small>(<?= e($p['created_at']) ?>)</small></p></div>
      <?php endforeach; ?>
      <?php if (empty($progress ?? [])): ?><p><?= t('no_progress') ?></p><?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="my-orders" id="my-orders">
      <h2 id="my-orders"><?= t('payments') ?></h2>
      <div class="dash-list">
      <?php foreach ($orders ?? [] as $o): ?>
        <div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, <?= t('sender_phone') ?>: <?= e($o['sender_phone'] ?? '') ?> — <span class="badge"><?= e($o['status']) ?></span></p></div>
      <?php endforeach; ?>
      <?php if (empty($orders ?? [])): ?><p><?= t('no_orders') ?> <a href="/courses"><?= t('browse_courses') ?></a></p><?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="checkins" id="checkins">
      <h2 id="checkins"><?= t('checkins') ?></h2>
      <div class="dash-list">
      <?php foreach ($checkins ?? [] as $c): ?>
        <div class="card"><p><?= e($c['student_name']) ?> — <?= t('class') ?> <?= e($c['class_level']) ?> — <span class="badge <?= $c['status'] === 'confirmed' ? 'green' : 'yellow' ?>"><?= e($c['status']) ?></span> <small>(<?= e($c['created_at']) ?>)</small></p></div>
      <?php endforeach; ?>
      <?php if (empty($checkins ?? [])): ?><p><?= t('no_checkins') ?></p><?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="latest-notifications">
      <h2 id="latest-notifications"><?= t('latest_notifications') ?></h2>
      <div class="dash-list">
      <?php foreach (array_slice($notifications ?? [], 0, 10) as $n): ?>
        <div class="card"><p><?= e($n['message']) ?> <small>(<?= e($n['created_at']) ?>)</small></p></div>
      <?php endforeach; ?>
      <?php if (empty($notifications ?? [])): ?><p><?= t('no_notifications') ?></p><?php endif; ?>
      </div>
    </section>
  </div>
</div>
