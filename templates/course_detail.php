<h1><?= e($course['title']) ?></h1>
<p><span class="badge"><?= e($course['paper']) ?></span> Class <?= e($course['class_level']) ?></p>
<p><?= e($course['description']) ?></p>
<p><strong>৳<?= (int)$course['price_bdt'] ?></strong> • <?= (int)$course['lessons'] ?> lessons</p>
<p><a class="btn yellow" href="/buy/<?= (int)$course['id'] ?>">Buy this course (bKash / Nagad)</a> <a class="btn" href="/courses">All courses</a></p>
<h2>What you will learn</h2>
<p>NCTB-aligned lessons, homework with Red Pen Review, a Mistake Notebook (ভুলের খাতা), weekly retry tests, and a monthly Green Progress report to parents. ভুল করো, বোঝো, সঠিক করো.</p>
