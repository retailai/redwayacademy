<?php // Shared course card. Expects $c (course row). Text-only thumbnail tile, no photos.
// Monthly subscription (live class) is the primary price; the one-time
// course price stays as a secondary option. Thumb + title link to detail.
$track = (string)($c['track'] ?? 'academic');
$fee = track_monthly_fee($track);
?>
<article class="card course-card">
  <a class="course-thumb <?= thumb_class($c) ?>" href="/course/<?= (int)$c['id'] ?>" aria-label="<?= e($c['title']) ?>">
    <span class="thumb-badge"><?= e(track_label($track)) ?></span>
    <span class="thumb-letter"><?= e(thumb_letter($c)) ?></span>
  </a>
  <div class="card-body">
    <h3><a href="/course/<?= (int)$c['id'] ?>" style="color:inherit;text-decoration:none"><?= e($c['title']) ?></a></h3>
    <p><?= e($c['description']) ?></p>
    <div class="tags"><span class="tag"><?= t('class') ?> <?= e($c['class_level']) ?></span><span class="tag"><?= num((int)$c['lessons']) ?> <?= t('lessons') ?></span><span class="tag">bKash / Nagad</span></div>
    <div class="price-row"><span class="price"><?= sprintf(t('monthly_fee'), tk($fee)) ?></span></div>
    <p class="live-line"><?= live_class_text() ?></p>
    <p class="once-line"><?= sprintf(t('or_once'), tk((int)$c['price_bdt'])) ?></p>
    <div class="card-actions"><a class="btn btn-primary" href="/course/<?= (int)$c['id'] ?>"><?= t('details') ?></a><a class="btn btn-secondary" href="/buy/<?= (int)$c['id'] ?>?plan=monthly"><?= sprintf(t('subscribe'), tk($fee)) ?></a></div>
  </div>
</article>
