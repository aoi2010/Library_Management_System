<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin']);
$pdo = getDB();
$msg = '';

// Handle add librarian
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
  if (!verify_csrf($_POST['csrf'] ?? '')) { $msg='Invalid CSRF token.'; }
  else {
    $name = trim($_POST['name']??'');
    $email = trim($_POST['email']??'');
    $password = $_POST['password']??'';
    if (!$name || !$email || !$password) { $msg='All fields required.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $msg='Invalid email.'; }
    elseif (strlen($password)<8) { $msg='Password must be at least 8 characters.'; }
    else {
      $chk = $pdo->prepare('SELECT id FROM users WHERE email=?'); $chk->execute([$email]);
      if ($chk->fetch()) { $msg='Email already exists.'; }
      else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,"librarian")')->execute([$name,$email,$hash]);
        $msg='Librarian added.';
      }
    }
  }
}

// Handle role update
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='role') {
  if (verify_csrf($_POST['csrf'] ?? '')) {
    $uid=(int)($_POST['uid']??0); $role=$_POST['role']??'';
    if (in_array($role,['admin','librarian','student'],true) && $uid>0) {
      $pdo->prepare('UPDATE users SET role=? WHERE id=?')->execute([$role,$uid]);
      $msg='Role updated.';
    }
  }
}

$users = $pdo->query('SELECT id,name,email,role FROM users ORDER BY role, name')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen app-bg flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-6xl mx-auto px-6 py-8">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold">Librarians & Roles</h1>
    </div>
    <?php if ($msg): ?><div class="mb-4 text-sm text-green-600"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="card p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Add Librarian</h3>
        <form method="post" class="space-y-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="action" value="add">
          <div><label class="text-sm">Name</label><input name="name" class="w-full px-3 py-2 border rounded-md" required></div>
          <div><label class="text-sm">Email</label><input type="email" name="email" class="w-full px-3 py-2 border rounded-md" required></div>
          <div><label class="text-sm">Password</label><input type="password" name="password" minlength="8" class="w-full px-3 py-2 border rounded-md" required></div>
          <button class="btn btn-primary">Add Librarian</button>
        </form>
      </div>
      <div class="card p-4 border border-gray-200 dark:border-gray-800 overflow-x-auto">
        <h3 class="font-semibold mb-3">All Users</h3>
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left border-b border-gray-200 dark:border-gray-800"><th class="py-2 pr-4">Name</th><th class="py-2 pr-4">Email</th><th class="py-2 pr-4">Role</th><th class="py-2 pr-4">Change</th></tr>
          </thead>
          <tbody>
            <?php foreach($users as $u): ?>
            <tr class="border-b border-gray-100 dark:border-gray-800">
              <td class="py-2 pr-4 font-medium"><?= htmlspecialchars($u['name']) ?></td>
              <td class="py-2 pr-4"><?= htmlspecialchars($u['email']) ?></td>
              <td class="py-2 pr-4"><?= htmlspecialchars($u['role']) ?></td>
              <td class="py-2 pr-4">
                <form method="post" class="inline-flex items-center gap-2">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                  <input type="hidden" name="action" value="role">
                  <input type="hidden" name="uid" value="<?= (int)$u['id'] ?>">
                  <select name="role" class="px-2 py-1 border rounded-md">
                    <option value="student" <?= $u['role']==='student'?'selected':'' ?>>student</option>
                    <option value="librarian" <?= $u['role']==='librarian'?'selected':'' ?>>librarian</option>
                    <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>admin</option>
                  </select>
                  <button class="btn btn-outline">Update</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
