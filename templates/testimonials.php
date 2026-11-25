<h1>Happy Parents &amp; Students</h1>
<p>Endorsements are published only after admin approval.</p>
<div class="grid">
<?php foreach ($testimonials as $t): ?><div class="card"><p>"<?= e($t['message']) ?>"</p><p><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, Class <?= e($t['class_level']) ?><?php endif; ?></p></div><?php endforeach; ?>
</div>
<?php if (empty($testimonials)): ?><p>No approved reviews yet — be the first!</p><?php endif; ?>
<h2>Share your experience</h2>
<form method="post" action="/testimonials">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Your name <input name="author_name" required></label>
  <label>I am a <select name="role"><option>Student</option><option>Parent</option></select></label>
  <label>Class <input name="class_level" placeholder="e.g. 9-10"></label>
  <label>Your message <textarea name="message" rows="4" required></textarea></label>
  <button class="btn" type="submit">Send for approval</button>
</form>
