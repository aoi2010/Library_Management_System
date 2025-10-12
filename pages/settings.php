<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin();
$pdo = getDB();
require_once __DIR__ . '/../includes/settings.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!verify_csrf($_POST['csrf'] ?? '')) { $msg = 'Invalid CSRF token.'; }
  else if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name) {
      $stmt = $pdo->prepare('UPDATE users SET name=? WHERE id=?');
      $stmt->execute([$name, $_SESSION['user']['id']]);
      $_SESSION['user']['name'] = $name;
      $msg = 'Profile updated.';
    }
  } else if (isset($_POST['change_password'])) {
    $pwd = $_POST['password'] ?? '';
    $pwd2 = $_POST['password2'] ?? '';
    if ($pwd && $pwd === $pwd2) {
      $hash = password_hash($pwd, PASSWORD_DEFAULT);
      $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([$hash, $_SESSION['user']['id']]);
      $msg = 'Password changed.';
    } else { $msg = 'Passwords do not match.'; }
  } else if (isset($_POST['update_app']) && (($_SESSION['user']['role'] ?? '')==='admin')) {
    // Admin-only settings
    $appName = trim($_POST['app_name'] ?? '');
    $fine = (float)($_POST['fine_per_day'] ?? 0);
    $days = max(1, (int)($_POST['default_due_days'] ?? 14));
    $allow = isset($_POST['allow_reservations']) ? '1' : '0';
    $theme = $_POST['default_theme'] ?? 'system';
    if ($appName !== '') set_setting('app_name', $appName);
    set_setting('fine_per_day', $fine);
    set_setting('default_due_days', $days);
    set_setting('allow_reservations', $allow);
    set_setting('default_theme', $theme);
    $msg = 'Application settings saved.';
  }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-3xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-semibold mb-4">Settings</h1>
    <?php if ($msg): ?><div class="mb-4 text-sm text-green-600"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="card p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Profile</h3>
        <form method="post" class="space-y-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="update_profile" value="1">
          <div>
            <label class="text-sm">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" required />
          </div>
          <button class="px-4 py-2 bg-primary text-white rounded-md">Save</button>
        </form>
      </div>
      <div class="card p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Change Password</h3>
        <form method="post" class="space-y-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
          <input type="hidden" name="change_password" value="1">
          <div>
            <label class="text-sm">New Password</label>
            <input type="password" name="password" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" required />
          </div>
          <div>
            <label class="text-sm">Confirm Password</label>
            <input type="password" name="password2" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" required />
          </div>
          <button class="px-4 py-2 bg-primary text-white rounded-md">Change</button>
        </form>
      </div>
    </div>
    <div class="card p-4 border border-gray-200 dark:border-gray-800 mt-6">
      <h3 class="font-semibold mb-3">Theme</h3>
      <p class="text-sm text-gray-600 dark:text-gray-300">Use the toggle in the top bar to switch Dark/Light. Preference is saved in your browser.</p>
    </div>
    <?php if (($_SESSION['user']['role'] ?? '')==='admin'): ?>
    <div class="card p-4 border border-gray-200 dark:border-gray-800 mt-6">
      <h3 class="font-semibold mb-3">Application Settings (Admin)</h3>
      <form method="post" class="grid gap-4 md:grid-cols-2">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="update_app" value="1">
        <div class="md:col-span-2">
          <label class="text-sm">App Name</label>
          <input type="text" name="app_name" value="<?= htmlspecialchars(app_name()) ?>" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" />
        </div>
        <div>
          <label class="text-sm">Fine per day</label>
          <input type="number" step="0.01" name="fine_per_day" value="<?= htmlspecialchars(fine_per_day()) ?>" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" />
        </div>
        <div>
          <label class="text-sm">Default due days</label>
          <input type="number" name="default_due_days" min="1" value="<?= htmlspecialchars(default_due_days()) ?>" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700" />
        </div>
        <div class="md:col-span-2 flex items-center gap-3">
          <input type="checkbox" id="allow_res" name="allow_reservations" <?= allow_reservations()?'checked':'' ?> />
          <label for="allow_res" class="text-sm">Allow reservations</label>
        </div>
        <div class="md:col-span-2">
          <label class="text-sm">Default theme</label>
          <select name="default_theme" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700">
            <?php $dt = default_theme(); ?>
            <option value="system" <?= $dt==='system'?'selected':'' ?>>System</option>
            <option value="light" <?= $dt==='light'?'selected':'' ?>>Light</option>
            <option value="dark" <?= $dt==='dark'?'selected':'' ?>>Dark</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <button class="px-4 py-2 bg-primary text-white rounded-md">Save Settings</button>
        </div>
      </form>
    </div>
    <?php endif; ?>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
