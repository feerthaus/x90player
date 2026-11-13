<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';
$admin = require_admin();
$KUNCI = ['nama_syarikat' => 'Nama syarikat / jenama', 'slogan' => 'Tajuk utama (hero)', 'subslogan' => 'Ayat di bawah tajuk', 'whatsapp' => 'No. WhatsApp (format 60123456789)', 'telefon' => 'No. telefon (kosongkan untuk sembunyi butang)'];
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (($_POST['borang'] ?? '') === 'tetapan') {
        $st = db()->prepare('INSERT INTO settings (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)');
        foreach ($KUNCI as $k => $_) {
            $val = trim($_POST[$k] ?? '');
            if ($k === 'whatsapp') $val = preg_replace('/\D/', '', $val);
            $st->execute([$k, $val]);
        }
        flash('Tetapan disimpan.'); redirect('settings.php');
    }
    if (($_POST['borang'] ?? '') === 'kata_laluan') {
        $st = db()->prepare('SELECT kata_laluan FROM admins WHERE id = ?'); $st->execute([$admin['id']]);
        if (!password_verify($_POST['lama'] ?? '', $st->fetchColumn())) $err = 'Kata laluan semasa salah.';
        elseif (strlen($_POST['baru'] ?? '') < 8) $err = 'Kata laluan baru mesti sekurang-kurangnya 8 aksara.';
        else {
            db()->prepare('UPDATE admins SET kata_laluan = ? WHERE id = ?')->execute([password_hash($_POST['baru'], PASSWORD_DEFAULT), $admin['id']]);
            flash('Kata laluan ditukar.'); redirect('settings.php');
        }
    }
}
$s = settings();
admin_header('Tetapan', $admin);
?>
<h1 class="text-2xl font-bold tracking-tight">Tetapan</h1>
<div class="mt-6 grid gap-6 lg:grid-cols-2">
  <form method="post" class="grid content-start gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
    <?= csrf_field() ?><input type="hidden" name="borang" value="tetapan">
    <h2 class="font-bold">Maklumat laman</h2>
    <?php foreach ($KUNCI as $k => $l): ?>
      <label class="<?= LABEL ?>"><?= e($l) ?>
        <?php if ($k === 'subslogan'): ?><textarea name="<?= $k ?>" rows="3" class="<?= INPUT ?>"><?= e($s[$k] ?? '') ?></textarea>
        <?php else: ?><input name="<?= $k ?>" value="<?= e($s[$k] ?? '') ?>" class="<?= INPUT ?>"><?php endif; ?>
      </label>
    <?php endforeach; ?>
    <button class="rounded-xl bg-zinc-900 py-3 font-semibold text-white hover:bg-zinc-700">Simpan tetapan</button>
  </form>
  <form method="post" class="grid content-start gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
    <?= csrf_field() ?><input type="hidden" name="borang" value="kata_laluan">
    <h2 class="font-bold">Tukar kata laluan</h2>
    <?php if ($err): ?><p class="rounded-xl bg-rose-50 p-3 text-sm text-rose-800"><?= e($err) ?></p><?php endif; ?>
    <label class="<?= LABEL ?>">Kata laluan semasa<input name="lama" type="password" required class="<?= INPUT ?>"></label>
    <label class="<?= LABEL ?>">Kata laluan baru (min 8 aksara)<input name="baru" type="password" minlength="8" required class="<?= INPUT ?>"></label>
    <button class="rounded-xl border border-zinc-200 py-3 font-semibold hover:bg-zinc-50">Tukar kata laluan</button>
  </form>
</div>
<?php admin_footer();
