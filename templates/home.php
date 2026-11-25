<?php
// Home: $courses, $testimonials, $current_user, $resume_course (optional).
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
        <a class="btn btn-secondary" href="/retake"><?= t('cta_retake') ?></a>
      </div>
      <div class="proof" aria-label="Redway Academy at a glance">
        <div><b><?= num(11) ?></b><span><?= t('stat_courses') ?></span></div>
        <div><b><?= num('6') ?>–<?= num(12) ?></b><span><?= t('stat_classes') ?></span></div>
        <div><b><?= tk(699) ?></b><span><?= t('stat_starting') ?></span></div>
        <div><b><?= num(3) ?></b><span><?= t('stat_logins') ?></span></div>
      </div>
    </div>
    <div class="hero-art" aria-hidden="false">
      <div class="art-card">
        <h3><?= t('method_title') ?></h3>
        <p><?= t('method_sub') ?></p>
        <div class="art-steps">
          <div class="art-step"><b>1</b><span><?= t('step1_t') ?></span></div>
          <div class="art-step"><b>2</b><span><?= t('step2_t') ?></span></div>
          <div class="art-step"><b>3</b><span><?= t('step3_t') ?></span></div>
          <div class="art-step"><b>4</b><span><?= t('step4_t') ?></span></div>
        </div>
      </div>
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

<section aria-labelledby="method-heading">
  <div class="section-head"><div><h2 id="method-heading"><?= t('method_title') ?></h2><p><?= t('method_sub') ?></p></div></div>
  <div class="grid">
    <div class="card step-card step-red"><span class="step-number" aria-hidden="true">1</span><h3><?= t('step1_t') ?></h3><p><?= t('step1_d') ?></p></div>
    <div class="card step-card step-yellow"><span class="step-number" aria-hidden="true">2</span><h3><?= t('step2_t') ?></h3><p><?= t('step2_d') ?></p></div>
    <div class="card step-card step-blue"><span class="step-number" aria-hidden="true">3</span><h3><?= t('step3_t') ?></h3><p><?= t('step3_d') ?></p></div>
    <div class="card step-card step-green"><span class="step-number" aria-hidden="true">4</span><h3><?= t('step4_t') ?></h3><p><?= t('step4_d') ?></p></div>
  </div>
</section>

<section aria-labelledby="story-heading">
  <div class="detail">
    <h2 id="story-heading" style="margin-top:0"><?= t('story_title') ?></h2>
    <p><?= t('story_text') ?></p>
  </div>
</section>

<section aria-labelledby="picked-heading">
  <div class="section-head"><div><h2 id="picked-heading"><?= t('picked_title') ?></h2><p><?= t('picked_sub') ?></p></div><a class="btn btn-secondary" href="/courses"><?= t('all_courses') ?></a></div>
  <div class="carousel-wrap">
    <button class="carousel-btn prev" type="button" data-carousel-prev aria-label="<?= e(t('prev')) ?>">‹</button>
    <div class="carousel" data-carousel>
      <?php foreach ($courses as $c): ?>
      <?php include __DIR__ . '/partials/course_card.php'; ?>
      <?php endforeach; ?>
    </div>
    <button class="carousel-btn next" type="button" data-carousel-next aria-label="<?= e(t('next')) ?>">›</button>
  </div>
</section>

<section aria-labelledby="path-heading">
  <div class="section-head"><div><h2 id="path-heading"><?= t('path_title') ?></h2><p><?= t('path_sub') ?></p></div></div>
  <div class="grid">
    <div class="card"><h3><?= t('path1_t') ?></h3><p><?= t('path1_d') ?></p><div class="card-actions"><a class="btn btn-secondary" href="/courses?paper=1st+Paper"><?= t('details') ?></a></div></div>
    <div class="card"><h3><?= t('path2_t') ?></h3><p><?= t('path2_d') ?></p><div class="card-actions"><a class="btn btn-secondary" href="/courses?paper=2nd+Paper"><?= t('details') ?></a></div></div>
    <div class="card"><h3><?= t('path3_t') ?></h3><p><?= t('path3_d') ?></p><div class="card-actions"><a class="btn btn-secondary" href="/courses?paper=Spoken+English"><?= t('details') ?></a></div></div>
  </div>
</section>

<section aria-labelledby="benefits-heading">
  <div class="section-head"><div><h2 id="benefits-heading"><?= t('benefits_title') ?></h2><p><?= t('benefits_sub') ?></p></div></div>
  <div class="grid">
    <div class="card"><h3><?= t('ben1_t') ?></h3><p><?= t('ben1_d') ?></p></div>
    <div class="card"><h3><?= t('ben2_t') ?></h3><p><?= t('ben2_d') ?></p></div>
    <div class="card"><h3><?= t('ben3_t') ?></h3><p><?= t('ben3_d') ?></p></div>
    <div class="card"><h3><?= t('ben4_t') ?></h3><p><?= t('ben4_d') ?></p></div>
  </div>
</section>

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
