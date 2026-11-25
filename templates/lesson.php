<article class="detail open">
  <p><a href="/course/<?= (int)$course['id'] ?>">← <?= e($course['title']) ?></a></p>
  <h1>Lesson <?= (int)$lesson['lesson_no'] ?> — <?= e($lesson['title']) ?></h1>
  <p><span class="unit"><?= e($course['paper']) ?></span> Class <?= e($course['class_level']) ?> • Lesson <?= (int)$lesson['lesson_no'] ?> of <?= (int)$total ?></p>
  <div class="dialogue">
    <p><?= nl2br(e($lesson['body'])) ?></p>
  </div>
  <p class="hero-actions">
    <?php if ((int)$lesson['lesson_no'] > 1): ?><a class="btn btn-secondary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$lesson['lesson_no'] - 1 ?>">← Previous lesson</a><?php endif; ?>
    <?php if ((int)$lesson['lesson_no'] < (int)$total): ?><a class="btn btn-primary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$lesson['lesson_no'] + 1 ?>">Next lesson →</a><?php endif; ?>
    <a class="btn btn-secondary" href="/dashboard">My Courses</a>
  </p>
</article>
