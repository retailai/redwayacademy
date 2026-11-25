<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <a href="/courses"><?= t('crumb_courses') ?></a> / <?= e($course['title']) ?></nav>
<h1 class="page-title"><?= sprintf(t('buy_title'), e($course['title'])) ?></h1>
<p><strong><?= e(track_label($track)) ?></strong> • <?= num((int)$course['lessons']) ?> <?= t('lessons') ?> • <?= t('class') ?> <?= e($course['class_level']) ?></p>
<h2><?= t('choose_plan') ?></h2>
<form method="post" action="/buy/<?= (int)$course['id'] ?>" id="buyForm">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <div class="plan-options" role="radiogroup" aria-label="<?= e(t('choose_plan')) ?>">
    <label class="plan-option<?= $plan === 'monthly' ? ' selected' : '' ?>">
      <input type="radio" name="plan" value="monthly" data-amount="<?= (int)$fee ?>"<?= $plan === 'monthly' ? ' checked' : '' ?>>
      <span class="plan-name"><?= sprintf(t('plan_monthly'), tk((int)$fee)) ?></span>
      <span class="plan-desc"><?= sprintf(t('plan_monthly_d'), num(live_days_per_week())) ?></span>
    </label>
    <label class="plan-option<?= $plan === 'once' ? ' selected' : '' ?>">
      <input type="radio" name="plan" value="once" data-amount="<?= (int)$course['price_bdt'] ?>"<?= $plan === 'once' ? ' checked' : '' ?>>
      <span class="plan-name"><?= sprintf(t('plan_once'), tk((int)$course['price_bdt'])) ?></span>
      <span class="plan-desc"><?= t('plan_once_d') ?></span>
    </label>
  </div>
  <h2><?= t('pay_title') ?></h2>
  <div class="detail" style="display:block">
    <p><strong>bKash / Nagad: <a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a></strong></p>
    <p><?= t('contact_pay_d') ?></p>
  </div>
  <ol>
    <li><?= sprintf(t('pay_step1'), '<span id="payAmount">' . tk((int)$amount) . '</span>', CONTACT_PHONE) ?></li>
    <li><?= t('pay_step2') ?></li>
    <li><?= t('pay_step3') ?></li>
  </ol>
  <label><?= t('pay_method') ?> <select name="method"><option>bKash</option><option>Nagad</option></select></label>
  <label><?= t('trxid') ?> <input name="trxid" required placeholder="<?= e(t('trxid_ph')) ?>"></label>
  <label><?= t('sender_phone') ?> <input name="sender_phone" type="tel" inputmode="numeric" required pattern="01[0-9]{9}" maxlength="11" placeholder="01XXXXXXXXX"></label>
  <button class="btn yellow" type="submit" id="paySubmit" data-label-format="<?= e(t('submit_payment')) ?>"><?= sprintf(t('submit_payment'), tk((int)$amount)) ?></button>
</form>
<p><small><?= sprintf(t('manual_note'), CONTACT_PHONE) ?></small></p>
