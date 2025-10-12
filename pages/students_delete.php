<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) {
  $pdo->prepare('DELETE FROM students WHERE id=?')->execute([$id]);
}
header('Location: /pages/students.php');
exit;
