<nav class="crumb" aria-label="Breadcrumb"><a href="/"><?= t('crumb_home') ?></a> / <a href="/courses"><?= t('crumb_courses') ?></a> / <?= e($course['title']) ?></nav>
<h1 class="page-title"><?= sprintf(t('buy_title'), e($course['title'])) ?></h1>
<p><strong><?= tk((int)$course['price_bdt']) ?></strong> • <?= num((int)$course['lessons']) ?> <?= t('lessons') ?> • <?= t('class') ?> <?= e($course['class_level']) ?></p>
<h2><?= t('pay_title') ?></h2>
<div class="detail" style="display:block">
  <p><strong>bKash / Nagad: <a href="tel:<?= CONTACT_PHONE ?>"><?= CONTACT_PHONE ?></a></strong></p>
  <p><?= t('contact_pay_d') ?></p>
</div>
<ol>
  <li><?= sprintf(t('pay_step1'), tk((int)$course['price_bdt']), CONTACT_PHONE) ?></li>
  <li><?= t('pay_step2') ?></li>
  <li><?= t('pay_step3') ?></li>
</ol>
<form method="post" action="/buy/<?= (int)$course['id'] ?>">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label><?= t('pay_method') ?> <select name="method"><option>bKash</option><option>Nagad</option></select></label>
  <label><?= t('trxid') ?> <input name="trxid" required placeholder="<?= e(t('trxid_ph')) ?>"></label>
  <label><?= t('sender_phone') ?> <input name="sender_phone" type="tel" inputmode="numeric" required pattern="01[0-9]{9}" maxlength="11" placeholder="01XXXXXXXXX"></label>
  <button class="btn yellow" type="submit"><?= sprintf(t('submit_payment'), tk((int)$course['price_bdt'])) ?></button>
</form>
<p><small><?= sprintf(t('manual_note'), CONTACT_PHONE) ?></small></p>
