<?php
// Membekalkan data hartanah kepada laman awam (index.html) dalam bentuk JavaScript.
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: application/javascript; charset=utf-8');
header('Cache-Control: public, max-age=60');

$s = settings();
$tetapan = [
    'namaSyarikat' => $s['nama_syarikat'] ?? '',
    'slogan'       => $s['slogan'] ?? '',
    'subslogan'    => $s['subslogan'] ?? '',
    'whatsapp'     => $s['whatsapp'] ?? '',
    'telefon'      => $s['telefon'] ?? '',
];

$imgs = [];
foreach (db()->query('SELECT property_id, fail FROM property_images ORDER BY susunan, id') as $r) {
    $imgs[$r['property_id']][] = 'uploads/' . $r['fail'];
}

$senarai = [];
foreach (db()->query('SELECT * FROM properties ORDER BY created_at DESC, id DESC') as $p) {
    $senarai[] = [
        'id' => $p['ruj'], 'tajuk' => $p['tajuk'], 'tujuan' => $p['tujuan'], 'jenis' => $p['jenis'],
        'harga' => (float)$p['harga'], 'lokasi' => $p['lokasi'], 'negeri' => $p['negeri'],
        'bilik' => (int)$p['bilik'], 'bilikAir' => (int)$p['bilik_air'],
        'keluasan' => (float)$p['keluasan'], 'unit' => $p['unit'], 'pegangan' => $p['pegangan'],
        'status' => $p['status'], 'pilihan' => (bool)$p['pilihan'], 'peta' => $p['peta'],
        'gambar' => $imgs[$p['id']] ?? [], 'penerangan' => $p['penerangan'],
        'ciri' => array_values(array_filter(array_map('trim', explode("\n", $p['ciri'])))),
    ];
}

$flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
echo 'const TETAPAN = ' . json_encode($tetapan, $flags) . ";\n";
echo 'const SENARAI = ' . json_encode($senarai, $flags) . ";\n";
