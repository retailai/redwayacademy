<?php
// Student dashboard — clearly its own page: blue accent, "My Courses" first.
$dash_role = 'student';
$dash_active = '/dashboard/student';
$dash_links = [
    ['/dashboard/student', t('side_dashboard')],
    ['/dashboard/student#my-courses', t('side_courses'), count($my_courses ?? [])],
    ['/homework', t('side_homework')],
    ['/progress', t('side_progress')],
    ['/dashboard/student#my-orders', t('side_orders'), count($orders ?? [])],
    ['/notifications', t('side_notifications'), $unread_count],
    ['/feedback', t('side_feedback')],
    ['/checkin', t('side_checkin')],
];
?>
<div class="dash-layout">
  <?php include __DIR__ . '/partials/dash_sidebar.php'; ?>
  <div class="dash-main">
    <h1><?= t('dash_student') ?> — <?= e($user['name']) ?></h1>
    <p class="dash-sub"><?= t('dash_student_sub') ?></p>

    <section aria-labelledby="my-courses" id="my-courses">
      <h2 id="my-courses"><?= t('my_courses') ?> — <?= t('side_courses') ?></h2>
      <div class="dash-list">
      <?php foreach ($my_courses ?? [] as $mc): ?>
        <div class="card"><p><strong><?= e($mc['title']) ?></strong> — <?= t('class') ?> <?= e($mc['class_level']) ?> • <?= num((int)$mc['lessons']) ?> <?= t('lessons') ?></p>
        <p class="hero-actions"><a class="btn btn-primary" href="/course/<?= (int)$mc['id'] ?>"><?= t('open_course') ?></a><a class="btn btn-secondary" href="/course/<?= (int)$mc['id'] ?>/lesson/1"><?= t('resume_continue') ?></a><a class="btn btn-secondary" href="/homework"><?= t('side_homework') ?></a><a class="btn btn-secondary" href="/progress"><?= t('side_progress') ?></a></p></div>
      <?php endforeach; ?>
      <?php if (empty($my_courses ?? [])): ?>
        <div class="card"><p><strong><?= t('no_courses_yet') ?></strong></p><p><?= t('no_courses_yet_d') ?></p><p><a class="btn btn-primary" href="/courses"><?= t('browse_courses') ?></a></p></div>
      <?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="my-progress" id="my-progress">
      <h2 id="my-progress"><?= t('my_progress') ?></h2>
      <div class="dash-list">
      <?php foreach ($progress ?? [] as $p): ?>
        <div class="card"><p><strong><?= t('score') ?>: <?= num((int)$p['score']) ?></strong> — <?= e($p['suggestion']) ?> <small>(<?= e($p['created_at']) ?>)</small></p></div>
      <?php endforeach; ?>
      <?php if (empty($progress ?? [])): ?><p><?= t('no_progress') ?></p><?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="my-orders" id="my-orders">
      <h2 id="my-orders"><?= t('my_orders') ?></h2>
      <div class="dash-list">
      <?php foreach ($orders ?? [] as $o): ?>
        <div class="card"><p><strong><?= e($o['course_title']) ?></strong> — <?= e($o['method']) ?>, TrxID <?= e($o['trxid']) ?>, <?= t('sender_phone') ?>: <?= e($o['sender_phone'] ?? '') ?> — <span class="badge"><?= e($o['status']) ?></span></p></div>
      <?php endforeach; ?>
      <?php if (empty($orders ?? [])): ?><p><?= t('no_orders') ?> <a href="/courses"><?= t('browse_courses') ?></a></p><?php endif; ?>
      </div>
    </section>

    <section aria-labelledby="homework-sec" id="homework-sec">
      <h2 id="homework-sec"><?= t('homework_title') ?></h2>
      <div class="dash-list">
      <?php foreach ($homework ?? [] as $h): ?>
        <div class="card"><h3><?= e($h['title']) ?></h3><p><?= e($h['details']) ?> <small>(<?= t('class') ?> <?= e($h['class_level']) ?>)</small></p></div>
      <?php endforeach; ?>
      </div>
      <h2><?= t('my_submissions') ?></h2>
      <div class="dash-list">
      <?php foreach ($my_submissions ?? [] as $s): ?>
        <div class="card"><p><?= e(mb_substr($s['answer'] ?? '', 0, 120)) ?> — <span class="badge"><?= e($s['status']) ?></span><?php if (!empty($s['teacher_comment'])): ?> — <?= e($s['teacher_comment']) ?><?php endif; ?></p></div>
      <?php endforeach; ?>
      <?php if (empty($my_submissions ?? [])): ?><p><?= t('no_submissions') ?></p><?php endif; ?>
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

    <div class="resume-card" style="margin-top:28px">
      <div class="resume-info"><h2 style="font-size:20px"><?= t('streak_title') ?></h2><p><?= t('streak_d') ?></p></div>
      <a class="btn btn-green" href="/courses"><?= t('browse_courses') ?> →</a>
    </div>
  </div>
</div>
