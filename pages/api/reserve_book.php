<?php
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/auth.php';
requireLogin();
header('Content-Type: application/json');

if (($_SESSION['user']['role'] ?? '') !== 'student') {
  http_response_code(403);
  echo json_encode(['error'=>'Only students can reserve']);
  exit;
}
$pdo = getDB();
$data = json_decode(file_get_contents('php://input'), true);
$book_id = (int)($data['book_id'] ?? 0);
if ($book_id>0) {
  // map logged-in user to students by email; create if missing
  $email = $_SESSION['user']['email'];
  $name = $_SESSION['user']['name'];
  $sid = $pdo->prepare('SELECT id FROM students WHERE email=? LIMIT 1');
  $sid->execute([$email]);
  $studentId = $sid->fetchColumn();
  if (!$studentId) {
    $pdo->prepare('INSERT INTO students(name,email) VALUES(?,?)')->execute([$name,$email]);
    $studentId = (int)$pdo->lastInsertId();
  }
  $pdo->prepare('INSERT INTO reservations(student_id, book_id) VALUES(?, ?)')->execute([$studentId, $book_id]);
  echo json_encode(['ok'=>true]);
} else {
  http_response_code(400);
  echo json_encode(['error'=>'Invalid book id']);
}
