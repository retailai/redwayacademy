<?php
// My Courses — the student's two course previews: Spoken English and
// Academic English (own class). $cards: track, title, desc, banner,
// banner_alt, total, done_count, enrolled, course_url, continue_url, btn_class.
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <a href="/dashboard/student"><?= t('menu_dashboard') ?></a> / <?= t('my_courses_title') ?></nav>
<h1 class="page-title"><?= t('my_courses_title') ?></h1>
<p class="section-lead"><?= t('my_courses_sub') ?></p>

<div class="track-grid">
  <?php foreach ($cards as $card): ?>
  <?php $pct = ($card['total'] ?? 0) > 0 ? (int)round($card['done_count'] / $card['total'] * 100) : 0; ?>
  <?php $dest = !empty($card['enrolled']) ? ($card['continue_url'] ?? $card['course_url']) : ($card['buy_url'] ?? $card['course_url']); ?>
  <article class="card track-card track-<?= e($card['track']) ?>">
    <div class="track-cover"><a href="<?= e($dest) ?>"><img class="track-banner" src="<?= e($card['banner']) ?>" alt="<?= e($card['banner_alt']) ?>"></a></div>
    <div class="track-body">
      <h2><a href="<?= e($dest) ?>"><?= e($card['title']) ?></a></h2>
      <p><?= e($card['desc']) ?></p>
      <?php if (!empty($card['enrolled'])): ?>
      <p class="track-progress-txt"><?= sprintf(t('lp_progress'), num($card['done_count']) . ' / ' . num((int)$card['total'])) ?> (<?= num($pct) ?>%)<?php if ((int)$card['done_count'] === 0): ?> — <?= t('not_started') ?><?php endif; ?></p>
      <div class="track-progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e($card['title']) ?>"><i style="width:<?= $pct ?>%"></i></div>
      <?php else: ?>
      <p><span class="badge"><?= t('not_enrolled') ?></span></p>
      <p><?= t('not_enrolled_d') ?></p>
      <?php endif; ?>
      <div class="track-actions">
        <?php if (!empty($card['enrolled'])): ?>
        <a class="btn <?= e($card['btn_class']) ?>" href="<?= e($card['continue_url']) ?>"><?= t('continue_learning') ?> →</a>
        <?php else: ?>
        <a class="btn btn-secondary" href="<?= e($card['buy_url']) ?>"><?= t('buy_now') ?> →</a>
        <?php endif; ?>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
</div>

<?php if (count($cards) < 2): ?>
<p style="margin-top:16px"><a class="btn btn-secondary" href="/courses"><?= t('browse_courses') ?></a></p>
<?php endif; ?>
