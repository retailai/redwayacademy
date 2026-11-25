<h1>Class Check-in</h1>
<p>Offline class arrival — teacher-confirmed PIN check-in. No facial recognition is used anywhere.</p>
<?php if ($current_user['role'] === 'teacher'): ?>
<h2>Create a class PIN</h2>
<form method="post" action="/checkin">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Class level <input name="class_level" placeholder="e.g. 9-10" required></label>
  <button class="btn" type="submit">Create 4-digit PIN</button>
</form>
<h2>Active PINs</h2>
<?php foreach ($codes as $c): ?><div class="card"><p>PIN <strong><?= e($c['pin']) ?></strong> — Class <?= e($c['class_level']) ?> <small>(<?= e($c['created_at']) ?>)</small></p></div><?php endforeach; ?>
<h2>Pending arrivals — confirm them</h2>
<?php foreach ($pending as $p): ?><div class="card"><p><strong><?= e($p['student_name']) ?></strong> — Class <?= e($p['class_level']) ?> — PIN <?= e($p['pin']) ?> <small>(<?= e($p['created_at']) ?>)</small></p>
<form class="inline" method="post" action="/checkin/<?= (int)$p['id'] ?>/confirm"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button class="btn green" type="submit">Confirm arrival</button></form></div><?php endforeach; ?>
<?php if (empty($pending)): ?><p>No pending arrivals.</p><?php endif; ?>
<?php else: ?>
<h2>Enter your class PIN</h2>
<form method="post" action="/checkin">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>4-digit PIN <input name="pin" required pattern="\d{4}" maxlength="4" placeholder="1234"></label>
  <button class="btn" type="submit">Check in</button>
</form>
<p>Your teacher will confirm your arrival, and your parent will be notified.</p>
<?php endif; ?>
<h2>My check-ins</h2>
<?php foreach ($mine as $m): ?><div class="card"><p>Class <?= e($m['class_level']) ?> — <span class="badge <?= $m['status'] === 'confirmed' ? 'green' : 'yellow' ?>"><?= e($m['status']) ?></span> <small>(<?= e($m['created_at']) ?>)</small></p></div><?php endforeach; ?>
<?php if (empty($mine)): ?><p>No check-ins yet.</p><?php endif; ?>
