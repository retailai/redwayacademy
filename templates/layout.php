<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e($csrf_token ?? '') ?>">
<title><?= e($title ?? 'Redway Academy — Mistakes Mark the Way') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="topbar">
  <a class="brand" href="/">
    <span class="logo" aria-hidden="true"><?= logo_svg(44) ?></span>
    <span><strong class="r">red</strong><strong class="w">way</strong> <span class="academy">ACADEMY</span><br><small>Mistakes Mark the Way</small></span>
  </a>
  <nav>
    <a href="/">Home</a>
    <a href="/courses">Courses</a>
    <a href="/homework">Homework</a>
    <a href="/retake">Free Retake</a>
    <a href="/testimonials">Reviews</a>
    <a href="/checkin">Check-in</a>
    <?php if ($current_user): ?>
      <a href="/notifications">&#128276;<?php if ($unread_count): ?> (<?= (int)$unread_count ?>)<?php endif; ?></a>
      <a href="/dashboard">Dashboard</a>
      <?php if ($current_user['role'] === 'teacher'): ?><a href="/admin">Admin Panel</a><?php endif; ?>
      <a href="/logout">Logout (<?= e($current_user['name']) ?>)</a>
    <?php else: ?>
      <a href="/login">Login</a>
      <a class="btn" href="/register">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="container">
  <?php foreach (flashes() as $m): ?><div class="flash"><p><?= e($m) ?></p></div><?php endforeach; ?>
  <?php include $view_file; ?>
</main>
<footer><p>Redway Academy &bull; redwayacademy.com &bull; <?= BRAND_SLOGAN_BN ?> &bull; Mistakes Mark the Way</p>
<p>NCTB English for Class 6&ndash;12 &bull; English for Today &bull; English 2nd Paper &bull; Spoken English</p></footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
