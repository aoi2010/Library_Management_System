<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
$pdo = getDB();
require_once __DIR__ . '/../includes/settings.php';

// Basic list
$q = trim($_GET['q'] ?? '');
if ($q) {
  $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? ORDER BY id DESC LIMIT 200");
  $like = "%$q%";
  $stmt->execute([$like,$like,$like]);
} else {
  $stmt = $pdo->query('SELECT * FROM books ORDER BY id DESC LIMIT 200');
}
$books = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col app-bg">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
    <div class="glass glow card border border-gray-200/60 dark:border-gray-800/80 p-5 mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Books</h1>
      <div class="flex gap-2">
        <?php $roleTop = $_SESSION['user']['role'] ?? null; ?>
        <?php if ($roleTop !== 'student'): ?>
          <a href="/pages/books_import.php" class="btn btn-outline">Import</a>
          <a href="/pages/books_export.php" class="btn btn-outline">Export</a>
          <a href="/pages/books_new.php" class="btn btn-primary">Add Book</a>
        <?php else: ?>
          <button class="btn btn-outline" disabled title="Admins/Librarians only">Import</button>
          <button class="btn btn-outline" disabled title="Admins/Librarians only">Export</button>
          <button class="btn btn-primary opacity-60 cursor-not-allowed" disabled title="Admins/Librarians only">Add Book</button>
        <?php endif; ?>
      </div>
    </div>
    <form method="get" class="mb-4">
      <input type="text" name="q" placeholder="Search by title, author, ISBN" value="<?= htmlspecialchars($q) ?>" class="w-full md:w-1/2 px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" />
    </form>

    <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
      <?php foreach ($books as $b): ?>
        <div class="card glow border border-gray-200 dark:border-gray-800 p-3">
          <div class="font-semibold leading-snug"><?= htmlspecialchars($b['title']) ?></div>
          <div class="text-sm text-gray-500">by <?= htmlspecialchars($b['author']) ?></div>
          <div class="text-xs mt-1">ISBN: <?= htmlspecialchars($b['isbn']) ?></div>
          <div class="mt-2 flex items-center justify-between">
            <span class="text-xs px-2 py-0.5 rounded-full <?= ($b['quantity']>0?'bg-green-100 text-green-700':'bg-red-100 text-red-700') ?>"><?= $b['quantity']>0?'Available':'Issued' ?></span>
            <div class="flex gap-2 text-sm">
              <?php $role = $_SESSION['user']['role'] ?? null; if ($role !== 'student'): ?>
                <a href="/pages/books_edit.php?id=<?= (int)$b['id'] ?>" class="hover:text-primary">Edit</a>
                <a href="/pages/books_delete.php?id=<?= (int)$b['id'] ?>" class="hover:text-red-600" onclick="return confirm('Delete this book?')">Delete</a>
              <?php else: ?>
                <?php if (allow_reservations()): ?>
                <button type="button" class="text-primary hover:underline" onclick="reserveBook(<?= (int)$b['id'] ?>)">Reserve</button>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
<script>
async function reserveBook(id){
  try{
    const res = await apiFetch('/pages/api/reserve_book.php',{method:'POST', body: JSON.stringify({book_id:id})});
    if(res.ok){ toast('Reservation placed'); }
    else { toast(res.error || 'Failed', 'error'); }
  }catch(e){ toast('Error', 'error'); }
}
</script>
