<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
  header('Location: /pages/dashboard.php');
  exit;
}
include __DIR__ . '/includes/header.php';
?>
<div class="min-h-screen flex flex-col app-bg">
  <?php include __DIR__ . '/includes/navbar.php'; ?>
  <main class="flex-1">
    <section class="max-w-7xl mx-auto px-6 pt-16 pb-24">
      <div class="grid md:grid-cols-2 gap-10 items-center">
        <div>
          <h1 class="text-4xl md:text-5xl font-bold leading-tight">
            Futuristic Library Management
            <span class="text-highlight">for Everyone</span>
          </h1>
          <p class="mt-4 text-gray-600 dark:text-gray-300">Manage books, students, issues, returns, and analytics in a minimal, modern interface.</p>
          <div class="mt-8 flex gap-3">
            <a href="/pages/login.php" class="btn btn-primary">Login</a>
            <a href="#features" class="btn btn-outline">Explore</a>
          </div>
        </div>
        <div class="glass card p-6 border border-gray-200 dark:border-gray-800">
          <ul class="space-y-3">
            <li>Role-based access (Admin / Student)</li>
            <li>Issue/Return with auto fines</li>
            <li>Charts, notifications, exports</li>
            <li>Dark/Light themes with smooth UI</li>
          </ul>
        </div>
      </div>
    </section>
  </main>
  <?php include __DIR__ . '/includes/footer.php'; ?>
</div>
