<h1>Buy: <?= e($course['title']) ?></h1>
<p><strong>৳<?= (int)$course['price_bdt'] ?></strong> • <?= (int)$course['lessons'] ?> lessons • Class <?= e($course['class_level']) ?></p>
<h2>Pay with bKash or Nagad (send money)</h2>
<ol>
  <li>Send <strong>৳<?= (int)$course['price_bdt'] ?></strong> by bKash / Nagad send money to the Redway Academy number (ask your teacher for the number).</li>
  <li>Copy the Transaction ID (TrxID) from your bKash / Nagad message.</li>
  <li>Submit it below. A teacher will verify your payment and mark the order verified.</li>
</ol>
<form method="post" action="/buy/<?= (int)$course['id'] ?>">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Payment method <select name="method"><option>bKash</option><option>Nagad</option></select></label>
  <label>Transaction ID (TrxID) <input name="trxid" required placeholder="e.g. 9AB12C34DE"></label>
  <button class="btn yellow" type="submit">Submit payment (৳<?= (int)$course['price_bdt'] ?>)</button>
</form>
<p><small>This is manual send-money + TrxID verification, not an automatic payment gateway.</small></p>
