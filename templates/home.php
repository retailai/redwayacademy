<section class="hero">
  <h1>Mistakes Mark the Way</h1>
  <p class="bangla"><?= BRAND_SLOGAN_BN ?></p>
  <p>At Redway, a mistake is not the end. It is the beginning. The red mark on a notebook is our way forward — every error is identified, understood and corrected.</p>
  <p><a class="btn" href="/courses">See Courses</a> <a class="btn yellow" href="/retake">Free SSC / HSC Retake Registration</a></p>
</section>
<section>
  <h2>Our story</h2>
  <p><strong>Red</strong> = the teacher's red pen mark, identifying a mistake. <strong>Way</strong> = the path. So <strong>Redway</strong> = the path of learning by identifying mistakes. A student who understands and corrects a mistake remembers it for much longer. We do not say "fail" — we say: ভুল করো, বোঝো, সঠিক করো.</p>
</section>
<section>
  <h2>The Red Pen Method</h2>
  <div class="grid">
    <div class="card"><h3>1. Red Pen Review</h3><p>After every test, mistakes are marked in red and discussed in class.</p></div>
    <div class="card"><h3>2. ভুলের খাতা — Mistake Notebook</h3><p>Every student keeps their own notebook: the mistake, its cause, and the correct solution.</p></div>
    <div class="card"><h3>3. Weekly Retry Test</h3><p>A short re-test on last week's wrong questions only.</p></div>
    <div class="card"><h3>4. Red to Green</h3><p>When fixed, a green tick next to the red mark. Monthly Green Progress report to parents.</p></div>
  </div>
</section>
<section>
  <h2>Courses — Class 6 to 12</h2>
  <div class="grid">
  <?php foreach ($courses as $c): ?>
    <div class="card"><span class="badge"><?= e($c['paper']) ?></span><h3><?= e($c['title']) ?></h3><p><?= e($c['description']) ?></p><p><strong>৳<?= (int)$c['price_bdt'] ?></strong> • <?= (int)$c['lessons'] ?> lessons</p><p><a class="btn" href="/course/<?= (int)$c['id'] ?>">Details</a></p></div>
  <?php endforeach; ?>
  </div>
</section>
<section>
  <h2>Free lesson — The Noun (English 2nd Paper)</h2>
  <p>A noun names a person, place, thing or idea. <em>Rahim</em> (person), <em>Dhaka</em> (place), <em>book</em> (thing), <em>honesty</em> (idea). Try it: underline the nouns in "Rahim reads a book in Dhaka."</p>
</section>
<?php if (!empty($testimonials)): ?>
<section><h2>Happy Parents &amp; Students</h2><div class="grid">
<?php foreach ($testimonials as $t): ?><div class="card"><p>"<?= e($t['message']) ?>"</p><p><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, Class <?= e($t['class_level']) ?><?php endif; ?></p></div><?php endforeach; ?>
</div><p><a href="/testimonials">See all reviews / add yours</a></p></section>
<?php endif; ?>
