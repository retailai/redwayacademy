<?php
// Lesson view: $course, $lesson, $total, $all_lessons (lesson_no, title).
$modules = [];
foreach ($all_lessons as $l) {
    $m = (int)ceil((int)$l['lesson_no'] / 6);
    $modules[$m][] = $l;
}
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <a href="/courses"><?= t('crumb_courses') ?></a> / <a href="/course/<?= (int)$course['id'] ?>"><?= e($course['title']) ?></a></nav>
<div class="lesson-layout">
  <aside class="curr-sidebar" aria-label="<?= e(t('curriculum')) ?>">
    <h2><?= e($course['title']) ?></h2>
    <?php foreach ($modules as $m => $ls): ?>
      <div class="module<?= (int)ceil((int)$lesson['lesson_no'] / 6) === $m ? ' open' : '' ?>">
        <button class="head" type="button" data-acc><?= sprintf(t('module'), num($m)) ?> <span aria-hidden="true">▾</span></button>
        <div class="body">
          <?php foreach ($ls as $l): ?>
          <a class="curr-item<?= (int)$l['lesson_no'] === (int)$lesson['lesson_no'] ? ' active' : '' ?>" href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$l['lesson_no'] ?>"<?php if ((int)$l['lesson_no'] === (int)$lesson['lesson_no']): ?> aria-current="true"<?php endif; ?>>
            <span class="n"><?= num((int)$l['lesson_no']) ?></span><span><?= e($l['title']) ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </aside>

  <article class="lesson-content">
    <p style="margin:0"><a href="/course/<?= (int)$course['id'] ?>">← <?= t('back_course') ?></a></p>
    <h1 style="margin-top:10px"><?= t('lesson') ?> <?= num((int)$lesson['lesson_no']) ?> — <?= e($lesson['title']) ?></h1>
    <div class="meta-row">
      <span class="unit"><?= e($course['paper']) ?></span>
      <span><?= t('class') ?> <?= e($course['class_level']) ?></span> •
      <span><?= sprintf(t('resume_lesson_of'), num((int)$lesson['lesson_no']), num((int)$total)) ?></span>
    </div>
    <div class="lesson-body">
      <p><?= nl2br(e($lesson['body'])) ?></p>
    </div>
    <div class="lesson-nav">
      <?php if ((int)$lesson['lesson_no'] > 1): ?><a class="btn btn-secondary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$lesson['lesson_no'] - 1 ?>"><?= t('prev_lesson') ?></a><?php endif; ?>
      <?php if ((int)$lesson['lesson_no'] < (int)$total): ?><a class="btn btn-primary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$lesson['lesson_no'] + 1 ?>"><?= t('next_lesson') ?></a><?php endif; ?>
      <a class="btn btn-secondary" href="/dashboard"><?= t('my_courses_link') ?></a>
    </div>
  </article>
</div>
