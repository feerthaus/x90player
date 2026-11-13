# Marketplace Hartanah

Laman web untuk menyenaraikan rumah, tanah dan kedai untuk **dijual** atau **disewa**, dengan **halaman admin** untuk mengurus hartanah.

## Stack
| Bahagian | Teknologi |
|---|---|
| Laman awam | HTML + Tailwind CSS + JavaScript |
| Admin & data | PHP 8.1+ (tanpa framework) |
| Pangkalan data | MySQL 5.7+ / MariaDB 10.3+ |
| Hosting | Mana-mana hosting cPanel yang ada PHP + MySQL |

## Ciri-ciri
**Laman awam:** carian dan penapis, hartanah pilihan, cari ikut kawasan, galeri gambar, peta, kalkulator ansuran, butang WhatsApp, borang hubungi, soalan lazim, pautan kongsi untuk setiap hartanah (`/#H001`).

**Admin (`/admin`):** log masuk, tambah/edit/padam hartanah, muat naik banyak gambar sekaligus (dikecilkan secara automatik), pilih gambar utama, tukar status (Tersedia/Ditempah/Terjual), tanda "pilihan", ubah nama syarikat dan nombor WhatsApp, tukar kata laluan.

---

## Pemasangan di cPanel (kali pertama)

1. **Cipta database**: cPanel → *MySQL® Databases*
   - Cipta database (contoh `akaun_hartanah`)
   - Cipta user dan kata laluan, kemudian *Add User To Database* dengan **ALL PRIVILEGES**
2. **Muat naik fail**: cPanel → *File Manager* → `public_html` → *Upload* fail zip → *Extract*
3. **Tetapan database**: dalam folder `inc/`, salin `config.sample.php` sebagai **`config.php`** dan isi nama database, user dan kata laluan.
4. **Pasang**: buka `https://domainanda.com/install.php`, isi nama, emel dan kata laluan admin, tekan **Pasang**.
5. **PADAM `install.php`** dari File Manager.
6. Log masuk di `https://domainanda.com/admin/` → **Tetapan** → tukar nama syarikat dan nombor WhatsApp → **+ Tambah** hartanah.

> Pastikan folder `uploads/` boleh ditulis (kebenaran 755). Jika gambar telefon gagal dimuat naik, naikkan `upload_max_filesize` dan `post_max_size` (contoh 16M) di cPanel → *MultiPHP INI Editor*.

## Fail penting
| Fail / folder | Fungsi |
|---|---|
| `index.html`, `app.js` | Laman awam |
| `data.php` | Membekalkan data dari database kepada laman awam |
| `admin/` | Halaman admin |
| `inc/config.php` | Maklumat database — **rahsia, jangan kongsi** |
| `uploads/` | Gambar hartanah — **sertakan dalam backup** |
| `install.sql` | Struktur database |
| `dist/style.css` | CSS yang dijana — jangan edit terus |

## Backup
Backup berkala: **database** (cPanel → *phpMyAdmin* → *Export*) dan **folder `uploads/`**.

## Untuk developer
Ubah reka bentuk (kelas Tailwind dalam `index.html`, `app.js`, `admin/*.php`) kemudian jana semula CSS:
```
npm install
npm run build
```
Ujian tempatan: `php -S localhost:8080` (perlukan MySQL dan `inc/config.php`).
