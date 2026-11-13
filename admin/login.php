<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';
start_session();
if (current_admin()) redirect('index.php');

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $st = db()->prepare('SELECT id, kata_laluan FROM admins WHERE emel = ?');
    $st->execute([trim($_POST['emel'] ?? '')]);
    $a = $st->fetch();
    if ($a && password_verify($_POST['kata_laluan'] ?? '', $a['kata_laluan'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $a['id'];
        redirect('index.php');
    }
    sleep(1); // perlahankan cubaan meneka kata laluan
    $err = 'Emel atau kata laluan salah.';
}
admin_header('Log masuk');
?>
<div class="mx-auto mt-16 max-w-sm rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm">
  <h1 class="text-2xl font-bold tracking-tight">Log masuk admin</h1>
  <?php if ($err): ?><p class="mt-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800"><?= e($err) ?></p><?php endif; ?>
  <form method="post" class="mt-6 grid gap-4">
    <?= csrf_field() ?>
    <label class="<?= LABEL ?>">Emel<input name="emel" type="email" required autofocus class="<?= INPUT ?>" value="<?= e($_POST['emel'] ?? '') ?>"></label>
    <label class="<?= LABEL ?>">Kata laluan<input name="kata_laluan" type="password" required class="<?= INPUT ?>"></label>
    <button class="rounded-xl bg-zinc-900 py-3 font-semibold text-white hover:bg-zinc-700">Log masuk</button>
  </form>
</div>
<?php admin_footer();
