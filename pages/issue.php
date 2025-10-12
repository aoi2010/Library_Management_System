<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
require_once __DIR__ . '/../includes/activity.php';
require_once __DIR__ . '/../includes/settings.php';
$pdo = getDB();

// Helper to safely rollback if possible (handles engines without TX or invalid states)
if (!function_exists('tx_rollback_safe')) {
  function tx_rollback_safe(PDO $pdo): void {
    try {
      if ($pdo->inTransaction()) { $pdo->rollBack(); }
    } catch (Throwable $e) { /* swallow */ }
  }
}

// Handle issue & return basic flows
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $msg = 'Invalid CSRF token.';
  } else if (isset($_POST['action']) && $_POST['action']==='issue') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $book_id = (int)($_POST['book_id'] ?? 0);
    $due_date = $_POST['due_date'] ?? '';
    if ($student_id && $book_id && $due_date) {
      $committed = false;
      $doTx = false;
      try { if ($pdo->beginTransaction()) { $doTx = $pdo->inTransaction(); } } catch (Throwable $e) { $doTx = false; }
      try {
        if ($doTx) {
          // Transactional path
          $q1 = $pdo->prepare('SELECT quantity FROM books WHERE id=? FOR UPDATE');
          $q1->execute([$book_id]);
          $qty = (int)$q1->fetchColumn();
          if ($qty > 0) {
            $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id=?')->execute([$book_id]);
            $pdo->prepare('INSERT INTO issued_books(student_id, book_id, issue_date, due_date) VALUES(?,?,CURDATE(),?)')->execute([$student_id, $book_id, $due_date]);
            if ($pdo->inTransaction()) $pdo->commit();
            $committed = true;
          } else {
            tx_rollback_safe($pdo);
            $msg = 'Book not available.';
          }
        } else {
          // Non-transactional optimistic path
          $upd = $pdo->prepare('UPDATE books SET quantity = quantity - 1 WHERE id=? AND quantity > 0');
          $upd->execute([$book_id]);
          if ($upd->rowCount() === 0) {
            $msg = 'Book not available.';
          } else {
            $pdo->prepare('INSERT INTO issued_books(student_id, book_id, issue_date, due_date) VALUES(?,?,CURDATE(),?)')->execute([$student_id, $book_id, $due_date]);
            $committed = true;
          }
        }
      } catch (Exception $e) { tx_rollback_safe($pdo); $msg='Error issuing book. ' . $e->getMessage(); }
      if ($committed) {
        // Logging outside the transaction avoids rollback-after-commit errors
        log_action("Issued book #$book_id to student #$student_id due $due_date");
        $msg = 'Book issued successfully.';
      }
    }
  } else if (isset($_POST['action']) && $_POST['action']==='return') {
    $issue_id = (int)($_POST['issue_id'] ?? 0);
    if ($issue_id) {
      $committed = false;
      $doTx = false;
      try { if ($pdo->beginTransaction()) { $doTx = $pdo->inTransaction(); } } catch (Throwable $e) { $doTx = false; }
      try {
        // Read the issue row first
        $st = $pdo->prepare('SELECT book_id, due_date, return_date FROM issued_books WHERE id=? LIMIT 1');
        $st->execute([$issue_id]);
        $row = $st->fetch();
        if (!$row) { tx_rollback_safe($pdo); $msg = 'Issue not found.'; }
        else {
          $today = new DateTime();
          $due = new DateTime($row['due_date']);
          $lateDays = $today > $due ? (int)$due->diff($today)->days : 0;
          $fine = $lateDays * fine_per_day();
          if ($doTx) {
            // Transactional path
            $upd = $pdo->prepare('UPDATE issued_books SET return_date = CURDATE(), fine = ? WHERE id=? AND return_date IS NULL');
            $upd->execute([$fine, $issue_id]);
            if ($upd->rowCount() === 0) {
              tx_rollback_safe($pdo); $msg = 'Issue already returned or not found.';
            } else {
              $pdo->prepare('UPDATE books SET quantity = quantity + 1 WHERE id=?')->execute([$row['book_id']]);
              if ($pdo->inTransaction()) $pdo->commit();
              $committed = true;
            }
          } else {
            // Non-transactional path
            if (!empty($row['return_date'])) { $msg = 'Issue already returned.'; }
            else {
              $upd = $pdo->prepare('UPDATE issued_books SET return_date = CURDATE(), fine = ? WHERE id=? AND return_date IS NULL');
              $upd->execute([$fine, $issue_id]);
              if ($upd->rowCount() === 0) { $msg = 'Issue already returned or not found.'; }
              else {
                $pdo->prepare('UPDATE books SET quantity = quantity + 1 WHERE id=?')->execute([$row['book_id']]);
                $committed = true;
              }
            }
          }
        }
      } catch(Exception $e) { tx_rollback_safe($pdo); $msg = 'Error returning book. ' . $e->getMessage(); }
      if ($committed) {
        log_action("Returned issue #$issue_id with fine $fine");
        $msg = 'Book returned. Fine: ' . $fine;
      }
    }
  }
}

$students = $pdo->query('SELECT id, name FROM students ORDER BY name')->fetchAll();
$books = $pdo->query('SELECT id, title FROM books WHERE quantity > 0 ORDER BY title')->fetchAll();
$openIssues = $pdo->query('SELECT ib.id, s.name AS student, b.title, ib.issue_date, ib.due_date FROM issued_books ib JOIN students s ON s.id=ib.student_id JOIN books b ON b.id=ib.book_id WHERE ib.return_date IS NULL ORDER BY ib.issue_date DESC LIMIT 200')->fetchAll();

// Admin-only debug data
$isAdmin = (($_SESSION['user']['role'] ?? '') === 'admin');
$debugRows = [];
$engineInfo = [];
if ($isAdmin) {
  try {
    $stmt = $pdo->query('SELECT ib.id, s.name AS student, b.title, ib.issue_date, ib.due_date, ib.return_date, ib.fine FROM issued_books ib JOIN students s ON s.id=ib.student_id JOIN books b ON b.id=ib.book_id ORDER BY ib.id DESC LIMIT 10');
    $debugRows = $stmt->fetchAll();
  } catch (Throwable $e) { /* ignore */ }
  try {
    $engineIssued = $pdo->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='issued_books'")->fetchColumn();
    $engineBooks = $pdo->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='books'")->fetchColumn();
    $engineInfo = ['issued_books'=>$engineIssued ?: 'unknown', 'books'=>$engineBooks ?: 'unknown'];
  } catch (Throwable $e) { $engineInfo = ['info'=>'unavailable']; }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold mb-4">Issue / Return</h1>
    <?php if ($msg): ?><div class="mb-4 text-sm text-blue-600"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="card p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Issue a Book</h3>
        <form method="post" class="space-y-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="action" value="issue">
          <div>
            <label class="text-sm">Student</label>
            <select name="student_id" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" required>
              <option value="">Select student</option>
              <?php foreach($students as $s): ?><option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-sm">Book</label>
            <select name="book_id" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" required>
              <option value="">Select book</option>
              <?php foreach($books as $b): ?><option value="<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['title']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-sm">Due Date</label>
            <?php $prefill = (new DateTime())->modify('+' . default_due_days() . ' days')->format('Y-m-d'); ?>
            <input type="date" name="due_date" value="<?= htmlspecialchars($prefill) ?>" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" required />
          </div>
          <button class="px-4 py-2 bg-primary text-white rounded-md">Issue</button>
        </form>
      </div>
      <div class="card p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Currently Issued</h3>
        <div class="space-y-2">
          <?php foreach($openIssues as $i): ?>
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-2">
              <div>
                <div class="font-medium"><?= htmlspecialchars($i['student']) ?></div>
                <div class="text-sm text-gray-500"><?= htmlspecialchars($i['title']) ?> — Due: <?= htmlspecialchars($i['due_date']) ?></div>
              </div>
              <form method="post">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="issue_id" value="<?= (int)$i['id'] ?>">
                <button class="px-3 py-1 border rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">Return</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php if ($isAdmin): ?>
    <div class="card p-4 border border-gray-200 dark:border-gray-800 mt-8">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Debug (Admin)</h3>
        <button type="button" class="px-3 py-1 border rounded-md hover:bg-gray-50 dark:hover:bg-gray-800" onclick="const d=document.getElementById('dbg'); d.classList.toggle('hidden');">Toggle</button>
      </div>
      <div id="dbg" class="mt-4 hidden">
        <div class="text-sm text-gray-600 dark:text-gray-300 mb-3">Table engines: issued_books = <?= htmlspecialchars($engineInfo['issued_books'] ?? 'unknown') ?>, books = <?= htmlspecialchars($engineInfo['books'] ?? 'unknown') ?></div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="text-left border-b border-gray-200 dark:border-gray-800">
                <th class="py-1 pr-3">ID</th>
                <th class="py-1 pr-3">Student</th>
                <th class="py-1 pr-3">Book</th>
                <th class="py-1 pr-3">Issued</th>
                <th class="py-1 pr-3">Due</th>
                <th class="py-1 pr-3">Returned</th>
                <th class="py-1 pr-3">Fine</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($debugRows as $r): ?>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                  <td class="py-1 pr-3"><?= (int)$r['id'] ?></td>
                  <td class="py-1 pr-3"><?= htmlspecialchars((string)$r['student']) ?></td>
                  <td class="py-1 pr-3"><?= htmlspecialchars((string)$r['title']) ?></td>
                  <td class="py-1 pr-3"><?= htmlspecialchars((string)$r['issue_date']) ?></td>
                  <td class="py-1 pr-3"><?= htmlspecialchars((string)$r['due_date']) ?></td>
                  <td class="py-1 pr-3"><?= htmlspecialchars((string)($r['return_date'] ?? '')) ?></td>
                  <td class="py-1 pr-3"><?= htmlspecialchars((string)$r['fine']) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
