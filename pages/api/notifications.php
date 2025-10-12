<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');
$pdo = getDB();
$items = [];
$overdue = $pdo->query('SELECT s.name AS student, b.title, DATEDIFF(CURDATE(), ib.due_date) AS late FROM issued_books ib JOIN students s ON s.id=ib.student_id JOIN books b ON b.id=ib.book_id WHERE ib.return_date IS NULL AND ib.due_date < CURDATE() ORDER BY late DESC LIMIT 5')->fetchAll();
foreach ($overdue as $o) {
  $items[] = [ 'type' => 'overdue', 'text' => $o['student'] . ' overdue: ' . $o['title'] . ' (' . (int)$o['late'] . ' days)' ];
}
echo json_encode($items);
