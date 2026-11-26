<div class="auth-card">
  <h1><?= t('register_title') ?></h1>
  <form method="post" action="/register" id="registerForm">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label for="name"><?= t('full_name') ?></label>
    <input id="name" name="name" autocomplete="name" required>
    <label for="phone"><?= t('phone') ?></label>
    <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="01XXXXXXXXX (11 digits)">
    <span class="field-hint"><?= t('password_hint') ?></span>
    <label for="password"><?= t('password_4') ?></label>
    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="4">
    <?php if (!empty($subdomain_role)): ?>
      <input type="hidden" name="role" id="role" value="<?= e($subdomain_role) ?>">
      <p><span class="badge"><?= e(subdomain_role_label($subdomain_role)) ?></span></p>
    <?php else: ?>
      <label for="role"><?= t('role') ?></label>
      <select id="role" name="role">
        <option value="student"><?= t('role_student') ?></option>
        <option value="parent"><?= t('role_parent') ?></option>
        <option value="teacher"><?= t('role_teacher') ?></option>
      </select>
    <?php endif; ?>
    <div id="fieldClass" hidden>
      <label for="class_level"><?= t('class') ?></label>
      <select id="class_level" name="class_level">
        <option value=""><?= t('reg_class_pick') ?></option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9-10">9-10</option>
        <option value="11-12">11-12</option>
      </select>
    </div>
    <div id="fieldStudentName" hidden>
      <label for="student_name"><?= t('reg_student_name') ?></label>
      <input id="student_name" name="student_name" autocomplete="name">
    </div>
    <div id="fieldBkash" hidden>
      <label for="bkash_payment_no"><?= t('reg_bkash_no') ?></label>
      <input id="bkash_payment_no" name="bkash_payment_no" type="tel" inputmode="numeric" autocomplete="tel" placeholder="01XXXXXXXXXXX">
      <span class="field-hint"><?= t('reg_bkash_hint') ?></span>
    </div>
    <div id="fieldTrxn" hidden>
      <label for="payment_trxn_id"><?= t('reg_trxn_id') ?></label>
      <input id="payment_trxn_id" name="payment_trxn_id" autocomplete="off" placeholder="Trxn ID">
    </div>
    <div class="form-actions"><button class="btn" type="submit"><?= t('register_btn') ?></button></div>
  </form>
  <p><?= t('have_account') ?> <a href="/login"><?= t('nav_login') ?></a></p>
</div>
