<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
$book = $pdo->prepare('SELECT * FROM books WHERE id=?');
$book->execute([$id]);
$book = $book->fetch();
if(!$book){ http_response_code(404); die('Book not found'); }

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) { $msg = 'Invalid CSRF token.'; }
  else {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $publisher = trim($_POST['publisher'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $stmt = $pdo->prepare('UPDATE books SET title=?,author=?,isbn=?,category=?,year=?,publisher=?,quantity=? WHERE id=?');
    $stmt->execute([$title,$author,$isbn,$category,$year?:null,$publisher,$quantity,$id]);
    header('Location: /pages/books.php');
    exit;
  }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-2xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold mb-4">Edit Book</h1>
    <?php if ($msg): ?><div class="mb-4 text-sm text-red-600"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-3">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div><label class="text-sm">Title</label><input name="title" value="<?= htmlspecialchars($book['title']) ?>" required class="w-full px-3 py-2 border rounded-md"></div>
      <div><label class="text-sm">Author</label><input name="author" value="<?= htmlspecialchars($book['author']) ?>" required class="w-full px-3 py-2 border rounded-md"></div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">ISBN</label><input name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" class="w-full px-3 py-2 border rounded-md"></div>
        <div><label class="text-sm">Category</label><input name="category" value="<?= htmlspecialchars($book['category']) ?>" class="w-full px-3 py-2 border rounded-md"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Year</label><input name="year" type="number" value="<?= htmlspecialchars($book['year']) ?>" class="w-full px-3 py-2 border rounded-md"></div>
        <div><label class="text-sm">Publisher</label><input name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>" class="w-full px-3 py-2 border rounded-md"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div><label class="text-sm">Quantity</label><input name="quantity" type="number" value="<?= htmlspecialchars($book['quantity']) ?>" class="w-full px-3 py-2 border rounded-md"></div>
        <div></div>
      </div>
      <button class="px-4 py-2 bg-primary text-white rounded-md">Update</button>
    </form>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
