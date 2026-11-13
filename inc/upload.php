<?php
// Simpan satu gambar yang dimuat naik. Pulangkan nama fail, atau buang Exception.
function simpan_gambar(array $f, int $maxMb): string {
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) throw new RuntimeException('Gambar terlalu besar.');
    if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) throw new RuntimeException('Gagal memuat naik gambar.');
    if ($f['size'] > $maxMb * 1024 * 1024) throw new RuntimeException("Gambar melebihi {$maxMb} MB.");

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext || !@getimagesize($f['tmp_name'])) throw new RuntimeException('Hanya gambar JPG, PNG atau WEBP dibenarkan.');

    $nama = date('Ym') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $nama;

    // Kecilkan gambar besar (contohnya dari kamera telefon) jika GD tersedia
    if (function_exists('imagecreatetruecolor') && kecilkan($f['tmp_name'], $dest, $mime, 1600)) return $nama;
    if (!move_uploaded_file($f['tmp_name'], $dest)) throw new RuntimeException('Tidak dapat menyimpan gambar. Semak kebenaran folder uploads/.');
    return $nama;
}

function kecilkan(string $src, string $dest, string $mime, int $max): bool {
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($src),
        'image/png'  => @imagecreatefrompng($src),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
        default => false,
    };
    if (!$img) return false;
    // Betulkan orientasi gambar telefon
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $o = (@exif_read_data($src) ?: [])['Orientation'] ?? 1;
        $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($rot) { $r = imagerotate($img, $rot, 0); if ($r) { imagedestroy($img); $img = $r; } }
    }
    $w = imagesx($img); $h = imagesy($img);
    $skala = min(1, $max / max($w, $h));
    $nw = (int)round($w * $skala); $nh = (int)round($h * $skala);
    $out = imagecreatetruecolor($nw, $nh);
    if ($mime !== 'image/jpeg') { imagealphablending($out, false); imagesavealpha($out, true); }
    imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
    $ok = match ($mime) {
        'image/jpeg' => imagejpeg($out, $dest, 82),
        'image/png'  => imagepng($out, $dest, 7),
        'image/webp' => imagewebp($out, $dest, 82),
    };
    imagedestroy($img); imagedestroy($out);
    return $ok;
}
