<h1>Admin — Retake Registrations</h1>
<p><a href="/admin">← Admin Panel</a></p>
<p>Never auto-rejected: anything missing or unreadable is "needs review" for a teacher.</p>
<table class="admintable"><tr><th>ID</th><th>Student</th><th>Exam</th><th>Roll / Reg</th><th>Result</th><th>Files</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($retakes as $r): ?><tr>
<td><?= (int)$r['id'] ?></td><td><?= e($r['student_name']) ?> (<?= e($r['phone']) ?>)</td><td><?= e($r['exam']) ?> <?= e($r['exam_year']) ?></td>
<td><?= e($r['roll_no']) ?> / <?= e($r['reg_no']) ?></td><td><?= e($r['result_text']) ?><br><small><?= e($r['reasons']) ?></small></td>
<td><?php if (!empty($r['admit_file'])): ?><a href="/uploads/<?= e($r['admit_file']) ?>">Admit</a><?php endif; ?> <?php if (!empty($r['tabulation_file'])): ?><a href="/uploads/<?= e($r['tabulation_file']) ?>">Tabulation</a><?php endif; ?></td>
<td><?= e($r['status']) ?></td>
<td>
<form class="inline" method="post" action="/admin/retakes/<?= (int)$r['id'] ?>/review"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="auto_verified"><button type="submit">Approve</button></form>
<form class="inline" method="post" action="/admin/retakes/<?= (int)$r['id'] ?>/review"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="needs_review"><button type="submit">Needs review</button></form>
<form class="inline" method="post" action="/admin/retakes/<?= (int)$r['id'] ?>/review"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="rejected"><button type="submit">Reject</button></form>
</td></tr><?php endforeach; ?></table>
<?php if (empty($retakes)): ?><p>No retake registrations yet.</p><?php endif; ?>
