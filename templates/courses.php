<?php
// Course listing — only 2 courses: Spoken English and Academic English.
//   step 'tracks': 2 track cards (cover style, real counts, real fee)
//   step 'pick':   class picker (Academic) or education picker (Spoken)
//   step 'class':  Academic English class page (1st + 2nd Paper together)
//   step 'list':   search results only
// A logged-in student only ever sees their own class (route enforces it).
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <?= t('crumb_courses') ?></nav>
<h1 class="page-title"><?= $step === 'class' ? sprintf(t('academic_class'), num($class)) : t('courses_title') ?></h1>
<p class="section-lead"><?= $step === 'class' ? t('courses_sub') : t('courses_sub') ?></p>

<?php if ($step === 'tracks'): ?>
<p class="section-lead" style="margin-top:8px"><strong><?= t('track_choose') ?></strong> — <?= t('track_choose_sub') ?></p>
<div class="track-grid">
  <article class="card track-card track-spoken">
    <div class="track-cover"><span class="thumb-badge"><?= t('track_spoken') ?></span><span class="thumb-letter" aria-hidden="true">Sp</span></div>
    <div class="track-body">
      <h2><?= t('track_spoken_full') ?></h2>
      <p><?= t('track_spoken_d') ?></p>
      <p class="track-count"><?= sprintf(t('modules_count'), num(4)) ?> • <?= num((int)($lesson_counts['spoken'] ?? 0)) ?> <?= t('lessons') ?></p>
      <ul class="track-bullets">
        <li><?= t('track_spoken_p1') ?></li>
        <li><?= t('track_spoken_p2') ?></li>
        <li><?= t('track_spoken_p3') ?></li>
      </ul>
      <p class="track-fee"><strong><?= tk(track_monthly_fee('spoken')) ?></strong> / <?= t('month') ?></p>
      <p class="track-live"><?= live_class_text() ?></p>
      <div class="track-actions">
        <a class="btn btn-green" href="<?= e($list_url(['track' => 'spoken'])) ?>"><?= t('start_course') ?> →</a>
        <a class="btn btn-secondary" href="<?= e($list_url(['track' => 'spoken'])) ?>"><?= t('fee_know') ?></a>
      </div>
    </div>
  </article>
  <article class="card track-card track-academic">
    <div class="track-cover"><span class="thumb-badge"><?= t('track_academic') ?></span><span class="thumb-letter" aria-hidden="true">Ac</span></div>
    <div class="track-body">
      <h2><?= t('track_academic_full') ?></h2>
      <p><?= t('track_academic_d') ?></p>
      <p class="track-count"><?= num((int)($academic_classes ?? 5)) ?> <?= t('classes_count') ?> (<?= num(6) ?>–<?= num(12) ?>) • <?= num((int)($lesson_counts['academic'] ?? 0)) ?> <?= t('lessons') ?></p>
      <ul class="track-bullets">
        <li><?= t('track_academic_p1') ?></li>
        <li><?= t('track_academic_p2') ?></li>
        <li><?= t('track_academic_p3') ?></li>
      </ul>
      <p class="track-fee"><strong><?= tk(track_monthly_fee('academic')) ?></strong> / <?= t('month') ?></p>
      <p class="track-live"><?= live_class_text() ?></p>
      <div class="track-actions">
        <a class="btn btn-primary" href="<?= e($list_url(['track' => 'academic'])) ?>"><?= t('start_course') ?> →</a>
        <a class="btn btn-secondary" href="<?= e($list_url(['track' => 'academic'])) ?>"><?= t('fee_know') ?></a>
      </div>
    </div>
  </article>
</div>

<?php elseif ($step === 'pick'): ?>
<p><a href="/courses"><?= t('back_tracks') ?></a></p>
<h2><?= $track === 'academic' ? t('pick_class') : t('pick_edu') ?></h2>
<p class="section-lead"><?= $track === 'academic' ? t('pick_class_sub') : t('pick_edu_sub') ?></p>
<?php if (!empty($own_class) && $track === 'academic'): ?><p class="section-lead"><strong><?= sprintf(t('class_choose_own'), num($own_class)) ?></strong></p><?php endif; ?>
<div class="class-pills">
  <?php if ($track === 'academic'): ?>
    <?php foreach ($classes as $cl): ?>
    <a class="pill<?= (!empty($own_class) && $own_class === $cl) ? ' own' : '' ?>" href="<?= e($list_url(['class' => $cl])) ?>"><?= t('class') ?> <?= num($cl) ?></a>
    <?php endforeach; ?>
  <?php else: ?>
    <?php foreach (['SSC', 'HSC', 'Honours', 'Other'] as $ed): ?>
    <a class="pill" href="<?= e($list_url(['edu' => $ed])) ?>"><?= t('edu_' . strtolower($ed)) ?></a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php elseif ($step === 'class'): ?>
<p><a href="<?= e($list_url(['track' => 'academic'])) ?>"><?= t('back_pick') ?></a> • <a href="/courses"><?= t('back_tracks') ?></a></p>
<?php
$first_paper = null;
foreach ($papers as $p) { if (($p['paper'] ?? '') === '1st Paper') { $first_paper = $p; break; } }
if (!$first_paper && !empty($papers)) $first_paper = $papers[0];
$is_teacher = !empty($me) && ($me['role'] ?? '') === 'teacher';
?>
<div class="detail-layout">
  <div class="detail">
    <p class="meta-row"><span class="badge"><?= t('track_academic') ?></span><span><?= t('class') ?> <?= num($class) ?></span><span>•</span><span><?= num(count($papers)) ?> <?= t('papers') ?></span><span>•</span><span><?= sprintf(t('class_lessons'), num((int)$class_lessons_total)) ?></span></p>
    <p><?= t('track_academic_d') ?></p>
    <?php foreach ($papers as $p): ?>
      <?php
      $can_view_paper = $is_teacher || !empty($class_subscribed) || !empty($class_paper_enrolled[(int)$p['id']]);
      $mods = [];
      foreach (($p['lessons_list'] ?? []) as $ls) { $mods[(int)ceil($ls['lesson_no'] / 6)][] = $ls; }
      ?>
      <h2 style="margin-top:28px"><?= ($p['paper'] ?? '') === '1st Paper' ? t('paper_1st') : t('paper_2nd') ?></h2>
      <p class="section-lead"><?= e($p['title']) ?> • <?= num(count($p['lessons_list'] ?? [])) ?> <?= t('lessons') ?></p>
      <div class="accordion" data-accordion>
        <?php foreach ($mods as $mn => $mlist): ?>
        <div class="acc<?= $mn === 1 ? ' open' : '' ?>">
          <button class="head" type="button" aria-expanded="<?= $mn === 1 ? 'true' : 'false' ?>"><span><?= e(module_heading($p, $mn)) ?></span><span aria-hidden="true"><?= $mn === 1 ? '−' : '+' ?></span></button>
          <div class="body">
            <?php foreach ($mlist as $ls): ?>
            <div class="lesson-row">
              <?php if ($can_view_paper): ?>
                <a href="/course/<?= (int)$p['id'] ?>/lesson/<?= (int)$ls['lesson_no'] ?>"><?= num((int)$ls['lesson_no']) ?>. <?= e($ls['title']) ?></a>
              <?php else: ?>
                <span><?= num((int)$ls['lesson_no']) ?>. <?= e($ls['title']) ?></span><span class="lock" aria-hidden="true">🔒</span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if (!$can_view_paper): ?>
      <div class="card-actions"><a class="btn btn-secondary" href="/buy/<?= (int)$p['id'] ?>?plan=once"><?= sprintf(t('buy_once_paper'), tk((int)($p['price_bdt'] ?? 0))) ?></a></div>
      <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($papers)): ?><p><?= t('no_courses') ?></p><?php endif; ?>
  </div>
  <aside class="buy-card" aria-label="<?= e(t('fee_know')) ?>">
    <p class="price-row"><span class="price"><?= tk(track_monthly_fee('academic')) ?></span><span>/ <?= t('month') ?></span></p>
    <p class="live-line"><?= live_class_text() ?></p>
    <p class="once-line"><?= t('subscribe_track_d') ?></p>
    <?php if (!empty($class_subscribed)): ?>
      <p><span class="badge green"><?= t('enrolled') ?> ✓</span></p>
    <?php elseif ($first_paper): ?>
      <a class="btn btn-primary" href="/buy/<?= (int)$first_paper['id'] ?>?plan=monthly"><?= sprintf(t('subscribe_track'), tk(track_monthly_fee('academic'))) ?></a>
    <?php endif; ?>
    <ul class="includes-list">
      <li><?= t('incl_live') ?></li>
      <li><?= t('incl_recorded') ?></li>
      <li><?= t('incl_notes') ?></li>
      <li><?= t('incl_progress') ?></li>
    </ul>
  </aside>
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
