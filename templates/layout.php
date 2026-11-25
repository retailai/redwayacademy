<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="color-scheme" content="light dark">
<meta name="description" content="Redway Academy — NCTB English for Class 6–12. Mistakes Mark the Way: English for Today, English 2nd Paper and Spoken English.">
<meta name="theme-color" content="#006A4E">
<meta name="csrf-token" content="<?= e($csrf_token ?? '') ?>">
<title><?= e($title ?? 'Redway Academy — Mistakes Mark the Way') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css?v=b25315f">
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="topbar">
  <a class="brand" href="/" aria-label="Redway Academy — home">
    <span class="brand-logo"><img src="/assets/img/logo.png" alt="Redway Academy — Mistakes Mark the Way" width="2400" height="819"></span>
  </a>
  <nav class="site-nav" aria-label="Main navigation">
    <a href="tel:<?= CONTACT_PHONE ?>" aria-label="Call Redway Academy on <?= CONTACT_PHONE ?>">
      <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
      <span><?= CONTACT_PHONE ?></span>
    </a>
    <a href="/">Home</a>
    <a href="/courses">Courses</a>
    <a href="/homework">Homework</a>
    <a href="/retake">Free Retake</a>
    <a href="/testimonials">Reviews</a>
    <a href="/checkin">Check-in</a>
    <?php if ($current_user): ?>
      <a href="/notifications">
        <span class="nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></span>
        <span>Notifications</span>
        <?php if ($unread_count): ?><span class="nav-badge" aria-label="<?= (int)$unread_count ?> unread notifications"><?= (int)$unread_count ?></span><?php endif; ?>
      </a>
      <a href="/dashboard">Dashboard</a>
      <?php if ($current_user['role'] === 'teacher'): ?><a href="/admin">Admin Panel</a><?php endif; ?>
      <a href="/logout">Logout (<?= e($current_user['name']) ?>)</a>
    <?php else: ?>
      <a href="/login">Login</a>
      <a class="btn" href="/register">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container" id="main">
  <?php foreach (flashes() as $m): ?><div class="flash" role="status"><p><?= e($m) ?></p></div><?php endforeach; ?>
  <?php include $view_file; ?>
</main>
<footer class="site-footer">
  <div class="container footer-inner">
    <span class="brand-logo"><img src="/assets/img/logo.png" alt="Redway Academy — Mistakes Mark the Way" width="2400" height="819" loading="lazy"></span>
    <p>Redway Academy &bull; <a href="https://redwayacademy.com">redwayacademy.com</a> &bull; Contact: <a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a> &bull; bKash / Nagad: <?= CONTACT_PHONE ?><br>
    <?= CONTACT_ADDRESS ?><br>
    <?= BRAND_SLOGAN_BN ?> &bull; Mistakes Mark the Way<br>
    NCTB English for Class 6&ndash;12 &bull; English for Today &bull; English 2nd Paper &bull; Spoken English</p>
  </div>
</footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
