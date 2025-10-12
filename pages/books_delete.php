<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if ($id) {
  $stmt = $pdo->prepare('DELETE FROM books WHERE id=?');
  $stmt->execute([$id]);
}
header('Location: /pages/books.php');
exit;
