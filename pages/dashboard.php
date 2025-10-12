<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDB();
$role = $_SESSION['user']['role'] ?? 'admin';

// Metrics (admin/librarian)
if ($role !== 'student') {
  $totalBooks = (int)$pdo->query('SELECT COALESCE(SUM(quantity),0) as c FROM books')->fetch()['c'];
  $totalStudents = (int)$pdo->query('SELECT COUNT(*) as c FROM students')->fetch()['c'];
  $booksIssued = (int)$pdo->query('SELECT COUNT(*) as c FROM issued_books WHERE return_date IS NULL')->fetch()['c'];
  $overdue = (int)$pdo->query('SELECT COUNT(*) as c FROM issued_books WHERE return_date IS NULL AND due_date < CURDATE()')->fetch()['c'];
}

// Build actual trends for last 7 months from issued_books
$start = (new DateTime('first day of this month'))->modify('-6 months');
$fromDate = $start->format('Y-m-01');
$labels = [];
$keys = [];
for ($i=0; $i<7; $i++) {
  $labels[] = $start->format('M'); // e.g., Jan
  $keys[] = $start->format('Y-m');
  $start->modify('+1 month');
}
$counts = array_fill_keys($keys, 0);
$stmt = $pdo->prepare("SELECT DATE_FORMAT(issue_date,'%Y-%m') ym, COUNT(*) c FROM issued_books WHERE issue_date >= ? GROUP BY ym");
$stmt->execute([$fromDate]);
foreach ($stmt as $row) {
  $ym = $row['ym'];
  if (isset($counts[$ym])) { $counts[$ym] = (int)$row['c']; }
}
$chartLabels = $labels;
$chartData = array_values($counts);

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
    <div class="glass glow card border border-gray-200/60 dark:border-gray-800/80 p-5 mb-6">
      <h1 class="text-2xl font-semibold">Dashboard</h1>
      <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Overview and quick stats</p>
    </div>
    <?php if ($role !== 'student'): ?>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
          <div class="text-sm text-gray-500">Total Books</div>
          <div class="text-2xl font-bold"><?= $totalBooks ?></div>
        </div>
        <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
          <div class="text-sm text-gray-500">Total Students</div>
          <div class="text-2xl font-bold"><?= $totalStudents ?></div>
        </div>
        <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
          <div class="text-sm text-gray-500">Books Issued</div>
          <div class="text-2xl font-bold"><?= $booksIssued ?></div>
        </div>
        <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
          <div class="text-sm text-gray-500">Overdue</div>
          <div class="text-2xl font-bold"><?= $overdue ?></div>
        </div>
      </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-6 mt-8">
      <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Book Trends</h3>
        <canvas id="chartTrends" height="120"></canvas>
      </div>
      <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
        <?php if ($role !== 'student'): ?>
          <h3 class="font-semibold mb-3">Overdue</h3>
          <ul class="space-y-2 text-sm" id="overdueList">
            <?php
            $stmt = $pdo->query('SELECT ib.id, s.name AS student, b.title, ib.due_date, DATEDIFF(CURDATE(), ib.due_date) AS late FROM issued_books ib JOIN students s ON s.id = ib.student_id JOIN books b ON b.id = ib.book_id WHERE ib.return_date IS NULL AND ib.due_date < CURDATE() ORDER BY late DESC LIMIT 10');
            foreach ($stmt as $row): ?>
              <li class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-1">
                <span><?= htmlspecialchars($row['student']) ?> — <?= htmlspecialchars($row['title']) ?></span>
                <span class="text-red-600"><?= (int)$row['late'] ?> days</span>
              </li>
            <?php endforeach; ?>
          </ul>
          <h3 class="font-semibold mt-6 mb-3">Recent Activity</h3>
          <ul class="space-y-1 text-sm">
            <?php foreach($pdo->query('SELECT action, timestamp FROM activity_log ORDER BY id DESC LIMIT 8') as $a): ?>
              <li class="flex justify-between"><span><?= htmlspecialchars($a['action']) ?></span><span class="text-gray-500 text-xs"><?= htmlspecialchars($a['timestamp']) ?></span></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <h3 class="font-semibold mb-3">My Borrowed Books</h3>
          <ul class="space-y-2 text-sm">
            <?php
            $uid = (int)$_SESSION['user']['id'];
            // Attempt mapping by email to student record; assume email in users also exists in students
            $userEmail = $_SESSION['user']['email'];
            $sid = $pdo->prepare('SELECT id FROM students WHERE email=? LIMIT 1');
            $sid->execute([$userEmail]);
            $sid = $sid->fetchColumn();
            if ($sid) {
              $issued = $pdo->prepare('SELECT b.title, ib.due_date, DATEDIFF(CURDATE(), ib.due_date) AS late FROM issued_books ib JOIN books b ON b.id=ib.book_id WHERE ib.student_id=? AND ib.return_date IS NULL');
              $issued->execute([$sid]);
              foreach ($issued as $row): ?>
                <li class="flex justify-between border-b border-gray-100 dark:border-gray-800 pb-1">
                  <span><?= htmlspecialchars($row['title']) ?></span>
                  <span class="<?= ((int)$row['late']>0?'text-red-600':'text-green-600') ?>">
                    Due: <?= htmlspecialchars($row['due_date']) ?> <?= ((int)$row['late']>0? '(' . (int)$row['late'] . ' days late)': '') ?>
                  </span>
                </li>
              <?php endforeach; }
            ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('chartTrends');
  if (ctx) {
    const chart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          label: 'Issues',
          data: <?= json_encode($chartData) ?>,
          borderColor: '#2563EB',
          backgroundColor: 'rgba(37,99,235,0.2)',
          tension: 0.3
        }]
      }, options: { responsive: true, plugins: { legend: { display: true } } }
    });
  }
});
</script>
