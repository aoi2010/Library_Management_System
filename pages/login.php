<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $error = 'Invalid CSRF token.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email && $password) {
      $pdo = getDB();
      $stmt = $pdo->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
      $stmt->execute([$email]);
      $user = $stmt->fetch();
      if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
          'id' => $user['id'],
          'name' => $user['name'],
          'email' => $user['email'],
          'role' => $user['role']
        ];
        header('Location: /pages/dashboard.php');
        exit;
      } else {
        $error = 'Invalid credentials.';
      }
    } else {
      $error = 'Email and password required.';
    }
  }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen bg-gradient-to-br from-primary/10 via-highlight/10 to-secondary/20 dark:from-secondary/70 dark:via-secondary/60 dark:to-black flex items-center justify-center px-6 py-12">
  <div class="w-full max-w-md">
    <div class="flex justify-center mb-6">
      <img src="/assets/images/logo.png" alt="Logo" class="h-12 w-auto opacity-90" />
    </div>
    <div class="glass card border border-gray-200/60 dark:border-gray-800/80 shadow-xl p-6">
      <div class="flex items-center gap-2 mb-4">
        <span class="w-2 h-6 bg-highlight rounded-sm"></span>
        <h2 class="text-2xl font-semibold">Welcome back</h2>
      </div>
      <?php if ($error): ?>
        <div class="mb-3 text-sm text-red-600"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>" />
        <div>
          <label class="block text-sm mb-1">Email</label>
          <input type="email" name="email" class="w-full px-3 py-2 border rounded-md bg-white/80 dark:bg-gray-900/70 border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-primary/40" required />
        </div>
        <div>
          <label class="block text-sm mb-1">Password</label>
          <input type="password" name="password" class="w-full px-3 py-2 border rounded-md bg-white/80 dark:bg-gray-900/70 border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-primary/40" required />
        </div>
        <button class="w-full py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition">Sign in</button>
      </form>
      <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">New here? <a class="text-primary hover:underline" href="/pages/signup.php">Create an account</a></p>
    </div>
  </div>
</div>
