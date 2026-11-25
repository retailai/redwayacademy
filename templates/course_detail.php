<article class="detail open">
  <?php if ($course['paper'] === 'Spoken English'): ?>
  <figure class="split-media" style="margin-bottom:24px">
    <img src="/assets/img/spoken-english-group.jpg" alt="Students speaking English confidently in a group discussion" width="1920" height="1280" loading="lazy" onerror="this.classList.add('img-failed')">
  </figure>
  <?php endif; ?>
  <h1><?= e($course['title']) ?></h1>
  <p><span class="unit"><?= e($course['paper']) ?></span> Class <?= e($course['class_level']) ?></p>
  <p><?= e($course['description']) ?></p>
  <p><span class="price">৳<?= (int)$course['price_bdt'] ?></span> • <?= (int)$course['lessons'] ?> lessons</p>
  <div class="tags"><span class="tag">NCTB-aligned</span><span class="tag">Homework + Red Pen Review</span><span class="tag">Monthly Green Progress report</span></div>
  <p class="hero-actions"><a class="btn btn-primary" href="/buy/<?= (int)$course['id'] ?>">Buy this course (bKash / Nagad)</a> <a class="btn btn-secondary" href="/courses">All courses</a></p>
  <h2>What you will learn</h2>
  <div class="dialogue">
    <p>NCTB-aligned lessons, homework with Red Pen Review, a Mistake Notebook (ভুলের খাতা), weekly retry tests, and a monthly Green Progress report to parents. ভুল করো, বোঝো, সঠিক করো.</p>
  </div>
</article>
