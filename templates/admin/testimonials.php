<h1>Admin — Reviews (Testimonials)</h1>
<p><a href="/admin">← Admin Panel</a></p>
<p>New endorsements stay hidden until approved. Only approved ones show publicly.</p>
<?php foreach ($testimonials as $t): ?><div class="card"><p>"<?= e($t['message']) ?>"</p><p><strong><?= e($t['author_name']) ?></strong> — <?= e($t['role']) ?><?php if (!empty($t['class_level'])): ?>, Class <?= e($t['class_level']) ?><?php endif; ?> — <span class="badge"><?= e($t['status']) ?></span></p>
<form class="inline" method="post" action="/admin/testimonials/<?= (int)$t['id'] ?>/status"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="approved"><button type="submit">Approve</button></form>
<form class="inline" method="post" action="/admin/testimonials/<?= (int)$t['id'] ?>/status"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="hidden"><button type="submit">Hide</button></form>
<form class="inline" method="post" action="/admin/testimonials/<?= (int)$t['id'] ?>/status"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="pending"><button type="submit">Pending</button></form>
<form class="inline" method="post" action="/admin/testimonials/<?= (int)$t['id'] ?>/delete" onsubmit="return confirm('Delete this testimonial?')"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><button type="submit">Delete</button></form>
</div><?php endforeach; ?>
<?php if (empty($testimonials)): ?><p>No testimonials yet.</p><?php endif; ?>
