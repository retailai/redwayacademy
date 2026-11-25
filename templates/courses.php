<h1>Courses — NCTB English, Class 6 to 12</h1>
<p class="section-lead">Showing: <strong><?= e($paper) ?></strong></p>
<ul class="filter-list" aria-label="Filter courses by paper">
  <li><a href="/courses"<?php if ($paper === 'All'): ?> aria-current="true"<?php endif; ?>>All</a></li>
  <li><a href="/courses?paper=1st+Paper"<?php if ($paper === '1st Paper'): ?> aria-current="true"<?php endif; ?>>1st Paper</a></li>
  <li><a href="/courses?paper=2nd+Paper"<?php if ($paper === '2nd Paper'): ?> aria-current="true"<?php endif; ?>>2nd Paper</a></li>
  <li><a href="/courses?paper=Spoken+English"<?php if ($paper === 'Spoken English'): ?> aria-current="true"<?php endif; ?>>Spoken English</a></li>
</ul>
<div class="grid">
<?php foreach ($courses as $c): ?>
  <article class="card course-card">
    <?php if ($c['paper'] === 'Spoken English'): ?>
    <div class="card-media"><img src="/assets/img/spoken-english-group.jpg" alt="Students speaking English confidently in a group discussion" width="1920" height="1280" loading="lazy" onerror="this.classList.add('img-failed')"></div>
    <?php endif; ?>
    <span class="badge"><?= e($c['paper']) ?></span>
    <h3><?= e($c['title']) ?></h3>
    <p><?= e($c['description']) ?></p>
    <p><span class="price">৳<?= (int)$c['price_bdt'] ?></span> • <?= (int)$c['lessons'] ?> lessons • Class <?= e($c['class_level']) ?></p>
    <p class="card-actions"><a class="btn" href="/course/<?= (int)$c['id'] ?>">Details</a> <a class="btn btn-secondary" href="/buy/<?= (int)$c['id'] ?>">Buy</a></p>
  </article>
<?php endforeach; ?>
<?php if (empty($courses)): ?><p>No courses in this group yet.</p><?php endif; ?>
</div>
