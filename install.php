<?php
// Pemasangan kali pertama: cipta jadual dan akaun admin pertama.
// PADAM FAIL INI selepas pemasangan berjaya.
require __DIR__ . '/inc/bootstrap.php';

$msg = ''; $ok = false;
try {
    $hasAdmin = false;
    try { $hasAdmin = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn() > 0; } catch (PDOException $ex) {}
    if ($hasAdmin) { $msg = 'Pemasangan sudah selesai. Sila padam fail install.php.'; $ok = true; }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();
        $nama = trim($_POST['nama'] ?? ''); $emel = trim($_POST['emel'] ?? ''); $pw = $_POST['kata_laluan'] ?? '';
        if ($nama === '' || !filter_var($emel, FILTER_VALIDATE_EMAIL) || strlen($pw) < 8) {
            $msg = 'Isi nama, emel yang sah dan kata laluan sekurang-kurangnya 8 aksara.';
        } else {
            db()->exec(file_get_contents(__DIR__ . '/install.sql'));
            db()->prepare('INSERT INTO admins (nama, emel, kata_laluan) VALUES (?,?,?)')
                ->execute([$nama, $emel, password_hash($pw, PASSWORD_DEFAULT)]);
            $msg = 'Berjaya! Akaun admin telah dicipta. PADAM fail install.php sekarang, kemudian log masuk di /admin.';
            $ok = true;
        }
    }
} catch (PDOException $ex) {
    $msg = 'Tidak dapat sambung ke database. Semak inc/config.php. (' . $ex->getMessage() . ')';
}
?><!doctype html>
<html lang="ms"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pemasangan</title><link rel="stylesheet" href="dist/style.css"></head>
<body class="grid min-h-screen place-items-center bg-zinc-50 p-4 font-sans text-zinc-900 antialiased">
  <div class="w-full max-w-md rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm">
    <h1 class="text-2xl font-bold tracking-tight">Pemasangan</h1>
    <p class="mt-1 text-sm text-zinc-500">Cipta jadual database dan akaun admin pertama.</p>
    <?php if ($msg): ?><p class="mt-5 rounded-xl <?= $ok ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' ?> p-3 text-sm"><?= e($msg) ?></p><?php endif; ?>
    <?php if (!$ok): ?>
    <form method="post" class="mt-6 grid gap-4">
      <?= csrf_field() ?>
      <label class="text-sm font-semibold">Nama<input name="nama" required class="mt-1 w-full rounded-xl border border-zinc-200 px-3 py-2.5 font-normal outline-none focus:border-zinc-400"></label>
      <label class="text-sm font-semibold">Emel<input name="emel" type="email" required class="mt-1 w-full rounded-xl border border-zinc-200 px-3 py-2.5 font-normal outline-none focus:border-zinc-400"></label>
      <label class="text-sm font-semibold">Kata laluan (min 8 aksara)<input name="kata_laluan" type="password" minlength="8" required class="mt-1 w-full rounded-xl border border-zinc-200 px-3 py-2.5 font-normal outline-none focus:border-zinc-400"></label>
      <button class="rounded-xl bg-zinc-900 py-3 font-semibold text-white hover:bg-zinc-700">Pasang</button>
    </form>
    <?php else: ?><a href="admin/" class="mt-6 inline-block rounded-xl bg-zinc-900 px-5 py-3 font-semibold text-white">Ke halaman admin →</a><?php endif; ?>
  </div>
</body></html>
