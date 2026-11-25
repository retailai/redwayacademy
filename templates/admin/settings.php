<h1><?= t('settings_title') ?></h1>
<p><a href="/admin">← Admin Panel</a></p>
<form method="post" action="/admin/settings">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <h2><?= t('settings_fees') ?></h2>
  <p><?= t('settings_fees_d') ?></p>
  <label><?= t('fee_live') ?> <input name="fee_live" type="number" min="0" inputmode="numeric" value="<?= (int)$fee_live ?>">
    <span class="hint"><?= sprintf(t('fee_live_d'), num($live_days)) ?></span></label>
  <label><?= t('fee_recorded') ?> <input name="fee_recorded" type="number" min="0" inputmode="numeric" value="<?= (int)$fee_recorded ?>">
    <span class="hint"><?= t('fee_recorded_d') ?><?php if ((int)$fee_recorded === 0): ?> (<?= t('fee_not_set') ?>)<?php endif; ?></span></label>
  <label><?= t('live_days') ?> <input name="live_days" type="number" min="1" max="7" inputmode="numeric" value="<?= (int)$live_days ?>">
    <span class="hint"><?= t('live_days_d') ?></span></label>
  <h2><?= t('retake_campaign') ?></h2>
  <p><?= t('retake_campaign_d') ?></p>
  <label class="remember"><input type="checkbox" name="retake_campaign" value="1"<?= !empty($retake_active) ? ' checked' : '' ?>> <?= !empty($retake_active) ? t('retake_on') : t('retake_off') ?></label>
  <div class="form-actions"><button class="btn" type="submit"><?= t('save_settings') ?></button></div>
</form>
