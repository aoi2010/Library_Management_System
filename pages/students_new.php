<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) { die('CSRF'); }
  $stmt = $pdo->prepare('INSERT INTO students(name,email,class,phone,roll_no) VALUES(?,?,?,?,?)');
  $stmt->execute([trim($_POST['name']),trim($_POST['email']),trim($_POST['class']),trim($_POST['phone']),trim($_POST['roll_no'])]);
  header('Location: /pages/students.php');
  exit;
}
include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-2xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold mb-4">Add Student</h1>
    <form method="post" class="space-y-3">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div><label class="text-sm">Name</label><input name="name" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 placeholder-gray-400"></div>
      <div><label class="text-sm">Email</label><input name="email" type="email" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 placeholder-gray-400"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Class</label><input name="class" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 placeholder-gray-400"></div>
        <div><label class="text-sm">Phone</label><input name="phone" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 placeholder-gray-400"></div>
      </div>
      <div><label class="text-sm">Roll No</label><input name="roll_no" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 placeholder-gray-400"></div>
      <button class="px-4 py-2 bg-primary text-white rounded-md">Save</button>
    </form>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
