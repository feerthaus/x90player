<?php
function admin_header(string $title, ?array $admin = null): void {
    $nav = [['index.php', 'Hartanah'], ['edit.php', '+ Tambah'], ['settings.php', 'Tetapan']];
    $cur = basename($_SERVER['SCRIPT_NAME']);
    $flash = flash();
    ?><!doctype html>
<html lang="ms"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($title) ?> · Admin</title>
<link rel="stylesheet" href="../dist/style.css"></head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased">
<?php if ($admin): ?>
<header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/90 backdrop-blur">
  <div class="mx-auto flex h-14 max-w-6xl items-center gap-2 px-4">
    <a href="index.php" class="mr-3 font-bold tracking-tight">Admin</a>
    <nav class="flex flex-1 gap-1 overflow-x-auto text-sm font-semibold">
      <?php foreach ($nav as [$href, $label]): ?>
        <a href="<?= $href ?>" class="whitespace-nowrap rounded-full px-3 py-1.5 <?= $cur === $href ? 'bg-zinc-900 text-white' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <a href="../" target="_blank" class="hidden text-sm font-semibold text-zinc-500 hover:text-zinc-900 sm:block">Lihat laman ↗</a>
    <form method="post" action="logout.php"><?= csrf_field() ?><button class="rounded-full px-3 py-1.5 text-sm font-semibold text-zinc-500 hover:bg-zinc-100">Keluar</button></form>
  </div>
</header>
<?php endif; ?>
<main class="mx-auto max-w-6xl px-4 py-8">
<?php if ($flash): ?><div class="mb-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800"><?= e($flash) ?></div><?php endif; ?>
<?php
}
function admin_footer(): void { echo "</main></body></html>"; }

const INPUT = 'mt-1 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2.5 text-sm font-normal outline-none focus:border-zinc-400';
const LABEL = 'block text-sm font-semibold';
