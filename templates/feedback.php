<h1>Feedback</h1>
<p>Tell your teacher what is working and what should improve. Feedback goes to all teachers.</p>
<form method="post" action="/feedback">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Your feedback <textarea name="message" rows="4" required></textarea></label>
  <button class="btn" type="submit">Send feedback</button>
</form>
<h2>Recent feedback</h2>
<?php foreach ($feedback as $f): ?><div class="card"><p><?= e($f['message']) ?></p><p><strong><?= e($f['author_name']) ?></strong> <small>(<?= e($f['created_at']) ?>)</small></p></div><?php endforeach; ?>
<?php if (empty($feedback)): ?><p>No feedback yet.</p><?php endif; ?>
