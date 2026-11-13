<?php
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/admin_layout.php';
$admin = require_admin();

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$sql = 'SELECT p.*, (SELECT fail FROM property_images i WHERE i.property_id = p.id ORDER BY susunan, id LIMIT 1) AS cover FROM properties p WHERE 1';
$args = [];
if ($q !== '') { $sql .= ' AND (p.tajuk LIKE ? OR p.lokasi LIKE ? OR p.ruj LIKE ?)'; array_push($args, "%$q%", "%$q%", "%$q%"); }
if (in_array($status, ['tersedia', 'tempahan', 'terjual'], true)) { $sql .= ' AND p.status = ?'; $args[] = $status; }
$st = db()->prepare($sql . ' ORDER BY p.created_at DESC, p.id DESC');
$st->execute($args);
$rows = $st->fetchAll();
$kira = db()->query("SELECT status, COUNT(*) n FROM properties GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

$warnaStatus = ['tersedia' => 'bg-emerald-50 text-emerald-700', 'tempahan' => 'bg-amber-50 text-amber-700', 'terjual' => 'bg-zinc-100 text-zinc-500'];
admin_header('Hartanah', $admin);
?>
<div class="flex flex-wrap items-end justify-between gap-4">
  <div>
    <p class="text-sm text-zinc-500">Salam, <?= e($admin['nama']) ?></p>
    <h1 class="text-2xl font-bold tracking-tight">Hartanah</h1>
  </div>
  <a href="edit.php" class="rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">+ Tambah hartanah</a>
</div>

<div class="mt-6 grid grid-cols-3 gap-3">
  <?php foreach (['tersedia' => 'Tersedia', 'tempahan' => 'Ditempah', 'terjual' => 'Terjual / Disewa'] as $k => $l): ?>
    <a href="?status=<?= $k ?>" class="rounded-2xl border border-zinc-200 bg-white p-4 hover:border-zinc-300 <?= $status === $k ? 'ring-2 ring-zinc-900' : '' ?>">
      <p class="text-xs font-semibold text-zinc-400"><?= $l ?></p><p class="mt-1 text-2xl font-bold"><?= (int)($kira[$k] ?? 0) ?></p>
    </a>
  <?php endforeach; ?>
</div>

<form class="mt-6 flex gap-2">
  <input name="q" value="<?= e($q) ?>" placeholder="Cari tajuk, lokasi atau rujukan…" class="<?= INPUT ?> mt-0">
  <?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
  <button class="rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold hover:bg-zinc-50">Cari</button>
  <?php if ($q || $status): ?><a href="index.php" class="grid place-items-center rounded-xl px-3 text-sm font-semibold text-zinc-500 hover:text-zinc-900">Reset</a><?php endif; ?>
</form>

<div class="mt-6 divide-y divide-zinc-100 overflow-hidden rounded-2xl border border-zinc-200 bg-white">
  <?php if (!$rows): ?><p class="p-10 text-center text-sm text-zinc-500">Tiada hartanah. <a href="edit.php" class="font-semibold text-zinc-900 underline">Tambah yang pertama</a>.</p><?php endif; ?>
  <?php foreach ($rows as $p): ?>
  <div class="flex items-center gap-4 p-3 sm:p-4">
    <a href="edit.php?id=<?= $p['id'] ?>" class="h-16 w-20 shrink-0 overflow-hidden rounded-xl bg-zinc-100">
      <?php if ($p['cover']): ?><img src="../uploads/<?= e($p['cover']) ?>" alt="" class="h-full w-full object-cover"><?php endif; ?>
    </a>
    <div class="min-w-0 flex-1">
      <a href="edit.php?id=<?= $p['id'] ?>" class="line-clamp-1 font-semibold hover:underline"><?= e($p['tajuk']) ?></a>
      <p class="text-sm text-zinc-500"><?= e($p['ruj']) ?> · <?= e($p['lokasi']) ?> · RM<?= number_format((float)$p['harga']) ?><?= $p['tujuan'] === 'sewa' ? '/bln' : '' ?></p>
      <div class="mt-1 flex flex-wrap gap-1.5 text-xs font-semibold">
        <span class="rounded-full bg-zinc-100 px-2 py-0.5"><?= $p['tujuan'] === 'jual' ? 'Dijual' : 'Disewa' ?></span>
        <span class="rounded-full px-2 py-0.5 <?= $warnaStatus[$p['status']] ?>"><?= ucfirst($p['status']) ?></span>
        <?php if ($p['pilihan']): ?><span class="rounded-full bg-violet-50 px-2 py-0.5 text-violet-700">★ Pilihan</span><?php endif; ?>
      </div>
    </div>
    <div class="flex shrink-0 gap-1">
      <a href="edit.php?id=<?= $p['id'] ?>" class="rounded-lg px-3 py-2 text-sm font-semibold hover:bg-zinc-100">Edit</a>
      <form method="post" action="delete.php" onsubmit="return confirm('Padam hartanah ini? Tindakan ini tidak boleh dibatalkan.')">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>">
        <button class="rounded-lg px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Padam</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php admin_footer();
