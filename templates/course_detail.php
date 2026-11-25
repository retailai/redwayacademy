<article>
  <?php if ($course['paper'] === 'Spoken English'): ?>
  <figure class="split-media" style="margin-bottom: var(--space-5);">
    <img src="/assets/img/spoken-english-group.jpg" alt="Students speaking English confidently in a group discussion" width="1920" height="1280" loading="lazy" onerror="this.classList.add('img-failed')">
  </figure>
  <?php endif; ?>
  <h1><?= e($course['title']) ?></h1>
  <p><span class="badge"><?= e($course['paper']) ?></span> Class <?= e($course['class_level']) ?></p>
  <p><?= e($course['description']) ?></p>
  <p><span class="price">৳<?= (int)$course['price_bdt'] ?></span> • <?= (int)$course['lessons'] ?> lessons</p>
  <p class="hero-actions"><a class="btn btn-yellow" href="/buy/<?= (int)$course['id'] ?>">Buy this course (bKash / Nagad)</a> <a class="btn btn-secondary" href="/courses">All courses</a></p>
  <h2>What you will learn</h2>
  <p>NCTB-aligned lessons, homework with Red Pen Review, a Mistake Notebook (ভুলের খাতা), weekly retry tests, and a monthly Green Progress report to parents. ভুল করো, বোঝো, সঠিক করো.</p>
</article>
