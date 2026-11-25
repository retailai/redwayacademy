<section class="hero" aria-labelledby="hero-heading">
  <div>
    <h1 id="hero-heading">Mistakes Mark the Way</h1>
    <p class="bangla"><?= BRAND_SLOGAN_BN ?></p>
    <p>At Redway, a mistake is not the end. It is the beginning. The red mark on a notebook is our way forward — every error is identified, understood and corrected.</p>
    <p class="hero-actions">
      <a class="btn" href="/courses">See Courses</a>
      <a class="btn btn-secondary" href="/retake">Free SSC / HSC Retake Registration</a>
    </p>
  </div>
  <figure class="hero-media">
    <img src="/assets/img/hero-classroom.jpg" alt="Bangladeshi high-school students studying English together in a bright classroom" width="1920" height="1280" fetchpriority="high" onerror="this.classList.add('img-failed')">
  </figure>
</section>

<section class="split" aria-labelledby="story-heading">
  <div>
    <h2 id="story-heading">Our story</h2>
    <p><strong>Red</strong> = the teacher's red pen mark, identifying a mistake. <strong>Way</strong> = the path. So <strong>Redway</strong> = the path of learning by identifying mistakes. A student who understands and corrects a mistake remembers it for much longer. We do not say "fail" — we say: ভুল করো, বোঝো, সঠিক করো.</p>
  </div>
  <figure class="split-media">
    <img src="/assets/img/red-pen-teacher.jpg" alt="A teacher marking a student's English notebook with a red pen" width="1920" height="1280" loading="lazy" onerror="this.classList.add('img-failed')">
  </figure>
</section>

<section aria-labelledby="method-heading">
  <h2 id="method-heading">The Red Pen Method</h2>
  <p class="section-lead">Four steps that turn every mistake into progress.</p>
  <div class="grid">
    <div class="card step-card step-red"><span class="step-number" aria-hidden="true">1</span><h3>Red Pen Review</h3><p>After every test, mistakes are marked in red and discussed in class.</p></div>
    <div class="card step-card step-yellow"><span class="step-number" aria-hidden="true">2</span><h3>ভুলের খাতা — Mistake Notebook</h3><p>Every student keeps their own notebook: the mistake, its cause, and the correct solution.</p></div>
    <div class="card step-card step-blue"><span class="step-number" aria-hidden="true">3</span><h3>Weekly Retry Test</h3><p>A short re-test on last week's wrong questions only.</p></div>
    <div class="card step-card step-green"><span class="step-number" aria-hidden="true">4</span><h3>Red to Green</h3><p>When fixed, a green tick next to the red mark. Monthly Green Progress report to parents.</p></div>
  </div>
</section>

<section aria-labelledby="courses-heading">
  <h2 id="courses-heading">Courses — Class 6 to 12</h2>
  <p class="section-lead">NCTB English for Today, English 2nd Paper (Grammar &amp; Composition), and Spoken English.</p>
  <div class="grid">
  <?php foreach ($courses as $c): ?>
    <article class="card course-card">
      <?php if ($c['paper'] === 'Spoken English'): ?>
      <div class="card-media"><img src="/assets/img/spoken-english-group.jpg" alt="Students speaking English confidently in a group discussion" width="1920" height="1280" loading="lazy" onerror="this.classList.add('img-failed')"></div>
      <?php endif; ?>
      <span class="badge"><?= e($c['paper']) ?></span>
      <h3><?= e($c['title']) ?></h3>
      <p><?= e($c['description']) ?></p>
      <p><span class="price">৳<?= (int)$c['price_bdt'] ?></span> • <?= (int)$c['lessons'] ?> lessons</p>
      <p class="card-actions"><a class="btn" href="/course/<?= (int)$c['id'] ?>">Details</a></p>
    </article>
  <?php endforeach; ?>
  </div>
</section>

<section aria-labelledby="lesson-heading">
  <h2 id="lesson-heading">Free lesson — The Noun (English 2nd Paper)</h2>
  <p>A noun names a person, place, thing or idea. <em>Rahim</em> (person), <em>Dhaka</em> (place), <em>book</em> (thing), <em>honesty</em> (idea). Try it: underline the nouns in "Rahim reads a book in Dhaka."</p>
</section>

<?php if (!empty($testimonials)): ?>
<section aria-labelledby="reviews-heading">
  <h2 id="reviews-heading">Happy Parents &amp; Students</h2>
  <div class="grid">
  <?php foreach ($testimonials as $t): ?>
    <figure class="card"><blockquote><p>"<?= e($t['message']) ?>"</p></blockquote><figcaption><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, Class <?= e($t['class_level']) ?><?php endif; ?></figcaption></figure>
  <?php endforeach; ?>
  </div>
  <p><a href="/testimonials">See all reviews / add yours</a></p>
</section>
<?php endif; ?>
