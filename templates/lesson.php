<?php
// Lesson player (two-pane): $course, $lesson, $total, $all_lessons,
// $done_lessons (done lesson numbers), $my_note, $my_rating.
$modules = [];
foreach ($all_lessons as $l) {
    $m = (int)ceil((int)$l['lesson_no'] / 6);
    $modules[$m][] = $l;
}
$done_count = count($done_lessons ?? []);
$pct = $total > 0 ? (int)round($done_count / $total * 100) : 0;
$is_done = in_array((int)$lesson['lesson_no'], $done_lessons ?? [], true);
$cur_mod = (int)ceil((int)$lesson['lesson_no'] / 6);
$ln = (int)$lesson['lesson_no'];
?>
<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <a href="/courses"><?= t('crumb_courses') ?></a> / <a href="/course/<?= (int)$course['id'] ?>"><?= e($course['title']) ?></a></nav>

<button class="btn btn-secondary list-toggle" type="button" id="lessonListToggle" aria-expanded="false" data-show="<?= e(t('lp_list_show')) ?>" data-hide="<?= e(t('lp_list_hide')) ?>"><?= t('lp_list_show') ?></button>

<div class="player">
  <aside class="player-side collapsed" id="playerSide" aria-label="<?= e(t('curriculum')) ?>">
    <div class="ps-head">
      <h2><?= e($course['title']) ?></h2>
      <div class="ps-progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(t('lp_progress')) ?>"><i style="width:<?= $pct ?>%"></i></div>
      <div class="ps-progress-txt"><?= sprintf(t('lp_progress'), num($done_count) . ' / ' . num((int)$total)) ?> (<?= num($pct) ?>%)</div>
    </div>
    <?php foreach ($modules as $m => $ls): ?>
      <div class="module<?= $cur_mod === $m ? ' open' : '' ?>">
        <button class="head" type="button" data-acc><?= e(module_heading($course, $m)) ?> <span aria-hidden="true"><?= $cur_mod === $m ? '−' : '+' ?></span></button>
        <div class="body">
          <?php foreach ($ls as $l): ?>
          <?php $l_done = in_array((int)$l['lesson_no'], $done_lessons ?? [], true); $l_cur = (int)$l['lesson_no'] === $ln; ?>
          <div class="lesson-row<?= $l_cur ? ' current' : '' ?>">
            <span class="row-ico<?= $l_done ? ' done' : '' ?>" aria-hidden="true"><?= $l_done ? '✓' : '▶' ?></span>
            <a href="/course/<?= (int)$course['id'] ?>/lesson/<?= (int)$l['lesson_no'] ?>"<?php if ($l_cur): ?> aria-current="true"<?php endif; ?>><?= e($l['title']) ?></a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </aside>

  <div class="player-stage">
    <div class="player-topbar">
      <div class="ps-progress" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(t('lp_progress')) ?>"><i style="width:<?= $pct ?>%"></i></div>
      <?php if ($ln > 1): ?><a class="btn btn-secondary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln - 1 ?>">← <?= t('lp_prev_lesson') ?></a><?php endif; ?>
      <?php if ($ln < (int)$total): ?><a class="btn btn-primary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln + 1 ?>"><?= t('lp_next_lesson') ?> →</a><?php endif; ?>
    </div>

    <?php $video_id = youtube_id((string)($lesson['video_url'] ?? '')); ?>
    <?php if ($video_id !== ''): ?>
    <div class="video-box">
      <iframe src="https://www.youtube-nocookie.com/embed/<?= e($video_id) ?>" title="<?= e($lesson['title']) ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
    <?php else: ?>
    <div class="video-box" aria-label="<?= e(t('video_coming_soon')) ?>">
      <div class="vb-ico" aria-hidden="true">▶</div>
      <h3><?= t('video_coming_soon') ?></h3>
      <p><?= t('lp_video_note') ?></p>
    </div>
    <?php endif; ?>

    <div class="markdone-bar">
      <div class="md-txt">
        <?php if ($is_done): ?><span class="done-state"><?= t('lp_done') ?> ✓</span>
        <?php else: ?><?= t('lp_watch_done') ?><small><?= t('lp_all_done') ?></small><?php endif; ?>
      </div>
      <?php if (!$is_done): ?>
      <form method="post" action="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln ?>/done" style="margin:0;max-width:none">
        <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
        <button class="btn btn-green" type="submit"><?= t('lp_mark_done') ?></button>
      </form>
      <?php endif; ?>
    </div>

    <article class="player-card">
      <p style="margin:0"><a href="/course/<?= (int)$course['id'] ?>">← <?= t('back_course') ?></a></p>
      <h1 style="margin-top:10px"><?= t('lesson') ?> <?= num($ln) ?> — <?= e($lesson['title']) ?></h1>
      <div class="meta-row">
        <span class="unit"><?= e($course['paper']) ?></span>
        <span><?= t('class') ?> <?= e($course['class_level']) ?></span> •
        <span><?= sprintf(t('resume_lesson_of'), num($ln), num((int)$total)) ?></span>
      </div>
      <div class="lesson-body">
        <p><?= nl2br(e($lesson['body'])) ?></p>
      </div>
    </article>

    <div class="player-card">
      <div class="acc open">
        <button class="head" type="button" data-acc><span><?= t('lp_note') ?></span><span aria-hidden="true">−</span></button>
        <div class="body">
          <p><?= t('lp_note_d') ?></p>
          <form method="post" action="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln ?>/note" style="max-width:none">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <label class="visually-hidden" for="lessonNote"><?= t('lp_note') ?></label>
            <textarea id="lessonNote" name="note" placeholder="<?= e(t('lp_note_ph')) ?>"><?= e($my_note ?? '') ?></textarea>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= t('lp_note_save') ?></button></div>
          </form>
        </div>
      </div>
    </div>

    <div class="player-card">
      <div class="acc open">
        <button class="head" type="button" data-acc><span><?= t('lp_rate') ?></span><span aria-hidden="true">−</span></button>
        <div class="body">
          <p><?= t('lp_rate_d') ?></p>
          <?php if (!empty($my_rating)): ?><p><strong><?= sprintf(t('lp_rated'), num((int)$my_rating)) ?></strong></p><?php endif; ?>
          <form method="post" action="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln ?>/rate" style="max-width:none">
            <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
            <div class="stars" role="group" aria-label="<?= e(t('lp_rate')) ?>">
              <?php for ($s = 1; $s <= 5; $s++): ?>
              <button type="submit" name="stars" value="<?= $s ?>" class="<?= !empty($my_rating) && $s <= (int)$my_rating ? 'on' : '' ?>" aria-label="<?= num($s) ?> ★">★</button>
              <?php endfor; ?>
            </div>
            <p style="color:var(--muted);font-size:14px"><?= t('lp_rate_hint') ?></p>
          </form>
        </div>
      </div>
    </div>

    <div class="lesson-nav">
      <?php if ($ln > 1): ?><a class="btn btn-secondary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln - 1 ?>"><?= t('prev_lesson') ?></a><?php endif; ?>
      <?php if ($ln < (int)$total): ?><a class="btn btn-primary" href="/course/<?= (int)$course['id'] ?>/lesson/<?= $ln + 1 ?>"><?= t('next_lesson') ?></a><?php endif; ?>
      <a class="btn btn-secondary" href="/homework"><?= t('nav_homework') ?></a>
      <a class="btn btn-secondary" href="/dashboard"><?= t('my_courses_link') ?></a>
    </div>
  </div>
</div>
