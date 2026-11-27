<?php
// Home: $current_user, $resume_course (optional).
// Course cards live on /courses only; stats, notebook, features,
// testimonials and the contact box were removed from the homepage.
?>
<section class="hero" aria-labelledby="hero-heading">
  <div class="hero-single">
    <h1 id="hero-heading"><span class="hl-red"><?= t('hero_a') ?></span><br><span class="hl-blue"><?= t('hero_b') ?></span></h1>
    <p class="slogan"><?= BRAND_SLOGAN_BN ?> • Mistakes Mark the Way</p>
    <p class="lead"><?= t('hero_intro') ?></p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="/courses"><?= t('cta_courses') ?></a>
      <a class="btn btn-green" href="/register"><?= t('cta_register') ?></a>
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

<section aria-labelledby="how3-heading">
  <div class="section-head"><div><h2 id="how3-heading"><?= t('how3_title') ?></h2><p><?= t('how3_sub') ?></p></div></div>
  <div class="steps3">
    <div class="step3"><span class="step-num" aria-hidden="true">1</span><h3><?= t('how3_1_t') ?></h3><p><?= t('how3_1_d') ?></p></div>
    <div class="step3"><span class="step-num" aria-hidden="true">2</span><h3><?= t('how3_2_t') ?></h3><p><?= t('how3_2_d') ?></p></div>
    <div class="step3"><span class="step-num" aria-hidden="true">3</span><h3><?= t('how3_3_t') ?></h3><p><?= t('how3_3_d') ?></p></div>
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
