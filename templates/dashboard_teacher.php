<?php
// Teacher dashboard — deliberately distinct from student/parent dashboards:
// red accent band, teacher-only stats and actions, Admin Panel front and centre.
$dash_role = 'teacher';
$dash_active = '/dashboard/teacher';
$pending_orders = count(array_filter($orders ?? [], fn($o) => $o['status'] === 'pending'));
$pending_retakes = count(array_filter($retakes ?? [], fn($r) => $r['status'] === 'needs_review'));
$dash_links = [
    ['/dashboard/teacher', t('side_dashboard')],
    ['/dashboard/teacher#overview', t('side_overview')],
    ['/admin/orders', t('side_orders'), $pending_orders],
    ['/admin/retakes', t('nav_retake'), $pending_retakes],
    ['/homework', t('side_homework')],
    ['/progress', t('side_progress')],
    ['/notifications', t('side_notifications'), $unread_count],
    ['/feedback', t('side_feedback')],
    ['/checkin', t('side_checkin')],
    ['/admin', t('side_admin')],
    ['/admin/teachers', t('side_add_teacher')],
    ['/logout', t('menu_logout')],
];
?>
<div class="dash-layout">
  <?php include __DIR__ . '/partials/dash_sidebar.php'; ?>
  <div class="dash-main">
    <h1><?= t('dash_teacher') ?> — <?= e($user['name']) ?><?php if ($is_super_admin): ?> · <?= t('super_admin') ?><?php endif; ?></h1>
    <p class="dash-sub"><?= t('dash_teacher_sub') ?></p>

    <div class="dash-band">
      <h2 style="margin-top:0"><?= t('side_admin') ?></h2>
      <p><?= t('teacher_overview') ?> — <?= t('pending_orders') ?>: <strong><?= num($pending_orders) ?></strong> • <?= t('retakes_review') ?>: <strong><?= num($pending_retakes) ?></strong></p>
      <p class="hero-actions" style="margin-bottom:0">
        <a class="btn btn-red" href="/admin"><?= t('nav_admin') ?> →</a>
        <a class="btn btn-secondary" href="/admin/orders"><?= t('recent_orders') ?></a>
        <a class="btn btn-secondary" href="/admin/teachers"><?= t('add_teacher') ?></a>
      </p>
    </div>

    <section aria-labelledby="teacher-overview" id="overview">
      <h2 id="teacher-overview"><?= t('teacher_overview') ?></h2>
      <div class="stats">
        <div class="stat-card"><p class="stat-value"><?= num($pending_orders) ?></p><p class="stat-label"><?= t('pending_orders') ?></p></div>
        <div class="stat-card"><p class="stat-value"><?= num($pending_retakes) ?></p><p class="stat-label"><?= t('retakes_review') ?></p></div>
        <div class="stat-card"><p class="stat-value"><?= num(count($submissions ?? [])) ?></p><p class="stat-label"><?= t('homework_subs') ?></p></div>
        <div class="stat-card"><p class="stat-value"><?= num(count($students ?? [])) ?></p><p class="stat-label"><?= t('side_students') ?></p></div>
      </div>

      <h3><?= t('recent_orders') ?></h3>
      <div class="dash-list">
      <?php foreach (array_slice($orders ?? [], 0, 5) as $o): ?>
        <div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['student_name']) ?> (<?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, <?= t('sender_phone') ?>: <?= e($o['sender_phone'] ?? '') ?>, <?= tk((int)$o['amount_bdt']) ?>) — <span class="badge"><?= e($o['status']) ?></span></p></div>
      <?php endforeach; ?>
      <?php if (empty($orders ?? [])): ?><p><?= t('no_orders') ?></p><?php endif; ?>
      </div>

      <h3><?= t('recent_retakes') ?></h3>
      <div class="dash-list">
      <?php foreach (array_slice($retakes ?? [], 0, 5) as $r): ?>
        <div class="card"><p><strong><?= e($r['student_name']) ?></strong> — <?= e($r['exam']) ?> <?= e($r['exam_year']) ?> — <span class="badge"><?= e($r['status']) ?></span></p></div>
      <?php endforeach; ?>
      </div>

      <h3><?= t('recent_submissions') ?></h3>
      <div class="dash-list">
      <?php foreach (array_slice($submissions ?? [], 0, 10) as $s): ?>
        <div class="card"><p><strong><?= e($s['student_name']) ?></strong>: <?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div>
      <?php endforeach; ?>
      </div>

      <h3><?= t('add_teacher') ?></h3>
      <p><a class="btn" href="/admin/teachers"><?= t('side_admin') ?> → <?= t('side_add_teacher') ?></a> (<?= t('add_teacher_d') ?>)</p>
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
