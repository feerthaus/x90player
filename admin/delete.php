<?php
require __DIR__ . '/../inc/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
check_csrf();
$id = (int)($_POST['id'] ?? 0);
$st = db()->prepare('SELECT fail FROM property_images WHERE property_id = ?');
$st->execute([$id]);
foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $f) @unlink(__DIR__ . '/../uploads/' . basename($f));
db()->prepare('DELETE FROM properties WHERE id = ?')->execute([$id]);
flash('Hartanah telah dipadam.');
redirect('index.php');
