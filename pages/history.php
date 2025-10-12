<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
$pdo = getDB();
$email = $_SESSION['user']['email'] ?? '';
$sid = $pdo->prepare('SELECT id FROM students WHERE email=? LIMIT 1');
$sid->execute([$email]);
$sid = $sid->fetchColumn();
$records = [];
if ($sid) {
  $st = $pdo->prepare('SELECT b.title, ib.issue_date, ib.due_date, ib.return_date, ib.fine FROM issued_books ib JOIN books b ON b.id=ib.book_id WHERE ib.student_id=? ORDER BY ib.issue_date DESC');
  $st->execute([$sid]);
  $records = $st->fetchAll();
}
include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-5xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold mb-4">My Borrowing History</h1>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b border-gray-200 dark:border-gray-800">
            <th class="py-2 pr-4">Title</th>
            <th class="py-2 pr-4">Issued</th>
            <th class="py-2 pr-4">Due</th>
            <th class="py-2 pr-4">Returned</th>
            <th class="py-2 pr-4">Fine</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($records as $r): ?>
          <tr class="border-b border-gray-100 dark:border-gray-800">
            <td class="py-2 pr-4 font-medium"><?= htmlspecialchars($r['title']) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars($r['issue_date']) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars($r['due_date']) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars($r['return_date'] ?? '-') ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars($r['fine']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
