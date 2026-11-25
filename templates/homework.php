<h1>Homework</h1>
<?php if ($current_user['role'] === 'teacher'): ?>
<h2>Create homework</h2>
<form method="post" action="/homework">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Title <input name="title" required></label>
  <label>Class level <input name="class_level" placeholder="e.g. 9-10"></label>
  <label>Details <textarea name="details" rows="4"></textarea></label>
  <button class="btn" type="submit">Create homework</button>
</form>
<?php endif; ?>
<h2>All homework</h2>
<?php foreach ($homework as $h): ?>
<div class="card">
  <h3><?= e($h['title']) ?> <small>(Class <?= e($h['class_level']) ?>)</small></h3>
  <p><?= e($h['details']) ?></p>
  <?php if ($current_user['role'] === 'student'): ?>
  <form method="post" action="/homework/<?= (int)$h['id'] ?>/submit">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label>Your answer <textarea name="answer" rows="3" required></textarea></label>
    <label>File link (optional) <input name="file_link" placeholder="Link to your file, if any"></label>
    <button class="btn" type="submit">Submit homework</button>
  </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (empty($homework)): ?><p>No homework yet.</p><?php endif; ?>
