<?php
// Course listing: $courses (page), $paper, $q, $min_price, $max_price, $price_band,
// $page, $pages, $total. Filter links preserve the other filters via $filter_url().
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <?= t('crumb_courses') ?></nav>
<h1 class="page-title"><?= t('courses_title') ?></h1>
<p class="section-lead"><?= t('courses_sub') ?></p>
<p class="section-lead"><?= t('showing') ?>: <strong><?= e($paper) ?></strong><?php if (!empty($q)): ?> • “<?= e($q) ?>”<?php endif; ?> • <?= num((int)$total) ?> <?= t('stat_courses') ?></p>

<section aria-labelledby="how-heading" style="margin-top:20px">
  <h2 id="how-heading" style="margin-top:0"><?= t('how_title') ?></h2>
  <p><?= t('how_sub') ?></p>
  <div class="how-row">
    <div class="how-step"><b>1</b><div><h3><?= t('how1_t') ?></h3><p><?= t('how1_d') ?></p></div></div>
    <div class="how-step"><b>2</b><div><h3><?= t('how2_t') ?></h3><p><?= t('how2_d') ?></p></div></div>
    <div class="how-step"><b>3</b><div><h3><?= t('how3_t') ?></h3><p><?= t('how3_d') ?></p></div></div>
  </div>
</section>

<div class="courses-layout">
  <aside class="filter-card" aria-label="<?= e(t('filter_title')) ?>">
    <h3><?= t('filter_title') ?></h3>
    <form method="get" action="/courses">
      <div class="filter-group">
        <label><?= t('filter_paper') ?></label>
        <div class="class-pills" style="margin-top:0">
          <a class="pill<?= $paper === 'All' ? ' active' : '' ?>" href="<?= e($filter_url(['paper' => 'All', 'page' => 1])) ?>"><?= t('filter_all') ?></a>
          <?php foreach (['1st Paper', '2nd Paper', 'Spoken English'] as $p): ?>
          <a class="pill<?= $paper === $p ? ' active' : '' ?>" href="<?= e($filter_url(['paper' => $p, 'page' => 1])) ?>"><?= e($p) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="filter-group">
        <label><?= t('filter_price') ?></label>
        <div class="price-pills">
          <a href="<?= e($filter_url(['band' => 'all', 'page' => 1])) ?>"<?= $price_band === 'all' ? ' class="active"' : '' ?>><?= t('price_all') ?></a>
          <a href="<?= e($filter_url(['band' => 'under', 'page' => 1])) ?>"<?= $price_band === 'under' ? ' class="active"' : '' ?>><?= t('price_under') ?></a>
          <a href="<?= e($filter_url(['band' => 'mid', 'page' => 1])) ?>"<?= $price_band === 'mid' ? ' class="active"' : '' ?>><?= t('price_mid') ?></a>
          <a href="<?= e($filter_url(['band' => 'over', 'page' => 1])) ?>"<?= $price_band === 'over' ? ' class="active"' : '' ?>><?= t('price_over') ?></a>
        </div>
      </div>
      <div class="filter-group">
        <label for="min_price"><?= t('filter_min') ?></label>
        <input id="min_price" name="min_price" type="number" min="0" inputmode="numeric" value="<?= $min_price !== '' ? (int)$min_price : '' ?>">
      </div>
      <div class="filter-group">
        <label for="max_price"><?= t('filter_max') ?></label>
        <input id="max_price" name="max_price" type="number" min="0" inputmode="numeric" value="<?= $max_price !== '' ? (int)$max_price : '' ?>">
      </div>
      <input type="hidden" name="paper" value="<?= e($paper) ?>">
      <input type="hidden" name="band" value="<?= e($price_band) ?>">
      <?php if (!empty($q)): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
      <div class="form-actions" style="display:flex;gap:8px">
        <button class="btn btn-primary" type="submit" style="flex:1"><?= t('filter_apply') ?></button>
        <a class="btn btn-secondary" href="/courses"><?= t('filter_clear') ?></a>
      </div>
    </form>
  </aside>

  <div>
    <div class="grid">
      <?php foreach ($courses as $c): ?>
      <?php include __DIR__ . '/partials/course_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <?php if (empty($courses)): ?><p><?= t('no_courses') ?></p><?php endif; ?>

    <?php if ($pages > 1): ?>
    <nav class="pager" aria-label="<?= e(t('page')) ?>">
      <?php if ($page > 1): ?><a href="<?= e($filter_url(['page' => $page - 1])) ?>">← <?= t('prev') ?></a><?php endif; ?>
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i === $page): ?><span class="current" aria-current="page"><?= num($i) ?></span>
        <?php else: ?><a href="<?= e($filter_url(['page' => $i])) ?>"><?= num($i) ?></a><?php endif; ?>
      <?php endfor; ?>
      <?php if ($page < $pages): ?><a href="<?= e($filter_url(['page' => $page + 1])) ?>"><?= t('next') ?> →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
  </div>
</div>
