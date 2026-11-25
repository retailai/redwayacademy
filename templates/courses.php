<h1>Courses — NCTB English, Class 6 to 12</h1>
<p>Filter: <a href="/courses">All</a> | <a href="/courses?paper=1st+Paper">1st Paper</a> | <a href="/courses?paper=2nd+Paper">2nd Paper</a> | <a href="/courses?paper=Spoken+English">Spoken English</a></p>
<p>Showing: <strong><?= e($paper) ?></strong></p>
<div class="grid">
<?php foreach ($courses as $c): ?>
  <div class="card"><span class="badge"><?= e($c['paper']) ?></span><h3><?= e($c['title']) ?></h3><p><?= e($c['description']) ?></p><p><strong>৳<?= (int)$c['price_bdt'] ?></strong> • <?= (int)$c['lessons'] ?> lessons • Class <?= e($c['class_level']) ?></p><p><a class="btn" href="/course/<?= (int)$c['id'] ?>">Details</a> <a class="btn yellow" href="/buy/<?= (int)$c['id'] ?>">Buy</a></p></div>
<?php endforeach; ?>
<?php if (empty($courses)): ?><p>No courses in this group yet.</p><?php endif; ?>
</div>
