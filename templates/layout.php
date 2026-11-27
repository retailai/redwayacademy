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
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css?v=v7">
</head>
<body data-soon-msg="<?= e(t('soon_msg')) ?>">
<a class="skip-link" href="#main">Skip to main content</a>
<?php if (!empty($retake_active) && empty($_COOKIE['rw_announce_hide'])): ?>
<div class="announce" role="note" id="announceBar"><span><?= t('announce') ?> — <a href="/retake"><?= t('nav_retake') ?></a></span><button class="announce-close" type="button" id="announceClose" aria-label="<?= e(t('announce_close')) ?>">✕</button></div>
<?php endif; ?>
<header class="site-header" id="siteHeader">
  <div class="header-row">
    <button class="icon-btn burger" type="button" id="burgerBtn" aria-label="Menu" aria-expanded="false" aria-controls="drawer">☰</button>
    <a class="brand" href="/" aria-label="Redway Academy — home">
      <span class="brand-logo"><img src="/assets/img/logo.png" alt="Redway Academy — Mistakes Mark the Way" width="1568" height="535"></span>
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
      <?php if (!empty($retake_active)): ?><a href="/retake"><?= t('nav_retake') ?></a><?php endif; ?>
      <a href="/testimonials"><?= t('nav_reviews') ?></a>
      <div class="more-menu" id="moreMenu">
        <a href="#" id="moreBtn" aria-haspopup="true" aria-expanded="false"><?= t('nav_more') ?> ▾</a>
        <div class="more-pop" role="menu" aria-label="<?= e(t('nav_more')) ?>">
          <a href="#" role="menuitem" data-soon class="dimmed"><span><?= t('nav_checkin') ?></span> <span class="phase2-tag"><?= t('checkin_phase2') ?></span></a>
          <a href="#" role="menuitem" data-soon class="dimmed"><span><?= t('nav_notes') ?></span> <span class="phase2-tag"><?= t('notes_later') ?></span></a>
          <a href="/community" role="menuitem"><?= t('nav_community') ?></a>
        </div>
      </div>
    </nav>
    <div class="header-right">
      <span class="lang-switch" role="group" aria-label="Language / ভাষা">
        <a href="<?= e(url_with_query(['lang' => 'bn'])) ?>"<?php if (lang_is_bn()): ?> class="active" aria-current="true"<?php endif; ?>><?= t('lang_bangla') ?></a>
        <a href="<?= e(url_with_query(['lang' => 'en'])) ?>"<?php if (!lang_is_bn()): ?> class="active" aria-current="true"<?php endif; ?>><?= t('lang_english') ?></a>
      </span>
      <button class="icon-btn" type="button" id="themeToggle" aria-label="<?= e(t('theme_toggle')) ?>" title="<?= e(t('theme_toggle')) ?>">◐</button>
      <?php if ($current_user): ?>
        <a class="bell" href="/notifications" aria-label="<?= e(t('nav_notifications')) ?>">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          <?php if ($unread_count): ?><span class="nav-badge" aria-label="<?= (int)$unread_count ?> unread"><?= (int)$unread_count ?></span><?php endif; ?>
        </a>
        <div class="avatar-menu" id="avatarMenu">
          <button class="avatar-btn" type="button" aria-haspopup="true" aria-expanded="false" id="avatarBtn">
            <span class="avatar-circle" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($current_user['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
            <span class="avatar-name"><?= e($current_user['name']) ?></span>
          </button>
          <div class="avatar-dropdown" role="menu" aria-label="Account menu">
            <div class="who"><b><?= e($current_user['name']) ?></b><span><?= e($current_user['phone']) ?> • <?= e(role_label($current_user['role'])) ?><?php if ($is_super_admin): ?> • <?= t('super_admin') ?><?php endif; ?></span></div>
            <?php if ($current_user['role'] === 'student'): ?><a href="/my-courses" role="menuitem"><?= t('menu_my_courses') ?></a>
            <?php else: ?><a href="<?= e(dashboard_url($current_user)) ?>" role="menuitem"><?= t('menu_dashboard') ?></a><?php endif; ?>
            <a href="/notifications" role="menuitem"><?= t('menu_notifications') ?><?php if ($unread_count): ?> (<?= (int)$unread_count ?>)<?php endif; ?></a>
            <?php if ($current_user['role'] === 'teacher'): ?><a href="/admin" role="menuitem"><?= t('nav_admin') ?></a><?php endif; ?>
            <a href="/logout" role="menuitem"><?= t('menu_logout') ?></a>
          </div>
        </div>
      <?php else: ?>
        <a class="btn btn-secondary" href="/login"><?= t('nav_login') ?></a>
        <a class="btn" href="/register"><?= t('nav_register') ?></a>
      <?php endif; ?>
      <?php if (!$current_user): ?><a class="icon-btn profile-btn" id="profileBtn" href="/login" aria-label="Profile"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a><?php endif; ?>
    </div>
  </div>
</header>
<div class="drawer-scrim" id="drawerScrim"></div>
<aside class="drawer" id="drawer" aria-label="Menu">
  <div class="drawer-head">
    <span class="brand-logo"><img src="/assets/img/logo.png" alt="Redway Academy" width="1568" height="535"></span>
    <button class="icon-btn" type="button" id="drawerClose" aria-label="Close">✕</button>
  </div>
  <div class="drawer-tools"><span class="lang-switch" role="group" aria-label="Language / ভাষা">
    <a href="<?= e(url_with_query(['lang' => 'bn'])) ?>"<?php if (lang_is_bn()): ?> class="active" aria-current="true"<?php endif; ?>><?= t('lang_bangla') ?></a>
    <a href="<?= e(url_with_query(['lang' => 'en'])) ?>"<?php if (!lang_is_bn()): ?> class="active" aria-current="true"<?php endif; ?>><?= t('lang_english') ?></a>
  </span><button class="icon-btn" type="button" id="themeToggle2" aria-label="Dark / light mode">◐</button></div>
  <nav>
    <a href="/"<?php if (($_SERVER['REQUEST_URI'] ?? '/') === '/'): ?> aria-current="page"<?php endif; ?>><?= t('nav_home') ?></a>
    <a href="/courses"><?= t('nav_courses') ?></a>
    <a href="/homework"><?= t('nav_homework') ?></a>
    <?php if (!empty($retake_active)): ?><a href="/retake"><?= t('nav_retake') ?></a><?php endif; ?>
    <a href="/testimonials"><?= t('nav_reviews') ?></a>
    <a href="#" data-soon class="dimmed"><span><?= t('nav_checkin') ?></span> <span class="phase2-tag"><?= t('checkin_phase2') ?></span></a>
    <a href="#" data-soon class="dimmed"><span><?= t('nav_notes') ?></span> <span class="phase2-tag"><?= t('notes_later') ?></span></a>
    <a href="/community"><?= t('nav_community') ?></a>
  </nav>
  <div class="drawer-actions">
    <?php if ($current_user): ?>
      <?php if ($current_user['role'] === 'student'): ?><a class="btn" href="/my-courses"><?= t('menu_my_courses') ?></a>
      <?php else: ?><a class="btn" href="<?= e(dashboard_url($current_user)) ?>"><?= t('menu_dashboard') ?></a><?php endif; ?>
      <a class="btn btn-secondary" href="/logout"><?= t('menu_logout') ?></a>
    <?php else: ?>
      <a class="btn" href="/login"><?= t('nav_login') ?></a>
      <a class="btn btn-secondary" href="/register"><?= t('nav_register') ?></a>
    <?php endif; ?>
  </div>
</aside>
<main class="container" id="main">
  <?php foreach (flashes() as $m): ?><div class="flash" role="status"><p><?= e($m) ?></p></div><?php endforeach; ?>
  <?php include $view_file; ?>
</main>
<footer class="site-footer">
  <div class="footer-grid">
    <div>
      <p style="margin-top:4px"><strong><?= t('foot_motto') ?></strong></p>
      <p><?= t('foot_motto2') ?></p>
    </div>
    <div class="social-row" aria-label="Social links">
      <a href="https://wa.me/880<?= substr(CONTACT_PHONE, 1) ?>" aria-label="WhatsApp" title="WhatsApp">W</a>
      <a href="https://www.youtube.com/@RedwayAcademy" aria-label="YouTube" title="YouTube">▶</a>
      <a href="https://www.facebook.com/profile.php?id=61596806529388" aria-label="Facebook" title="Facebook">f</a>
      <a href="https://www.linkedin.com/company/redwayacademy" aria-label="LinkedIn" title="LinkedIn">in</a>
      <a href="https://www.instagram.com/redwayacademy/" aria-label="Instagram" title="Instagram">◎</a>
      <a href="#" data-soon aria-label="TikTok" title="TikTok">♪</a>
    </div>
    <div>
      <h3><?= t('foot_org') ?></h3>
      <ul>
        <li><a href="/"><?= t('nav_home') ?></a></li>
        <li><a href="/courses"><?= t('nav_courses') ?></a></li>
        <?php if (!empty($retake_active)): ?><li><a href="/retake"><?= t('nav_retake') ?></a></li><?php endif; ?>
        <li><a href="/testimonials"><?= t('nav_reviews') ?></a></li>
      </ul>
    </div>
    <div>
      <h3><?= t('foot_explore') ?></h3>
      <ul>
        <li><a href="/homework"><?= t('nav_homework') ?></a></li>
        <li><a href="#" data-soon class="dimmed"><span><?= t('nav_checkin') ?></span> <span class="phase2-tag"><?= t('checkin_phase2') ?></span></a></li>
        <li><a href="/login"><?= t('nav_login') ?></a></li>
        <li><a href="/register"><?= t('nav_register') ?></a></li>
      </ul>
    </div>
    <div>
      <h3><?= t('foot_help') ?></h3>
      <p><a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a><br>
      <?= CONTACT_ADDRESS ?><br>
      <a href="mailto:info@redwayacademy.com">info@redwayacademy.com</a><br>
      bKash / Nagad: <?= CONTACT_PHONE ?></p>
      <ul><li><a href="/terms"><?= t('foot_terms') ?></a></li><li><a href="/privacy"><?= t('foot_privacy') ?></a></li><li><a href="#" data-soon class="dimmed"><span><?= t('foot_refund') ?></span> <span class="phase2-tag"><?= t('checkin_phase2') ?></span></a></li></ul>
    </div>
  </div>
  <div class="copyright-bar"><p><?= sprintf(t('rights'), date('Y')) ?> — Mistakes Mark the Way</p></div>
</footer>
<button class="back-top" id="backTop" type="button" aria-label="<?= e(t('back_top')) ?>">↑</button>
<script src="/assets/js/app.js?v=v4"></script>
</body>
</html>
