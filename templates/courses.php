<h1>Courses — NCTB English, Class 6 to 12</h1>
<p class="section-lead">Showing: <strong><?= e($paper) ?></strong></p>
<div class="class-pills" aria-label="Filter courses by paper">
  <a class="pill<?= $paper === 'All' ? ' active' : '' ?>" href="/courses"<?php if ($paper === 'All'): ?> aria-current="true"<?php endif; ?>>All</a>
  <a class="pill<?= $paper === '1st Paper' ? ' active' : '' ?>" href="/courses?paper=1st+Paper"<?php if ($paper === '1st Paper'): ?> aria-current="true"<?php endif; ?>>1st Paper</a>
  <a class="pill<?= $paper === '2nd Paper' ? ' active' : '' ?>" href="/courses?paper=2nd+Paper"<?php if ($paper === '2nd Paper'): ?> aria-current="true"<?php endif; ?>>2nd Paper</a>
  <a class="pill<?= $paper === 'Spoken English' ? ' active' : '' ?>" href="/courses?paper=Spoken+English"<?php if ($paper === 'Spoken English'): ?> aria-current="true"<?php endif; ?>>Spoken English</a>
</div>
<div class="grid">
<?php foreach ($courses as $c): ?>
  <article class="card course-card">
    <?php if ($c['paper'] === 'Spoken English'): ?>
    <div class="card-media"><img src="/assets/img/spoken-english-group.jpg" alt="Students speaking English confidently in a group discussion" width="1920" height="1280" loading="lazy" onerror="this.classList.add('img-failed')"></div>
    <?php endif; ?>
    <div class="card-top"><span class="unit"><?= e($c['paper']) ?></span></div>
    <h3><?= e($c['title']) ?></h3>
    <p><?= e($c['description']) ?></p>
    <div class="tags"><span class="tag">Class <?= e($c['class_level']) ?></span><span class="tag"><?= (int)$c['lessons'] ?> lessons</span><span class="tag">bKash / Nagad</span></div>
    <p><span class="price">৳<?= (int)$c['price_bdt'] ?></span></p>
    <div class="card-actions"><a class="btn btn-primary" href="/course/<?= (int)$c['id'] ?>">Details</a><a class="btn btn-secondary" href="/buy/<?= (int)$c['id'] ?>">Buy</a></div>
  </article>
<?php endforeach; ?>
<?php if (empty($courses)): ?><p>No courses in this group yet.</p><?php endif; ?>
</div>
