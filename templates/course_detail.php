<?php
// Course detail: $course, $is_enrolled, $can_view_lessons, $lessons (lesson_no, title).
// Syllabus is grouped into modules of 6 lessons.
$modules = [];
foreach ($lessons as $l) {
    $m = (int)ceil((int)$l['lesson_no'] / 6);
    $modules[$m][] = $l;
}
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <a href="/courses"><?= t('crumb_courses') ?></a> / <?= e($course['title']) ?></nav>
<div class="detail-layout">
  <article class="detail">
    <p style="margin:0"><span class="unit"><?= e($course['paper']) ?></span> <span class="unit" style="background:var(--green-soft);color:var(--green-dark)"><?= e(track_label((string)($course['track'] ?? 'academic'))) ?></span></p>
    <h1 style="margin-top:10px"><?= e($course['title']) ?></h1>
    <div class="meta-row">
      <span><?= t('class') ?> <?= e($course['class_level']) ?></span> •
      <span><?= num((int)$course['lessons']) ?> <?= t('lessons') ?></span> •
      <span>NCTB</span>
      <?php if (!empty($is_enrolled)): ?><span class="badge green"><?= t('enrolled') ?></span><?php endif; ?>
    </div>
    <p><?= e($course['description']) ?></p>

    <h2><?= t('what_learn') ?></h2>
    <ul class="learn-list">
      <li><?= t('inc1') ?></li>
      <li><?= t('inc2') ?></li>
      <li><?= t('inc3') ?></li>
      <li><?= t('inc4') ?></li>
      <li><?= t('inc5') ?></li>
    </ul>

    <div class="instructor-card">
      <span class="avatar-circle" aria-hidden="true">R</span>
      <div><b><?= t('instructor') ?></b><p style="margin:4px 0 0;color:var(--muted)"><?= t('instructor_d') ?></p></div>
    </div>

    <h2><?= t('syllabus') ?></h2>
    <?php if (empty($can_view_lessons)): ?>
      <p><?= t('lessons_locked') ?></p>
      <div class="accordion">
        <?php for ($m = 1; $m <= (int)ceil((int)$course['lessons'] / 6); $m++): ?>
        <?php $from = ($m - 1) * 6 + 1; $to = min($m * 6, (int)$course['lessons']); ?>
        <div class="module">
          <button class="head" type="button" data-acc><?= sprintf(t('module'), num($m)) ?> — <?= sprintf(t('module_lessons'), num($from), num($to)) ?> <span aria-hidden="true">▾</span></button>
          <div class="body">
            <?php for ($n = $from; $n <= $to; $n++): ?>
            <div class="lesson-row"><span><?= t('lesson') ?> <?= num($n) ?></span><span class="lock" aria-label="Locked">🔒</span></div>
            <?php endfor; ?>
          </div>
        </div>
        <?php endfor; ?>
      </div>
    <?php else: ?>
      <div class="accordion">
        <?php foreach ($modules as $m => $ls): ?>
        <?php $from = ($m - 1) * 6 + 1; $to = $from + count($ls) - 1; ?>
        <div class="module<?= $m === 1 ? ' open' : '' ?>">
          <button class="head" type="button" data-acc><?= sprintf(t('module'), num($m)) ?> — <?= sprintf(t('module_lessons'), num($from), num($to)) ?> <span aria-hidden="true">▾</span></button>
          <div class="body">
            <?php foreach ($ls as $l): ?>
            <div class="lesson-row"><a href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$l['lesson_no'] ?>"><?= t('lesson') ?> <?= num((int)$l['lesson_no']) ?> — <?= e($l['title']) ?></a><span class="done-dot" aria-hidden="true">›</span></div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (empty($lessons)): ?><p><?= t('no_courses') ?></p><?php endif; ?>
    <?php endif; ?>
  </article>

  <aside class="buy-card" aria-label="<?= e(t('buy_now')) ?>">
    <div class="price-row"><span class="price"><?= sprintf(t('monthly_fee'), tk((int)$track_fee)) ?></span></div>
    <p style="color:var(--muted);margin:8px 0 0"><?= live_class_text() ?></p>
    <p style="color:var(--muted);margin:4px 0 0"><?= num((int)$course['lessons']) ?> <?= t('lessons') ?> • <?= t('class') ?> <?= e($course['class_level']) ?></p>
    <?php if (!empty($is_enrolled)): ?>
      <a class="btn btn-green" href="/course/<?= (int)$course['id'] ?>/lesson/1"><?= t('open_course') ?> →</a>
      <?php if (!empty($is_subscribed)): ?><p style="margin:10px 0 0;color:var(--green-dark);font-weight:700"><?= sprintf(t('sub_active_until'), num(subscription_until($current_user['phone'], (string)($course['track'] ?? 'academic')))) ?></p><?php endif; ?>
    <?php else: ?>
      <a class="btn btn-red" href="/buy/<?= (int)$course['id'] ?>?plan=monthly"><?= sprintf(t('subscribe'), tk((int)$track_fee)) ?></a>
      <a class="btn btn-secondary" href="/buy/<?= (int)$course['id'] ?>?plan=once" style="margin-top:10px"><?= sprintf(t('buy_once'), tk((int)$course['price_bdt'])) ?></a>
    <?php endif; ?>
    <h3 style="margin-top:20px"><?= t('includes') ?></h3>
    <ul class="includes-list">
      <li><?= t('inc1') ?></li>
      <li><?= t('inc2') ?></li>
      <li><?= t('inc3') ?></li>
      <li><?= t('inc4') ?></li>
      <li><?= t('inc5') ?></li>
    </ul>
    <p style="margin-top:16px;color:var(--muted);font-size:14px">bKash / Nagad: <a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a></p>
  </aside>
</div>
