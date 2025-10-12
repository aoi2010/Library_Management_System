<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function isLoggedIn(): bool {
  return !empty($_SESSION['user']);
}

function requireLogin() {
  if (!isLoggedIn()) {
    header('Location: /pages/login.php');
    exit;
  }
}

function requireRole($roles) {
  if (!isLoggedIn()) requireLogin();
  $userRole = $_SESSION['user']['role'] ?? '';
  if (is_string($roles)) $roles = [$roles];
  if (!in_array($userRole, $roles, true)) {
    http_response_code(403);
    include __DIR__ . '/unauthorized.php';
    exit;
  }
}

function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function verify_csrf($token) {
  return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token ?? '');
}

function redirect($path) {
  header('Location: ' . $path);
  exit;
}
