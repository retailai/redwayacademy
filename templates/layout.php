<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Redway Academy — NCTB English for Class 6–12. Mistakes Mark the Way: English for Today, English 2nd Paper and Spoken English.">
<meta name="theme-color" content="#4285F4">
<meta name="csrf-token" content="<?= e($csrf_token ?? '') ?>">
<title><?= e($title ?? 'Redway Academy — Mistakes Mark the Way') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>
<header class="topbar">
  <a class="brand" href="/">
    <span class="logo" aria-hidden="true"><?= logo_svg(44) ?></span>
    <span><strong class="r">red</strong><strong class="w">way</strong> <span class="academy">ACADEMY</span><br><small>Mistakes Mark the Way</small></span>
  </a>
  <nav class="site-nav" aria-label="Main navigation">
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
<footer class="site-footer"><p>Redway Academy &bull; <a href="https://redwayacademy.com">redwayacademy.com</a> &bull; <?= BRAND_SLOGAN_BN ?> &bull; Mistakes Mark the Way</p>
<p>NCTB English for Class 6&ndash;12 &bull; English for Today &bull; English 2nd Paper &bull; Spoken English</p></footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
