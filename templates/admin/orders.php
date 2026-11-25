<h1>Admin — Orders (bKash / Nagad)</h1>
<p><a href="/admin">← Admin Panel</a></p>
<p>Filter: <a href="/admin/orders">All</a> | <a href="/admin/orders?status=pending">Pending</a> | <a href="/admin/orders?status=verified">Verified</a> | <a href="/admin/orders?status=rejected">Rejected</a> — showing: <strong><?= e($status) ?></strong></p>
<table class="admintable"><tr><th>ID</th><th>Course</th><th>Student</th><th>Method</th><th>TrxID</th><th>Sender number</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($orders as $o): ?><tr>
<td><?= (int)$o['id'] ?></td><td><?= e($o['course_title']) ?></td><td><?= e($o['student_name']) ?> (<?= e($o['student_phone']) ?>)</td>
<td><?= e($o['method']) ?></td><td><?= e($o['trxid']) ?></td><td><?= e($o['sender_phone'] ?? '') ?></td><td>৳<?= (int)$o['amount_bdt'] ?></td><td><?= e($o['status']) ?></td>
<td>
<form class="inline" method="post" action="/admin/orders/<?= (int)$o['id'] ?>/verify"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="verified"><button class="btn green" type="submit">Verify</button></form>
<form class="inline" method="post" action="/admin/orders/<?= (int)$o['id'] ?>/verify"><input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>"><input type="hidden" name="status" value="rejected"><button class="btn red" type="submit">Reject</button></form>
</td></tr><?php endforeach; ?></table>
<?php if (empty($orders)): ?><p>No orders in this view.</p><?php endif; ?>
