<?php
declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Fail inc/config.php belum ada. Salin inc/config.sample.php sebagai inc/config.php dan isi maklumat database.');
}
$CONFIG = require $configFile;

function db(): PDO {
    static $pdo = null;
    global $CONFIG;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host={$CONFIG['db_host']};dbname={$CONFIG['db_name']};charset=utf8mb4",
            $CONFIG['db_user'], $CONFIG['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]
        );
    }
    return $pdo;
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function settings(): array {
    return db()->query('SELECT kunci, nilai FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
}

// ---------- Sesi & keselamatan ----------
function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'httponly' => true, 'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('hartanah_admin');
    session_start();
}

function csrf_token(): string {
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function check_csrf(): void {
    start_session();
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Sesi tamat. Sila kembali dan cuba lagi.');
    }
}

function current_admin(): ?array {
    start_session();
    if (empty($_SESSION['admin_id'])) return null;
    $st = db()->prepare('SELECT id, nama, emel FROM admins WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    return $st->fetch() ?: null;
}
function require_admin(): array {
    $a = current_admin();
    if (!$a) { header('Location: login.php'); exit; }
    return $a;
}

function flash(?string $msg = null): ?string {
    start_session();
    if ($msg !== null) { $_SESSION['flash'] = $msg; return null; }
    $m = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $m;
}

function redirect(string $to): never { header('Location: ' . $to); exit; }
