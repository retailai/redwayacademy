<h1>Edit homework</h1>
<p><a href="/admin/homework">← Homework</a></p>
<form method="post" action="/admin/homework/<?= (int)$hw['id'] ?>/edit">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Title <input name="title" required value="<?= e($hw['title']) ?>"></label>
  <label>Class level <input name="class_level" value="<?= e($hw['class_level']) ?>"></label>
  <label>Details <textarea name="details" rows="4"><?= e($hw['details']) ?></textarea></label>
  <button class="btn" type="submit">Save changes</button>
</form>
