<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';
require __DIR__ . '/../inc/upload.php';
$admin = require_admin();

$id = (int)($_GET['id'] ?? 0);
$p = null;
if ($id) {
    $st = db()->prepare('SELECT * FROM properties WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch() ?: redirect('index.php');
}
$JENIS = ['Rumah Teres', 'Semi-D', 'Banglo', 'Kondominium', 'Apartment', 'Townhouse', 'Tanah', 'Kedai', 'Pejabat', 'Gudang'];
$NEGERI = ['Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Perak', 'Perlis', 'Pulau Pinang', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $d = [
        'tajuk' => trim($_POST['tajuk'] ?? ''), 'tujuan' => $_POST['tujuan'] ?? '', 'jenis' => trim($_POST['jenis'] ?? ''),
        'harga' => (float)str_replace(',', '', $_POST['harga'] ?? '0'), 'lokasi' => trim($_POST['lokasi'] ?? ''),
        'negeri' => trim($_POST['negeri'] ?? ''), 'bilik' => max(0, (int)($_POST['bilik'] ?? 0)),
        'bilik_air' => max(0, (int)($_POST['bilik_air'] ?? 0)), 'keluasan' => (float)str_replace(',', '', $_POST['keluasan'] ?? '0'),
        'unit' => ($_POST['unit'] ?? '') === 'ekar' ? 'ekar' : 'kp', 'pegangan' => trim($_POST['pegangan'] ?? ''),
        'status' => $_POST['status'] ?? 'tersedia', 'pilihan' => isset($_POST['pilihan']) ? 1 : 0,
        'peta' => trim($_POST['peta'] ?? ''), 'penerangan' => trim($_POST['penerangan'] ?? ''),
        'ciri' => trim(str_replace("\r", '', $_POST['ciri'] ?? '')),
    ];
    if ($d['tajuk'] === '') $errors[] = 'Tajuk diperlukan.';
    if (!in_array($d['tujuan'], ['jual', 'sewa'], true)) $errors[] = 'Pilih Dijual atau Disewa.';
    if (!in_array($d['status'], ['tersedia', 'tempahan', 'terjual'], true)) $errors[] = 'Status tidak sah.';
    if ($d['jenis'] === '' || $d['lokasi'] === '' || $d['negeri'] === '') $errors[] = 'Jenis, lokasi dan negeri diperlukan.';
    if ($d['harga'] <= 0) $errors[] = 'Harga mesti lebih daripada 0.';

    if (!$errors) {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if ($p) {
                $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($d)));
                $pdo->prepare("UPDATE properties SET $set WHERE id = :id")->execute($d + ['id' => $p['id']]);
                $pid = (int)$p['id'];
            } else {
                $cols = implode(', ', array_keys($d)); $vals = ':' . implode(', :', array_keys($d));
                $pdo->prepare("INSERT INTO properties (ruj, $cols) VALUES (:ruj, $vals)")->execute($d + ['ruj' => 'TMP' . bin2hex(random_bytes(6))]);
                $pid = (int)$pdo->lastInsertId();
                $pdo->prepare('UPDATE properties SET ruj = ? WHERE id = ?')->execute([sprintf('H%03d', $pid), $pid]);
            }
            // Padam gambar yang ditanda
            foreach ((array)($_POST['padam_gambar'] ?? []) as $gid) {
                $st = $pdo->prepare('SELECT fail FROM property_images WHERE id = ? AND property_id = ?');
                $st->execute([(int)$gid, $pid]);
                if ($f = $st->fetchColumn()) {
                    $pdo->prepare('DELETE FROM property_images WHERE id = ?')->execute([(int)$gid]);
                    @unlink(__DIR__ . '/../uploads/' . basename($f));
                }
            }
            // Gambar utama
            if (!empty($_POST['gambar_utama'])) {
                $pdo->prepare('UPDATE property_images SET susunan = susunan + 1 WHERE property_id = ?')->execute([$pid]);
                $pdo->prepare('UPDATE property_images SET susunan = 0 WHERE id = ? AND property_id = ?')->execute([(int)$_POST['gambar_utama'], $pid]);
            }
            // Gambar baru
            $up = $_FILES['gambar'] ?? null;
            if ($up && is_array($up['name'])) {
                $next = (int)$pdo->query("SELECT COALESCE(MAX(susunan), 0) + 1 FROM property_images WHERE property_id = $pid")->fetchColumn();
                foreach ($up['name'] as $i => $n) {
                    if ($up['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
                    $fail = simpan_gambar(['name' => $n, 'tmp_name' => $up['tmp_name'][$i], 'error' => $up['error'][$i], 'size' => $up['size'][$i]], (int)$CONFIG['max_upload_mb']);
                    $pdo->prepare('INSERT INTO property_images (property_id, fail, susunan) VALUES (?,?,?)')->execute([$pid, $fail, $next++]);
                }
            }
            $pdo->commit();
            flash($p ? 'Perubahan disimpan.' : 'Hartanah baru ditambah.');
            redirect('edit.php?id=' . $pid);
        } catch (Throwable $ex) {
            $pdo->rollBack();
            $errors[] = $ex instanceof RuntimeException ? $ex->getMessage() : 'Ralat semasa menyimpan: ' . $ex->getMessage();
        }
    }
    $p = array_merge($p ?? [], $d);
}

$imgs = [];
if (!empty($p['id'])) {
    $st = db()->prepare('SELECT id, fail FROM property_images WHERE property_id = ? ORDER BY susunan, id');
    $st->execute([$p['id']]);
    $imgs = $st->fetchAll();
}
$v = fn($k, $def = '') => e($p[$k] ?? $def);
$num = fn($k) => isset($p[$k]) && (float)$p[$k] != 0 ? e(rtrim(rtrim(number_format((float)$p[$k], 2, '.', ''), '0'), '.')) : '';

admin_header($p['id'] ?? null ? 'Edit hartanah' : 'Tambah hartanah', $admin);
?>
<div class="flex items-center justify-between gap-4">
  <div>
    <a href="index.php" class="text-sm font-semibold text-zinc-500 hover:text-zinc-900">← Semua hartanah</a>
    <h1 class="mt-1 text-2xl font-bold tracking-tight"><?= !empty($p['id']) ? 'Edit: ' . $v('ruj') : 'Tambah hartanah' ?></h1>
  </div>
  <?php if (!empty($p['ruj'])): ?><a href="../#<?= $v('ruj') ?>" target="_blank" class="rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-zinc-50">Lihat ↗</a><?php endif; ?>
</div>

<?php if ($errors): ?><div class="mt-6 rounded-2xl bg-rose-50 p-4 text-sm text-rose-800"><?php foreach ($errors as $er) echo '<p>' . e($er) . '</p>'; ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">
  <?= csrf_field() ?>
  <div class="grid gap-6">
    <section class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
      <h2 class="font-bold">Maklumat asas</h2>
      <label class="<?= LABEL ?>">Tajuk<input name="tajuk" required value="<?= $v('tajuk') ?>" placeholder="Contoh: Rumah Teres 2 Tingkat, Taman Aman" class="<?= INPUT ?>"></label>
      <div class="grid gap-4 sm:grid-cols-3">
        <label class="<?= LABEL ?>">Untuk
          <select name="tujuan" class="<?= INPUT ?>"><option value="jual" <?= ($p['tujuan'] ?? '') === 'jual' ? 'selected' : '' ?>>Dijual</option><option value="sewa" <?= ($p['tujuan'] ?? '') === 'sewa' ? 'selected' : '' ?>>Disewa</option></select></label>
        <label class="<?= LABEL ?>">Jenis
          <input name="jenis" list="senarai-jenis" required value="<?= $v('jenis') ?>" class="<?= INPUT ?>">
          <datalist id="senarai-jenis"><?php foreach ($JENIS as $j) echo '<option value="' . e($j) . '">'; ?></datalist></label>
        <label class="<?= LABEL ?>">Harga (RM) <span class="font-normal text-zinc-400">— sewa: sebulan</span><input name="harga" inputmode="decimal" required value="<?= $num('harga') ?>" class="<?= INPUT ?>"></label>
      </div>
      <div class="grid gap-4 sm:grid-cols-2">
        <label class="<?= LABEL ?>">Bandar / kawasan<input name="lokasi" required value="<?= $v('lokasi') ?>" placeholder="Contoh: Kuantan" class="<?= INPUT ?>"></label>
        <label class="<?= LABEL ?>">Negeri
          <select name="negeri" required class="<?= INPUT ?>"><option value="">Pilih…</option><?php foreach ($NEGERI as $n) echo '<option ' . (($p['negeri'] ?? '') === $n ? 'selected' : '') . '>' . e($n) . '</option>'; ?></select></label>
      </div>
    </section>

    <section class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
      <h2 class="font-bold">Butiran</h2>
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <label class="<?= LABEL ?>">Bilik tidur<input name="bilik" type="number" min="0" value="<?= $v('bilik', 0) ?>" class="<?= INPUT ?>"></label>
        <label class="<?= LABEL ?>">Bilik air<input name="bilik_air" type="number" min="0" value="<?= $v('bilik_air', 0) ?>" class="<?= INPUT ?>"></label>
        <label class="<?= LABEL ?>">Keluasan<input name="keluasan" inputmode="decimal" value="<?= $num('keluasan') ?>" class="<?= INPUT ?>"></label>
        <label class="<?= LABEL ?>">Unit<select name="unit" class="<?= INPUT ?>"><option value="kp">kaki persegi</option><option value="ekar" <?= ($p['unit'] ?? '') === 'ekar' ? 'selected' : '' ?>>ekar</option></select></label>
      </div>
      <label class="<?= LABEL ?>">Pegangan<input name="pegangan" list="senarai-pegangan" value="<?= $v('pegangan') ?>" class="<?= INPUT ?>">
        <datalist id="senarai-pegangan"><option value="Freehold"><option value="Leasehold"><option value="Rizab Melayu"><option value="Geran Pertanian"></datalist></label>
      <label class="<?= LABEL ?>">Penerangan<textarea name="penerangan" rows="5" class="<?= INPUT ?>"><?= $v('penerangan') ?></textarea></label>
      <label class="<?= LABEL ?>">Ciri-ciri <span class="font-normal text-zinc-400">— satu setiap baris</span><textarea name="ciri" rows="4" placeholder="Lot tepi&#10;Auto gate&#10;Dekat sekolah" class="<?= INPUT ?>"><?= $v('ciri') ?></textarea></label>
      <label class="<?= LABEL ?>">Lokasi peta <span class="font-normal text-zinc-400">— pilihan: alamat atau koordinat (contoh 3.8077,103.3260)</span><input name="peta" value="<?= $v('peta') ?>" class="<?= INPUT ?>"></label>
    </section>

    <section class="rounded-2xl border border-zinc-200 bg-white p-5">
      <h2 class="font-bold">Gambar</h2>
      <?php if ($imgs): ?>
      <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <?php foreach ($imgs as $i => $g): ?>
        <div class="overflow-hidden rounded-xl border border-zinc-200">
          <img src="../uploads/<?= e($g['fail']) ?>" alt="" class="aspect-[4/3] w-full object-cover">
          <div class="grid gap-1 p-2 text-xs font-medium">
            <label class="flex items-center gap-1.5"><input type="radio" name="gambar_utama" value="<?= $g['id'] ?>" <?= $i === 0 ? 'checked' : '' ?>> Gambar utama</label>
            <label class="flex items-center gap-1.5 text-rose-600"><input type="checkbox" name="padam_gambar[]" value="<?= $g['id'] ?>"> Padam</label>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <label class="mt-4 block cursor-pointer rounded-xl border-2 border-dashed border-zinc-200 p-6 text-center text-sm hover:border-zinc-300">
        <span class="font-semibold">Tambah gambar</span><span class="block text-zinc-400">JPG, PNG atau WEBP · boleh pilih banyak sekaligus</span>
        <input type="file" name="gambar[]" accept="image/jpeg,image/png,image/webp" multiple class="mt-3 w-full text-xs" id="input-gambar">
      </label>
    </section>
  </div>

  <aside class="grid content-start gap-4 lg:sticky lg:top-20">
    <section class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5">
      <label class="<?= LABEL ?>">Status
        <select name="status" class="<?= INPUT ?>">
          <?php foreach (['tersedia' => 'Tersedia', 'tempahan' => 'Ditempah', 'terjual' => 'Terjual / Sudah disewa'] as $k => $l) echo "<option value=\"$k\" " . (($p['status'] ?? 'tersedia') === $k ? 'selected' : '') . ">$l</option>"; ?>
        </select></label>
      <label class="flex items-start gap-3 rounded-xl bg-zinc-50 p-3 text-sm"><input type="checkbox" name="pilihan" class="mt-0.5" <?= !empty($p['pilihan']) ? 'checked' : '' ?>>
        <span><b>Hartanah pilihan</b><span class="block text-zinc-500">Papar di bahagian atas laman (maks 3 dipaparkan).</span></span></label>
      <button class="rounded-xl bg-brand-600 py-3 font-semibold text-white hover:bg-brand-700">Simpan</button>
    </section>
  </aside>
</form>
<?php admin_footer();
