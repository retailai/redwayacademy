<?php
// Dashboard sidebar. Expects $user, $dash_links (array of [url, label, count?]),
// $dash_active (url of active link), $dash_role ('student'|'parent'|'teacher'),
// $unread_count, $is_super_admin.
$role_chip = $dash_role === 'teacher' ? 'teacher' : ($dash_role === 'parent' ? 'parent' : '');
?>
<aside class="dash-side">
  <div class="dash-profile">
    <span class="avatar-circle" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span>
    <div>
      <b><?= e($user['name']) ?></b>
      <span><?= t('phone_label') ?>: <?= e($user['phone']) ?><?php if (!empty($user['class_level'])): ?> • <?= t('class') ?> <?= e($user['class_level']) ?><?php endif; ?></span><br>
      <span class="role-chip <?= $role_chip ?>"><?= e(role_label($user['role'])) ?><?php if ($is_super_admin): ?> • <?= t('super_admin') ?><?php endif; ?></span>
    </div>
  </div>
  <nav class="dash-nav" aria-label="<?= e(t('nav_dashboard')) ?>">
    <?php foreach ($dash_links as $link): ?>
    <a href="<?= e($link[0]) ?>"<?= ($dash_active ?? '') === $link[0] ? ' class="active' . ($dash_role === 'teacher' ? ' teacher' : '') . '" aria-current="page"' : '' ?>><?= e($link[1]) ?><?php if (!empty($link[2])): ?><span class="count"><?= (int)$link[2] ?></span><?php endif; ?></a>
    <?php endforeach; ?>
  </nav>
</aside>
