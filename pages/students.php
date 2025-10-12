<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();

$q = trim($_GET['q'] ?? '');
if ($q) {
  $stmt = $pdo->prepare("SELECT * FROM students WHERE name LIKE ? OR email LIKE ? OR roll_no LIKE ? ORDER BY id DESC LIMIT 200");
  $like = "%$q%";
  $stmt->execute([$like,$like,$like]);
} else {
  $stmt = $pdo->query('SELECT * FROM students ORDER BY id DESC LIMIT 200');
}
$students = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Students</h1>
      <div>
        <a href="/pages/students_new.php" class="px-3 py-2 bg-primary text-white rounded-md hover:bg-primary-dark">Add Student</a>
      </div>
    </div>
    <form method="get" class="mb-4">
      <input type="text" name="q" placeholder="Search by name, email, roll no" value="<?= htmlspecialchars($q) ?>" class="w-full md:w-1/2 px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" />
    </form>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left border-b border-gray-200 dark:border-gray-800">
            <th class="py-2 pr-4">Name</th>
            <th class="py-2 pr-4">Email</th>
            <th class="py-2 pr-4">Class</th>
            <th class="py-2 pr-4">Phone</th>
            <th class="py-2 pr-4">Roll No</th>
            <th class="py-2 pr-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($students as $s): ?>
          <tr class="border-b border-gray-100 dark:border-gray-800">
            <td class="py-2 pr-4 font-medium"><?= htmlspecialchars((string)($s['name'] ?? '')) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars((string)($s['email'] ?? '')) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars((string)($s['class'] ?? '')) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars((string)($s['phone'] ?? '')) ?></td>
            <td class="py-2 pr-4"><?= htmlspecialchars((string)($s['roll_no'] ?? '')) ?></td>
            <td class="py-2 pr-4">
              <a class="hover:text-primary" href="/pages/students_edit.php?id=<?= (int)$s['id'] ?>">Edit</a>
              <a class="hover:text-red-600 ml-2" onclick="return confirm('Delete this student?')" href="/pages/students_delete.php?id=<?= (int)$s['id'] ?>">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
