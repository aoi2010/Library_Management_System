<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
if (isLoggedIn()) { header('Location: /pages/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) {
    $error = 'Invalid CSRF token.';
  } else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Basic validations
    if (!$name || !$email || !$password || !$password2) {
      $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Invalid email address.';
    } elseif ($password !== $password2) {
      $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
      $error = 'Password must be at least 8 characters.';
    } else {
      try {
        $pdo = getDB();
        // Ensure unique email
        $chk = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $chk->execute([$email]);
        if ($chk->fetch()) {
          $error = 'Email already registered. Try logging in.';
        } else {
          $hash = password_hash($password, PASSWORD_DEFAULT);
          $pdo->beginTransaction();
          $ins = $pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,"student")');
          $ins->execute([$name,$email,$hash]);
          $uid = (int)$pdo->lastInsertId();
          // also create a student record
          $st = $pdo->prepare('INSERT INTO students(name,email) VALUES(?,?)');
          $st->execute([$name,$email]);
          $pdo->commit();
          // auto-login
          $_SESSION['user'] = ['id'=>$uid,'name'=>$name,'email'=>$email,'role'=>'student'];
          header('Location: /pages/dashboard.php');
          exit;
        }
      } catch (Throwable $e) {
        if ($pdo && $pdo->inTransaction()) { $pdo->rollBack(); }
        $error = 'Signup failed. Please try again.';
      }
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
        <h1 class="text-2xl font-semibold">Create your account</h1>
      </div>
      <?php if ($error): ?>
        <div class="mb-3 text-sm text-red-600"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="post" class="space-y-4">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div>
          <label class="block text-sm mb-1">Full name</label>
          <input type="text" name="name" class="w-full px-3 py-2 border rounded-md bg-white/80 dark:bg-gray-900/70 border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-primary/40" required />
        </div>
        <div>
          <label class="block text-sm mb-1">Email</label>
          <input type="email" name="email" class="w-full px-3 py-2 border rounded-md bg-white/80 dark:bg-gray-900/70 border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-primary/40" required />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm mb-1">Password</label>
            <input type="password" name="password" class="w-full px-3 py-2 border rounded-md bg-white/80 dark:bg-gray-900/70 border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-primary/40" minlength="8" required />
          </div>
          <div>
            <label class="block text-sm mb-1">Confirm password</label>
            <input type="password" name="password2" class="w-full px-3 py-2 border rounded-md bg-white/80 dark:bg-gray-900/70 border-gray-300 dark:border-gray-700 focus:ring-2 focus:ring-primary/40" minlength="8" required />
          </div>
        </div>
        <button class="w-full py-2 bg-primary text-white rounded-md hover:bg-primary-dark transition">Sign up</button>
      </form>
      <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">Already have an account? <a class="text-primary hover:underline" href="/pages/login.php">Sign in</a></p>
    </div>
  </div>
</div>
