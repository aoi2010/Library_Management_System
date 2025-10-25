<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();
$type = $_GET['type'] ?? 'csv';

if ($type === 'json') {
  header('Content-Type: application/json');
  header('Content-Disposition: attachment; filename="books.json"');
  $rows = $pdo->query('SELECT id,title,author,isbn,category,year,publisher,quantity,cover_url FROM books ORDER BY id')->fetchAll();
  echo json_encode($rows);
} else {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="books.csv"');
  $out = fopen('php://output', 'w');
    fputcsv($out, ['id','title','author','isbn','category','year','publisher','quantity','cover_url'], ',', '"', '\\');
  foreach ($pdo->query('SELECT id,title,author,isbn,category,year,publisher,quantity,cover_url FROM books ORDER BY id') as $row) {
      fputcsv($out, $row, ',', '"', '\\');
  }
}
exit;
