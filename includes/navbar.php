<?php if (session_status() === PHP_SESSION_NONE) session_start(); require_once __DIR__ . '/settings.php'; ?>
<header class="sticky top-0 z-[2147483647] pointer-events-auto backdrop-blur bg-white/70 dark:bg-secondary/70 border-b border-gray-200 dark:border-gray-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex h-14 items-center justify-between flex-nowrap">
      <a href="<?= base_url() ?>/index.php" class="flex items-center gap-2 font-semibold text-primary whitespace-nowrap shrink-0">
        <img src="<?= base_url() ?>/assets/images/logo.png" alt="Logo" class="h-7 w-auto" />
        <span class="hidden md:inline"><?= htmlspecialchars(app_name()) ?></span>
      </a>
      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-6">
  <a href="<?= base_url() ?>/pages/dashboard.php" class="hover:text-primary dark:hover:text-primary transition">Dashboard</a>
  <a href="<?= base_url() ?>/pages/books.php" class="hover:text-primary dark:hover:text-primary transition">Books</a>
        <?php $role = $_SESSION['user']['role'] ?? null; if ($role !== 'student'): ?>
          <a href="<?= base_url() ?>/pages/students.php" class="hover:text-primary dark:hover:text-primary transition">Students</a>
          <a href="<?= base_url() ?>/pages/issue.php" class="hover:text-primary dark:hover:text-primary transition">Issue</a>
          <a href="<?= base_url() ?>/pages/reports.php" class="hover:text-primary dark:hover:text-primary transition">Reports</a>
          <?php if ($role==='admin'): ?>
            <a href="<?= base_url() ?>/pages/librarians.php" class="hover:text-primary dark:hover:text-primary transition">Librarians</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= base_url() ?>/pages/history.php" class="hover:text-primary dark:hover:text-primary transition">History</a>
        <?php endif; ?>
  <a href="<?= base_url() ?>/pages/settings.php" class="hover:text-primary dark:hover:text-primary transition">Settings</a>
      </nav>
      <div class="flex items-center gap-1 md:gap-2 shrink-0">
        <!-- Mobile hamburger -->
        <button id="mobileMenuBtn" class="md:hidden p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Open menu" aria-expanded="false" aria-controls="mobileMenu" onclick="(function(btn){var m=document.getElementById('mobileMenu');if(!m)return;var hidden=m.classList.toggle('hidden');btn.setAttribute('aria-expanded',(!hidden).toString()); if(event) event.stopPropagation();})(this)">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <button id="themeToggle" class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition" title="Toggle theme">
          <svg id="iconSun" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:inline" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zM4.222 4.222a1 1 0 011.415 0l.707.707a1 1 0 01-1.414 1.414l-.708-.707a1 1 0 010-1.414zM2 10a1 1 0 011-1h1a1 1 0 110 2H3a1 1 0 01-1-1zm12-1h1a1 1 0 110 2h-1a1 1 0 110-2zm1.657-4.071a1 1 0 010 1.414l-.707.707A1 1 0 1112.536 6.95l.707-.707a1 1 0 011.414 0zM10 15a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-5.657.071a1 1 0 011.414 0l.708.707A1 1 0 114.05 17.9l-.707-.707a1 1 0 010-1.414zM10 6a4 4 0 100 8 4 4 0 000-8z"/></svg>
          <svg id="iconMoon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 dark:hidden" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707 8.001 8.001 0 1017.293 13.293z"/></svg>
        </button>
        <?php if (!empty($_SESSION['user'])): ?>
          <?php $uname = trim($_SESSION['user']['name'] ?? 'User'); $initial = strtoupper(mb_substr($uname, 0, 1)); ?>
          <div class="relative">
            <!-- Compact avatar for mobile -->
            <button data-user-menu-trigger="true" class="md:hidden h-8 w-8 rounded-full bg-primary text-white flex items-center justify-center font-semibold" title="Account">
              <?= htmlspecialchars($initial) ?>
              <span class="sr-only">Open user menu</span>
            </button>
            <!-- Full label button for desktop -->
            <button id="userMenuBtn" data-user-menu-trigger="true" class="hidden md:inline-flex px-3 py-1 rounded-md bg-primary text-white hover:bg-primary-dark transition">
              <?= htmlspecialchars($uname) ?>
            </button>
            <div id="userMenu" class="absolute right-0 top-full w-44 bg-white dark:bg-secondary border border-gray-200 dark:border-gray-700 rounded-md shadow-lg hidden">
              <a href="<?= base_url() ?>/pages/settings.php" class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Profile</a>
              <a href="<?= base_url() ?>/pages/logout.php" class="block px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?= base_url() ?>/pages/login.php" class="px-2 py-1 text-sm rounded-md border border-primary text-primary hover:bg-primary hover:text-white transition">Login</a>
          <a href="<?= base_url() ?>/pages/signup.php" class="hidden md:inline-flex px-3 py-1 rounded-md bg-primary text-white hover:bg-primary-dark transition">Sign up</a>
        <?php endif; ?>
      </div>
    </div>
    <!-- Mobile menu panel -->
    <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 dark:border-gray-800 py-2" role="navigation" aria-label="Mobile">
      <div class="flex flex-col gap-1">
        <a href="<?= base_url() ?>/pages/dashboard.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Dashboard</a>
        <a href="<?= base_url() ?>/pages/books.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Books</a>
        <?php $role = $_SESSION['user']['role'] ?? null; if ($role !== 'student'): ?>
          <a href="<?= base_url() ?>/pages/students.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Students</a>
          <a href="<?= base_url() ?>/pages/issue.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Issue</a>
          <a href="<?= base_url() ?>/pages/reports.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Reports</a>
          <?php if ($role==='admin'): ?>
            <a href="<?= base_url() ?>/pages/librarians.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Librarians</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= base_url() ?>/pages/history.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">History</a>
        <?php endif; ?>
        <a href="<?= base_url() ?>/pages/settings.php" class="px-2 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Settings</a>
      </div>
    </div>
  </div>
</header>
