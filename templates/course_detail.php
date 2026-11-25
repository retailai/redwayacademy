<article class="detail open">
  <h1><?= e($course['title']) ?></h1>
  <p><span class="unit"><?= e($course['paper']) ?></span> Class <?= e($course['class_level']) ?></p>
  <p><?= e($course['description']) ?></p>
  <p><span class="price">৳<?= (int)$course['price_bdt'] ?></span> • <?= (int)$course['lessons'] ?> lessons</p>
  <div class="tags"><span class="tag">NCTB-aligned</span><span class="tag">Homework + Red Pen Review</span><span class="tag">Monthly Green Progress report</span></div>
  <?php if (!empty($is_enrolled)): ?>
  <p><span class="badge">Enrolled ✓ — ভর্তি হয়েছে</span></p>
  <?php endif; ?>
  <?php if (empty($can_view_lessons)): ?>
  <p class="hero-actions"><a class="btn btn-primary" href="/buy/<?= (int)$course['id'] ?>">Buy this course (bKash / Nagad)</a> <a class="btn btn-secondary" href="/courses">All courses</a></p>
  <?php else: ?>
  <p class="hero-actions"><a class="btn btn-primary" href="/dashboard">My Courses</a> <a class="btn btn-secondary" href="/courses">All courses</a></p>
  <h2>Lessons — পাঠ</h2>
  <ol class="lesson-list">
  <?php foreach ($lessons as $l): ?>
    <li><a href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$l['lesson_no'] ?>">Lesson <?= (int)$l['lesson_no'] ?> — <?= e($l['title']) ?></a></li>
  <?php endforeach; ?>
  </ol>
  <?php if (empty($lessons)): ?><p>Lessons for this course are being prepared. Please check back soon.</p><?php endif; ?>
  <?php endif; ?>
  <h2>What you will learn</h2>
  <div class="dialogue">
    <p>NCTB-aligned lessons, homework with Red Pen Review, a Mistake Notebook (ভুলের খাতা), weekly retry tests, and a monthly Green Progress report to parents. ভুল করো, বোঝো, সঠিক করো।</p>
  </div>
</article>
