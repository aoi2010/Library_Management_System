<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');
$pdo = getDB();
$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare('SELECT id,title,author,quantity FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? ORDER BY title LIMIT 50');
$like = "%$q%";
$stmt->execute([$like,$like,$like]);
echo json_encode($stmt->fetchAll());
