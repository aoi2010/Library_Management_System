<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();
$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) { $msg = 'Invalid CSRF token.'; }
  else if (!empty($_FILES['file']['tmp_name'])) {
    $type = $_POST['type'] ?? 'csv';
    if ($type === 'csv') {
      if (($h = fopen($_FILES['file']['tmp_name'], 'r')) !== false) {
        // skip header
        $header = fgetcsv($h);
        $ins = $pdo->prepare('INSERT INTO books(title,author,isbn,category,year,publisher,quantity) VALUES(?,?,?,?,?,?,?)');
        while(($row = fgetcsv($h)) !== false) {
          [$title,$author,$isbn,$category,$year,$publisher,$quantity] = array_pad($row,7,'');
          $ins->execute([$title,$author,$isbn,$category,(int)$year?:null,$publisher,(int)$quantity]);
        }
        fclose($h);
        $msg = 'Imported successfully.';
      }
    } else if ($type === 'json') {
      $data = json_decode(file_get_contents($_FILES['file']['tmp_name']), true);
      if (is_array($data)) {
        $ins = $pdo->prepare('INSERT INTO books(title,author,isbn,category,year,publisher,quantity) VALUES(?,?,?,?,?,?,?)');
        foreach ($data as $b) {
          $ins->execute([
            $b['title']??'', $b['author']??'', $b['isbn']??'', $b['category']??'', $b['year']??null, $b['publisher']??'', $b['quantity']??0
          ]);
        }
        $msg = 'Imported successfully.';
      }
    }
  }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold mb-4">Import Books</h1>
    <?php if ($msg): ?><div class="mb-4 text-sm text-green-600"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="space-y-3">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <div>
        <label class="text-sm">File Type</label>
        <select name="type" class="w-full px-3 py-2 border rounded-md"><option value="csv">CSV</option><option value="json">JSON</option></select>
      </div>
      <div><input type="file" name="file" required class="w-full"></div>
      <button class="px-4 py-2 bg-primary text-white rounded-md">Import</button>
    </form>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
