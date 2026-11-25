<h1><?= $course ? 'Edit course' : 'New course' ?></h1>
<p><a href="/admin/courses">← Courses</a></p>
<form method="post" action="<?= $course ? '/admin/courses/' . (int)$course['id'] . '/edit' : '/admin/courses/new' ?>">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Title <input name="title" required value="<?= e($course['title'] ?? '') ?>"></label>
  <label>Class level <input name="class_level" value="<?= e($course['class_level'] ?? '') ?>" placeholder="e.g. 9-10"></label>
  <label>Paper <select name="paper"><option <?= ($course['paper'] ?? '') === '1st Paper' ? 'selected' : '' ?>>1st Paper</option><option <?= ($course['paper'] ?? '') === '2nd Paper' ? 'selected' : '' ?>>2nd Paper</option><option <?= ($course['paper'] ?? '') === 'Spoken English' ? 'selected' : '' ?>>Spoken English</option></select></label>
  <label>Track <select name="track"><option value="academic"<?= ($course['track'] ?? 'academic') === 'academic' ? ' selected' : '' ?>>Academic English</option><option value="spoken"<?= ($course['track'] ?? '') === 'spoken' ? ' selected' : '' ?>>Spoken English</option></select></label>
  <label>Price (৳) <input name="price_bdt" type="number" min="0" value="<?= (int)($course['price_bdt'] ?? 0) ?>"></label>
  <label>Lessons <input name="lessons" type="number" min="0" value="<?= (int)($course['lessons'] ?? 0) ?>"></label>
  <label>Description <textarea name="description" rows="3"><?= e($course['description'] ?? '') ?></textarea></label>
  <?php if ($course): ?><label><input type="checkbox" name="active" value="1" <?= (int)($course['active'] ?? 1) ? 'checked' : '' ?> style="width:auto"> Active (shown publicly)</label><?php endif; ?>
  <button class="btn" type="submit"><?= $course ? 'Save changes' : 'Create course' ?></button>
</form>
