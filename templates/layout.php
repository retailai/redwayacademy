<!doctype html>
<html lang="<?= html_lang() ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Redway Academy — NCTB English for Class 6–12. Mistakes Mark the Way: English for Today, English 2nd Paper and Spoken English.">
<meta name="theme-color" content="#4285F4">
<meta name="csrf-token" content="<?= e($csrf_token ?? '') ?>">
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="180x180" href="/assets/img/favicon.png">
<link rel="apple-touch-icon" href="/assets/img/favicon.png">
<title><?= e($title ?? 'Redway Academy — Mistakes Mark the Way') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css?v=lb1">
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<div class="announce" role="note"><?= t('announce') ?> — <a href="/retake"><?= t('nav_retake') ?></a></div>
<header class="site-header">
  <div class="header-row">
    <a class="brand" href="/" aria-label="Redway Academy — home">
      <span class="brand-logo"><img src="/assets/img/logo.png" alt="Redway Academy — Mistakes Mark the Way" width="2400" height="819"></span>
    </a>
    <form class="header-search" role="search" method="get" action="/courses">
      <label class="visually-hidden" for="q"><?= t('search_ph') ?></label>
      <input id="q" name="q" type="search" placeholder="<?= e(t('search_ph')) ?>" value="<?= e($_GET['q'] ?? '') ?>">
      <button type="submit"><?= t('search_btn') ?></button>
    </form>
    <nav class="main-nav" aria-label="Main navigation">
      <a href="/"<?php if (($_SERVER['REQUEST_URI'] ?? '/') === '/'): ?> aria-current="page"<?php endif; ?>><?= t('nav_home') ?></a>
      <a href="/courses"<?php if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/course') || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/buy')): ?> aria-current="page"<?php endif; ?>><?= t('nav_courses') ?></a>
      <a href="/homework"><?= t('nav_homework') ?></a>
      <a href="/retake"><?= t('nav_retake') ?></a>
      <a href="/testimonials"><?= t('nav_reviews') ?></a>
      <a href="/checkin"><?= t('nav_checkin') ?></a>
    </nav>
    <div class="header-right">
      <span class="lang-switch" role="group" aria-label="Language / ভাষা">
        <a href="<?= e(url_with_query(['lang' => 'bn'])) ?>"<?php if (lang_is_bn()): ?> class="active" aria-current="true"<?php endif; ?>><?= t('lang_bangla') ?></a>
        <a href="<?= e(url_with_query(['lang' => 'en'])) ?>"<?php if (!lang_is_bn()): ?> class="active" aria-current="true"<?php endif; ?>><?= t('lang_english') ?></a>
      </span>
      <?php if ($current_user): ?>
        <a class="bell" href="/notifications" aria-label="<?= e(t('nav_notifications')) ?>">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          <?php if ($unread_count): ?><span class="nav-badge" aria-label="<?= (int)$unread_count ?> unread"><?= (int)$unread_count ?></span><?php endif; ?>
        </a>
        <div class="avatar-menu" id="avatarMenu">
          <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false" id="avatarBtn">
            <span class="avatar-circle" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($current_user['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
            <span><?= e($current_user['name']) ?></span>
          </button>
          <div class="avatar-dropdown" role="menu" aria-label="Account menu">
            <div class="who"><b><?= e($current_user['name']) ?></b><span><?= e($current_user['phone']) ?> • <?= e(role_label($current_user['role'])) ?><?php if ($is_super_admin): ?> • <?= t('super_admin') ?><?php endif; ?></span></div>
            <a href="<?= e(dashboard_url($current_user)) ?>" role="menuitem"><?= t('menu_dashboard') ?></a>
            <a href="/notifications" role="menuitem"><?= t('menu_notifications') ?><?php if ($unread_count): ?> (<?= (int)$unread_count ?>)<?php endif; ?></a>
            <?php if ($current_user['role'] === 'teacher'): ?><a href="/admin" role="menuitem"><?= t('nav_admin') ?></a><?php endif; ?>
            <a href="/logout" role="menuitem"><?= t('menu_logout') ?></a>
          </div>
        </div>
      <?php else: ?>
        <a class="btn btn-secondary" href="/login"><?= t('nav_login') ?></a>
        <a class="btn" href="/register"><?= t('nav_register') ?></a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="container" id="main">
  <?php foreach (flashes() as $m): ?><div class="flash" role="status"><p><?= e($m) ?></p></div><?php endforeach; ?>
  <?php include $view_file; ?>
</main>
<footer class="site-footer">
  <div class="footer-grid">
    <div>
      <span class="brand-logo"><img src="/assets/img/logo.png" alt="Redway Academy — Mistakes Mark the Way" width="2400" height="819" loading="lazy"></span>
      <p style="margin-top:12px"><strong><?= t('foot_motto') ?></strong></p>
      <p><?= t('foot_about') ?></p>
      <div class="social-row" aria-label="Social links">
        <a href="https://wa.me/880<?= substr(CONTACT_PHONE, 1) ?>" aria-label="WhatsApp — <?= CONTACT_PHONE ?>" title="WhatsApp">W</a>
        <a href="tel:<?= CONTACT_PHONE ?>" aria-label="Call — <?= CONTACT_PHONE ?>" title="Call">☎</a>
      </div>
    </div>
    <div>
      <h3><?= t('foot_links') ?></h3>
      <ul>
        <li><a href="/"><?= t('nav_home') ?></a></li>
        <li><a href="/courses"><?= t('nav_courses') ?></a></li>
        <li><a href="/homework"><?= t('nav_homework') ?></a></li>
        <li><a href="/retake"><?= t('nav_retake') ?></a></li>
        <li><a href="/testimonials"><?= t('nav_reviews') ?></a></li>
      </ul>
    </div>
    <div>
      <h3><?= t('foot_courses') ?></h3>
      <ul>
        <li><a href="/courses?paper=1st+Paper">English for Today — 1st Paper</a></li>
        <li><a href="/courses?paper=2nd+Paper">English 2nd Paper — Grammar</a></li>
        <li><a href="/courses?paper=Spoken+English">Spoken English</a></li>
        <li><a href="/checkin"><?= t('nav_checkin') ?></a></li>
      </ul>
    </div>
    <div>
      <h3><?= t('foot_contact') ?></h3>
      <p><a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a><br>
      <?= CONTACT_ADDRESS ?><br>
      bKash / Nagad: <?= CONTACT_PHONE ?></p>
    </div>
  </div>
  <div class="copyright-bar"><p><?= sprintf(t('rights'), date('Y')) ?></p></div>
</footer>
<button class="back-top" id="backTop" type="button" aria-label="<?= e(t('back_top')) ?>">↑</button>
<script src="/assets/js/app.js"></script>
</body>
</html>
