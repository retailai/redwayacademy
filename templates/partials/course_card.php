<?php // Shared course card. Expects $c (course row). Text-only thumbnail tile, no photos. ?>
<article class="card course-card">
  <div class="course-thumb <?= thumb_class($c) ?>" aria-hidden="true">
    <span class="thumb-badge"><?= e($c['paper']) ?></span>
    <span class="thumb-letter"><?= e(thumb_letter($c)) ?></span>
  </div>
  <div class="card-body">
    <h3><?= e($c['title']) ?></h3>
    <p><?= e($c['description']) ?></p>
    <div class="tags"><span class="tag"><?= t('class') ?> <?= e($c['class_level']) ?></span><span class="tag"><?= num((int)$c['lessons']) ?> <?= t('lessons') ?></span><span class="tag">bKash / Nagad</span></div>
    <div class="price-row"><span class="price"><?= tk((int)$c['price_bdt']) ?></span><span class="price-old" title="<?= e(t('regular_price')) ?>"><?= tk(orig_price((int)$c['price_bdt'])) ?></span></div>
    <div class="card-actions"><a class="btn btn-primary" href="/course/<?= (int)$c['id'] ?>"><?= t('details') ?></a><a class="btn btn-secondary" href="/buy/<?= (int)$c['id'] ?>"><?= t('buy') ?></a></div>
  </div>
</article>
