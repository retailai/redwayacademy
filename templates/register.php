<div class="auth-card">
  <h1><?= t('register_title') ?></h1>
  <p><?= t('register_sub') ?></p>
  <form method="post" action="/register">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label for="name"><?= t('full_name') ?></label>
    <input id="name" name="name" autocomplete="name" required>
    <label for="phone"><?= t('phone') ?></label>
    <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="01XXXXXXXXX (11 digits)">
    <span class="field-hint"><?= t('password_hint') ?></span>
    <label for="password"><?= t('password_4') ?></label>
    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="4">
    <?php if (!empty($subdomain_role)): ?>
      <input type="hidden" name="role" value="<?= e($subdomain_role) ?>">
      <p><span class="badge"><?= e(subdomain_role_label($subdomain_role)) ?></span></p>
    <?php else: ?>
      <label for="role"><?= t('role') ?></label>
      <select id="role" name="role">
        <option value="student"><?= t('role_student') ?></option>
        <option value="parent"><?= t('role_parent') ?></option>
        <option value="teacher"><?= t('role_teacher') ?></option>
      </select>
    <?php endif; ?>
    <label for="class_level"><?= t('class_level') ?></label>
    <input id="class_level" name="class_level" placeholder="<?= e(t('class_level_ph')) ?>">
    <label for="linked_student_phone"><?= t('linked_phone') ?></label>
    <input id="linked_student_phone" name="linked_student_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="<?= e(t('linked_phone_ph')) ?>">
    <div class="form-actions"><button class="btn" type="submit"><?= t('register_btn') ?></button></div>
  </form>
  <p><?= t('have_account') ?> <a href="/login"><?= t('nav_login') ?></a></p>
</div>
