<section class="hero" aria-labelledby="hero-heading">
  <div class="hero-card">
    <div class="hero-text">
      <span class="badge"><i aria-hidden="true"></i> NCTB &bull; English for Today &bull; Class 6&ndash;12</span>
      <h1 id="hero-heading">Mistakes Mark<br><span>the Way</span></h1>
      <p class="bangla"><?= BRAND_SLOGAN_BN ?></p>
      <p>At Redway, a mistake is not the end. It is the beginning. The red mark on a notebook is our way forward — every error is identified, understood and corrected.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="/courses">See Courses — শুরু করো</a>
        <a class="btn btn-secondary" href="/retake">Free SSC / HSC Retake Registration</a>
      </div>
      <div class="stats" aria-label="Redway Academy at a glance">
        <div class="stat"><b>11</b><span>Courses</span></div>
        <div class="stat"><b>6&ndash;12</b><span>Classes</span></div>
        <div class="stat"><b>৳699</b><span>Starting price</span></div>
        <div class="stat"><b>3</b><span>Logins: student, parent, teacher</span></div>
      </div>
    </div>
  </div>
</section>

<nav class="tabs" aria-label="Page sections">
  <div class="tabs-row">
    <a class="tab active" href="#method-heading">Red Pen Method</a>
    <a class="tab" href="#courses-heading">Courses</a>
    <a class="tab" href="#lesson-heading">Free Lesson</a>
    <a class="tab" href="#reviews-heading">Reviews</a>
  </div>
</nav>

<section class="split" aria-labelledby="story-heading">
  <div>
    <h2 id="story-heading">Our story</h2>
    <p><strong>Red</strong> = the teacher's red pen mark, identifying a mistake. <strong>Way</strong> = the path. So <strong>Redway</strong> = the path of learning by identifying mistakes. A student who understands and corrects a mistake remembers it for much longer. We do not say "fail" — we say: ভুল করো, বোঝো, সঠিক করো.</p>
  </div>
</section>

<section aria-labelledby="method-heading">
  <div class="section-head"><div><h2 id="method-heading">The Red Pen Method</h2><p>Four steps that turn every mistake into progress.</p></div></div>
  <div class="grid">
    <div class="card step-card step-red"><span class="step-number" aria-hidden="true">1</span><h3>Red Pen Review</h3><p>After every test, mistakes are marked in red and discussed in class.</p></div>
    <div class="card step-card step-yellow"><span class="step-number" aria-hidden="true">2</span><h3>ভুলের খাতা — Mistake Notebook</h3><p>Every student keeps their own notebook: the mistake, its cause, and the correct solution.</p></div>
    <div class="card step-card step-blue"><span class="step-number" aria-hidden="true">3</span><h3>Weekly Retry Test</h3><p>A short re-test on last week's wrong questions only.</p></div>
    <div class="card step-card step-green"><span class="step-number" aria-hidden="true">4</span><h3>Red to Green</h3><p>When fixed, a green tick next to the red mark. Monthly Green Progress report to parents.</p></div>
  </div>
</section>

<section aria-labelledby="courses-heading">
  <div class="section-head"><div><h2 id="courses-heading">Courses — Class 6 to 12</h2><p>NCTB English for Today, English 2nd Paper (Grammar &amp; Composition), and Spoken English.</p></div><a class="btn btn-secondary" href="/courses">All courses</a></div>
  <div class="grid">
  <?php foreach ($courses as $c): ?>
    <article class="card course-card">
      <div class="card-top"><span class="unit"><?= e($c['paper']) ?></span></div>
      <h3><?= e($c['title']) ?></h3>
      <p><?= e($c['description']) ?></p>
      <div class="tags"><span class="tag">Class <?= e($c['class_level']) ?></span><span class="tag"><?= (int)$c['lessons'] ?> lessons</span><span class="tag">bKash / Nagad</span></div>
      <p><span class="price">৳<?= (int)$c['price_bdt'] ?></span></p>
      <div class="card-actions"><a class="btn btn-primary" href="/course/<?= (int)$c['id'] ?>">Details</a></div>
    </article>
  <?php endforeach; ?>
  </div>
</section>

<section aria-labelledby="lesson-heading">
  <h2 id="lesson-heading">Free lesson — The Noun (English 2nd Paper)</h2>
  <div class="dialogue">
    <p>A noun names a person, place, thing or idea. <em>Rahim</em> (person), <em>Dhaka</em> (place), <em>book</em> (thing), <em>honesty</em> (idea).</p>
    <p>Try it: underline the nouns in "Rahim reads a book in Dhaka."</p>
  </div>
</section>

<?php if (!empty($testimonials)): ?>
<section aria-labelledby="reviews-heading">
  <div class="section-head"><div><h2 id="reviews-heading">Happy Parents &amp; Students</h2><p>Endorsements published after admin approval.</p></div><a class="btn btn-secondary" href="/testimonials">See all reviews / add yours</a></div>
  <div class="grid">
  <?php foreach ($testimonials as $t): ?>
    <figure class="card"><blockquote><p>"<?= e($t['message']) ?>"</p></blockquote><figcaption><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, Class <?= e($t['class_level']) ?><?php endif; ?></figcaption></figure>
  <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section aria-labelledby="contact-heading">
  <h2 id="contact-heading">Contact us — যোগাযোগ</h2>
  <div class="dialogue">
    <p>Call us: <strong><a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a></strong></p>
    <p>Visit us: <strong><?= CONTACT_ADDRESS ?></strong></p>
    <p>Course payment: <strong>bKash / Nagad: <?= CONTACT_PHONE ?></strong> — send money to this number and submit the TrxID on the buy page.</p>
  </div>
</section>
