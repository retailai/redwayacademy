<h1>Progress</h1>
<p>Scores, teacher suggestions, and your Mistake Notebook progress. Monthly Green Progress reports go to parents.</p>
<?php if ($current_user['role'] === 'teacher'): ?>
<h2>Add a progress note</h2>
<form method="post" action="/progress">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Student phone <input name="student_phone" type="tel" required></label>
  <label>Student name <input name="student_name" required></label>
  <label>Score (0–100) <input name="score" type="number" min="0" max="100" required></label>
  <label>Suggestion <textarea name="suggestion" rows="3"></textarea></label>
  <button class="btn" type="submit">Add progress note</button>
</form>
<?php endif; ?>
<h2>Progress notes</h2>
<?php foreach ($progress as $p): ?><div class="card"><p><strong><?= e($p['student_name']) ?></strong> — Score: <strong><?= (int)$p['score'] ?></strong></p><p><?= e($p['suggestion']) ?></p><small><?= e($p['created_at']) ?></small></div><?php endforeach; ?>
<?php if (empty($progress)): ?><p>No progress notes yet.</p><?php endif; ?>
