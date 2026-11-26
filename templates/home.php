<?php
// Home: $courses (11 DB rows, presented as 2 courses), $total_lessons,
// $testimonials, $current_user, $resume_course (optional).
$track_stats = ['academic' => ['lessons' => 0, 'classes' => []], 'spoken' => ['lessons' => 0, 'classes' => []]];
foreach ($courses as $c) {
    $tr = (string)($c['track'] ?? 'academic');
    $track_stats[$tr]['lessons'] += (int)($c['lessons'] ?? 0);
    if (!empty($c['class_level'])) $track_stats[$tr]['classes'][$c['class_level']] = true;
}
$fee = track_monthly_fee('academic');
?>
<section class="hero" aria-labelledby="hero-heading">
  <div class="hero-grid">
    <div>
      <span class="hero-eyebrow"><i aria-hidden="true"></i> <?= t('hero_eyebrow') ?></span>
      <h1 id="hero-heading"><span class="hl-red"><?= t('hero_a') ?></span><br><span class="hl-blue"><?= t('hero_b') ?></span></h1>
      <p class="slogan"><?= BRAND_SLOGAN_BN ?> • Mistakes Mark the Way</p>
      <p class="lead"><?= t('hero_intro') ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="/courses"><?= t('cta_courses') ?></a>
        <?php if (!empty($retake_active)): ?><a class="btn btn-secondary" href="/retake"><?= t('cta_retake') ?></a><?php endif; ?>
      </div>
      <div class="hero-stats" aria-label="Redway Academy at a glance">
        <div><b><?= num(2) ?></b><span><?= t('stat_courses') ?></span></div>
        <div><b><?= num((int)($total_lessons ?? 244)) ?></b><span><?= t('stat_lessons') ?></span></div>
        <div><b><?= tk($fee) ?></b><span><?= t('stat_monthly') ?></span></div>
        <div><b><?= num(6) ?>–<?= num(12) ?></b><span><?= t('stat_classes') ?></span></div>
      </div>
    </div>
    <div class="notebook" aria-label="<?= e(t('hero_nb_tag')) ?>">
      <span class="nb-tick" aria-hidden="true">✓</span>
      <span class="nb-tag"><i aria-hidden="true"></i> <?= t('hero_nb_tag') ?></span>
      <div class="nb-lines">
        <div class="nb-line">He <s>go</s> to school. → <span class="nb-fix">goes</span></div>
        <div class="nb-line">They <s>don't</s> know. → <span class="nb-fix">doesn't</span></div>
        <div class="nb-line">She <span class="nb-correct">is reading</span> a book. ✓</div>
      </div>
      <p class="nb-note"><?= t('hero_nb_note') ?></p>
    </div>
  </div>
</section>

<?php if (!empty($current_user)): ?>
<section aria-labelledby="resume-heading">
  <div class="resume-card">
    <div class="resume-info">
      <h2 id="resume-heading"><?= t('resume_title') ?></h2>
      <?php if (!empty($resume_course)): ?>
        <p><strong><?= e($resume_course['title']) ?></strong> — <?= t('class') ?> <?= e($resume_course['class_level']) ?> • <?= num((int)$resume_course['lessons']) ?> <?= t('lessons') ?></p>
      <?php else: ?>
        <p><?= sprintf(t('resume_welcome'), e($current_user['name'])) ?></p>
      <?php endif; ?>
    </div>
    <?php if (!empty($resume_course)): ?>
      <div class="resume-progress">
        <div class="progress" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" aria-label="<?= e(t('resume_title')) ?>"><i style="width:0%"></i></div>
        <small><?= sprintf(t('resume_lesson_of'), num(1), num((int)$resume_course['lessons'])) ?> • <?= t('resume_not_started') ?></small>
      </div>
      <a class="btn btn-green" href="/course/<?= (int)$resume_course['id'] ?>/lesson/1"><?= t('resume_continue') ?> →</a>
    <?php else: ?>
      <a class="btn btn-green" href="<?= e(dashboard_url($current_user)) ?>"><?= t('resume_go_dashboard') ?> →</a>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<section aria-labelledby="courses-heading">
  <div class="section-head"><div><h2 id="courses-heading"><?= t('courses_title') ?></h2><p><?= t('courses_sub') ?></p></div></div>
  <div class="track-grid">
    <article class="card track-card track-spoken">
      <div class="track-cover"><span class="thumb-badge"><?= t('track_spoken') ?></span><span class="thumb-letter" aria-hidden="true">Sp</span></div>
      <div class="track-body">
        <h2><?= t('track_spoken') ?></h2>
        <p class="track-count"><?= sprintf(t('modules_count'), num(4)) ?> • <?= num($track_stats['spoken']['lessons']) ?> <?= t('lessons') ?></p>
        <ul class="track-bullets">
          <li><?= t('track_spoken_p1') ?></li>
          <li><?= t('track_spoken_p2') ?></li>
          <li><?= t('track_spoken_p3') ?></li>
        </ul>
        <p class="track-fee"><strong><?= tk($fee) ?></strong> / <?= t('month') ?></p>
        <p class="track-live"><?= live_class_text() ?></p>
        <div class="track-actions">
          <a class="btn btn-primary" href="/courses?track=spoken"><?= t('start_course') ?></a>
          <a class="btn btn-secondary" href="/courses?track=spoken"><?= t('fee_know') ?></a>
        </div>
      </div>
    </article>
    <article class="card track-card track-academic">
      <div class="track-cover"><span class="thumb-badge"><?= t('track_academic') ?></span><span class="thumb-letter" aria-hidden="true">Ac</span></div>
      <div class="track-body">
        <h2><?= t('track_academic') ?></h2>
        <p class="track-count"><?= num(count($track_stats['academic']['classes'])) ?> <?= t('classes_count') ?> (<?= num(6) ?>–<?= num(12) ?>) • <?= num($track_stats['academic']['lessons']) ?> <?= t('lessons') ?></p>
        <ul class="track-bullets">
          <li><?= t('track_academic_p1') ?></li>
          <li><?= t('track_academic_p2') ?></li>
          <li><?= t('track_academic_p3') ?></li>
        </ul>
        <p class="track-fee"><strong><?= tk($fee) ?></strong> / <?= t('month') ?></p>
        <p class="track-live"><?= live_class_text() ?></p>
        <div class="track-actions">
          <a class="btn btn-primary" href="/courses?track=academic"><?= t('start_course') ?></a>
          <a class="btn btn-secondary" href="/courses?track=academic"><?= t('fee_know') ?></a>
        </div>
      </div>
    </article>
  </div>
</section>

<section aria-labelledby="how3-heading">
  <div class="section-head"><div><h2 id="how3-heading"><?= t('how3_title') ?></h2><p><?= t('how3_sub') ?></p></div></div>
  <div class="steps3">
    <div class="step3"><span class="step-num" aria-hidden="true">1</span><h3><?= t('how3_1_t') ?></h3><p><?= t('how3_1_d') ?></p></div>
    <div class="step3"><span class="step-num" aria-hidden="true">2</span><h3><?= t('how3_2_t') ?></h3><p><?= t('how3_2_d') ?></p></div>
    <div class="step3"><span class="step-num" aria-hidden="true">3</span><h3><?= t('how3_3_t') ?></h3><p><?= t('how3_3_d') ?></p></div>
  </div>
</section>

<section aria-labelledby="features-heading">
  <div class="section-head"><div><h2 id="features-heading"><?= t('features_title') ?></h2></div></div>
  <div class="feat-grid">
    <div class="feat-tile"><div class="feat-ico" aria-hidden="true">▶</div><h3><?= t('feat1_t') ?></h3><p><?= t('feat1_d') ?></p></div>
    <div class="feat-tile"><div class="feat-ico" aria-hidden="true">✎</div><h3><?= t('feat2_t') ?></h3><p><?= t('feat2_d') ?></p></div>
    <div class="feat-tile"><div class="feat-ico" aria-hidden="true">▰</div><h3><?= t('feat3_t') ?></h3><p><?= t('feat3_d') ?></p></div>
    <div class="feat-tile"><div class="feat-ico" aria-hidden="true">✓</div><h3><?= t('feat4_t') ?></h3><p><?= t('feat4_d') ?></p></div>
  </div>
</section>

<?php if (!empty($retake_active)): ?>
<section aria-label="<?= e(t('cta_retake_t')) ?>">
  <div class="cta-band retake">
    <div><h2><?= t('cta_retake_t') ?></h2><p><?= t('cta_retake_d') ?></p></div>
    <a class="btn" href="/retake"><?= t('cta_retake_btn') ?></a>
  </div>
</section>
<?php else: ?>
<section aria-label="<?= e(t('cta_free_t')) ?>">
  <div class="cta-band">
    <div><h2><?= t('cta_free_t') ?></h2><p><?= t('cta_free_d') ?></p></div>
    <button class="btn" type="button" data-soon><?= t('cta_free_btn') ?></button>
  </div>
</section>
<?php endif; ?>

<section aria-labelledby="lesson-heading">
  <h2 id="lesson-heading"><?= t('lesson_free_title') ?></h2>
  <div class="dialogue"><p><?= t('lesson_free_body') ?></p></div>
</section>

<?php if (!empty($testimonials)): ?>
<section aria-labelledby="reviews-heading">
  <div class="section-head"><div><h2 id="reviews-heading"><?= t('reviews_title') ?></h2><p><?= t('reviews_sub') ?></p></div><a class="btn btn-secondary" href="/testimonials"><?= t('see_all_reviews') ?></a></div>
  <div class="carousel-wrap">
    <button class="carousel-btn prev" type="button" data-carousel-prev aria-label="<?= e(t('prev')) ?>">‹</button>
    <div class="carousel" data-carousel>
      <?php foreach ($testimonials as $t): ?>
      <figure class="card" style="margin:0"><blockquote style="margin:0"><p>"<?= e($t['message']) ?>"</p></blockquote><figcaption><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, <?= t('class') ?> <?= e($t['class_level']) ?><?php endif; ?></figcaption></figure>
      <?php endforeach; ?>
    </div>
    <button class="carousel-btn next" type="button" data-carousel-next aria-label="<?= e(t('next')) ?>">›</button>
  </div>
</section>
<?php endif; ?>

<section aria-labelledby="contact-heading">
  <h2 id="contact-heading"><?= t('contact_title') ?></h2>
  <div class="dialogue">
    <p><?= t('contact_call') ?>: <strong><a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a></strong></p>
    <p><?= t('contact_visit') ?>: <strong><?= CONTACT_ADDRESS ?></strong></p>
    <p><?= t('contact_pay') ?>: <strong>bKash / Nagad: <?= CONTACT_PHONE ?></strong> — <?= t('contact_pay_d') ?></p>
  </div>
</section>
