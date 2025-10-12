<?php
require_once __DIR__ . '/../config/db.php';

function settings_pdo(): PDO { return getDB(); }

function settings_init() {
  $pdo = settings_pdo();
  $pdo->exec('CREATE TABLE IF NOT EXISTS app_settings (
    `key` VARCHAR(100) PRIMARY KEY,
    `value` TEXT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

function get_setting(string $key, $default = null) {
  static $cache = null; settings_init();
  if ($cache === null) {
    $rows = settings_pdo()->query('SELECT `key`, `value` FROM app_settings')->fetchAll();
    $cache = [];
    foreach ($rows as $r) { $cache[$r['key']] = $r['value']; }
  }
  return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function set_setting(string $key, $value): void {
  settings_init();
  $stmt = settings_pdo()->prepare('INSERT INTO app_settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)');
  $stmt->execute([$key, (string)$value]);
}

function app_name(): string { return (string)(get_setting('app_name', APP_NAME)); }
function fine_per_day(): float { return (float)(get_setting('fine_per_day', FINE_PER_DAY)); }
function default_due_days(): int { return (int)(get_setting('default_due_days', 14)); }
function allow_reservations(): bool { return (string)get_setting('allow_reservations', '1') === '1'; }
function default_theme(): string {
  $v = strtolower((string)get_setting('default_theme', 'system'));
  return in_array($v, ['light','dark','system'], true) ? $v : 'system';
}

// Base URL helper: returns the web path prefix to the project root (handles subfolder deployments)
function base_url(): string {
  $projectRootFs = str_replace('\\','/', realpath(__DIR__ . '/..'));
  $docRootFs = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
  if (!$projectRootFs || !$docRootFs) return '';
  $path = '';
  if (strpos($projectRootFs, $docRootFs) === 0) {
    $path = substr($projectRootFs, strlen($docRootFs));
  }
  if ($path === false || $path === '') return '';
  return ($path[0] === '/') ? $path : ('/' . $path);
}
