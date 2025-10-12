<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function log_action(string $action): void {
  try {
    $pdo = getDB();
    $uid = $_SESSION['user']['id'] ?? null;
    $stmt = $pdo->prepare('INSERT INTO activity_log(user_id, action) VALUES(?, ?)');
    $stmt->execute([$uid, $action]);
  } catch (Throwable $e) { /* best-effort logging, ignore errors */ }
}
