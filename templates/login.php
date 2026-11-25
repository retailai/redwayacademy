<div class="auth-card">
  <h1><?= t('login_title') ?></h1>
  <p><?= t('login_sub') ?></p>
  <?php if (!empty($subdomain_role)): ?>
  <p><span class="badge red"><?= e(subdomain_role_label($subdomain_role)) ?></span></p>
  <?php endif; ?>
  <form method="post" action="/login">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label for="phone"><?= t('phone') ?></label>
    <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="01XXXXXXXXX">
    <span class="field-hint" id="phone-hint"><?= t('phone_hint') ?></span>
    <label for="password"><?= t('password') ?></label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <label for="role"><?= t('login_as') ?></label>
    <select id="role" name="role" required>
      <option value="student"<?= ($subdomain_role ?? null) === 'student' ? ' selected' : '' ?>><?= t('role_student') ?></option>
      <option value="parent"<?= ($subdomain_role ?? null) === 'parent' ? ' selected' : '' ?>><?= t('role_parent') ?></option>
      <option value="teacher"<?= ($subdomain_role ?? null) === 'teacher' ? ' selected' : '' ?>><?= t('role_teacher') ?></option>
    </select>
    <span class="field-hint"><?= t('login_as_hint') ?></span>
    <label class="remember"><input type="checkbox" name="remember" value="1" checked> <?= t('remember_me') ?></label>
    <div class="form-actions"><button class="btn" type="submit"><?= t('login_btn') ?></button></div>
  </form>
  <p><?= t('no_account') ?> <a href="/register"><?= t('nav_register') ?></a></p>
  <p><small><?= t('lock_note') ?></small></p>
</div>
