<?php
// Course listing — two-step picker, few clicks:
//   step 'tracks': 2 track cards (Academic English / Spoken English)
//   step 'pick':   class picker (Academic) or education picker (Spoken)
//   step 'list':   course cards for the chosen track + class
// Search (?q=) jumps straight to the list. No "how it works", no price filter.
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <?= t('crumb_courses') ?></nav>
<h1 class="page-title"><?= t('courses_title') ?></h1>
<p class="section-lead"><?= t('courses_sub') ?></p>

<?php if ($step === 'tracks'): ?>
<p class="section-lead" style="margin-top:8px"><strong><?= t('track_choose') ?></strong> — <?= t('track_choose_sub') ?></p>
<div class="track-grid">
  <?php foreach (tracks() as $tr): ?>
  <article class="card track-card <?= $tr === 'spoken' ? 'track-spoken' : 'track-academic' ?>">
    <span class="track-letter" aria-hidden="true"><?= $tr === 'spoken' ? 'S' : 'A' ?></span>
    <h2><?= $tr === 'spoken' ? t('track_spoken_full') : t('track_academic_full') ?></h2>
    <p><?= $tr === 'spoken' ? t('track_spoken_d') : t('track_academic_d') ?></p>
    <p class="track-fee"><strong><?= sprintf(t('monthly_fee'), tk(track_monthly_fee($tr))) ?></strong></p>
    <p class="track-live"><?= live_class_text() ?></p>
    <p class="track-count"><?= num((int)($counts[$tr] ?? 0)) ?> <?= t('stat_courses') ?></p>
    <a class="btn <?= $tr === 'spoken' ? 'btn-green' : 'btn-primary' ?>" href="<?= e($list_url(['track' => $tr])) ?>"><?= t('track_choose') ?> →</a>
  </article>
  <?php endforeach; ?>
</div>

<?php elseif ($step === 'pick'): ?>
<p><a href="/courses"><?= t('back_tracks') ?></a></p>
<h2><?= $track === 'academic' ? t('pick_class') : t('pick_edu') ?></h2>
<p class="section-lead"><?= $track === 'academic' ? t('pick_class_sub') : t('pick_edu_sub') ?></p>
<div class="class-pills">
  <?php if ($track === 'academic'): ?>
    <?php foreach ($classes as $cl): ?>
    <a class="pill" href="<?= e($list_url(['class' => $cl])) ?>"><?= t('class') ?> <?= num($cl) ?></a>
    <?php endforeach; ?>
  <?php else: ?>
    <?php foreach (['SSC', 'HSC', 'Honours', 'Other'] as $ed): ?>
    <a class="pill" href="<?= e($list_url(['edu' => $ed])) ?>"><?= t('edu_' . strtolower($ed)) ?></a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php else: ?>
<p><a href="<?= e($list_url(['track' => $track])) ?>"><?= t('back_pick') ?></a> • <a href="/courses"><?= t('back_tracks') ?></a></p>
<p class="section-lead"><?= t('showing') ?>: <?php if (!empty($track)): ?><strong><?= e(track_label($track)) ?></strong><?php endif; ?><?php if (!empty($class)): ?> • <?= t('class') ?> <?= num($class) ?><?php endif; ?><?php if (!empty($edu)): ?> • <?= e(t('edu_' . strtolower($edu))) ?><?php endif; ?><?php if (!empty($q)): ?> • “<?= e($q) ?>”<?php endif; ?> • <?= num((int)$total) ?> <?= t('stat_courses') ?></p>
<div class="grid">
  <?php foreach ($courses as $c): ?>
  <?php include __DIR__ . '/partials/course_card.php'; ?>
  <?php endforeach; ?>
</div>
<?php if (empty($courses)): ?><p><?= t('no_courses') ?></p><?php endif; ?>

<?php if ($pages > 1): ?>
<nav class="pager" aria-label="<?= e(t('page')) ?>">
  <?php if ($page > 1): ?><a href="<?= e($list_url(['page' => $page - 1])) ?>">← <?= t('prev') ?></a><?php endif; ?>
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i === $page): ?><span class="current" aria-current="page"><?= num($i) ?></span>
    <?php else: ?><a href="<?= e($list_url(['page' => $i])) ?>"><?= num($i) ?></a><?php endif; ?>
  <?php endfor; ?>
  <?php if ($page < $pages): ?><a href="<?= e($list_url(['page' => $page + 1])) ?>"><?= t('next') ?> →</a><?php endif; ?>
</nav>
<?php endif; ?>
<?php endif; ?>
