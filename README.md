# Hartanah Kita — Marketplace Hartanah

Laman web ringkas untuk menyenaraikan hartanah (rumah, tanah, kedai) untuk **dijual** atau **disewa**.
Pembeli boleh cari, tapis dan terus hubungi melalui **WhatsApp**. Tiada server atau database diperlukan.

## Fail
| Fail | Fungsi |
|---|---|
| `listings.js` | **Edit fail ini sahaja**: nama syarikat, nombor WhatsApp dan senarai hartanah |
| `gambar/` | Letak gambar hartanah di sini |
| `index.html`, `style.css`, `app.js` | Reka bentuk dan logik laman |

## Cara tambah hartanah
1. Upload gambar ke folder `gambar/` (contoh `gambar/h008-1.jpg`).
2. Dalam `listings.js`, salin satu blok `{ ... }` dan tukar maklumatnya.
3. Set `gambar: ["gambar/h008-1.jpg", "gambar/h008-2.jpg"]`.
4. Bila sudah dijual atau disewa, tukar `status: "terjual"`.

## Online percuma (GitHub Pages)
Repo → **Settings → Pages** → Source: *Deploy from a branch* → pilih branch `main`, folder `/ (root)` → **Save**.
Laman akan ada di `https://<username>.github.io/<nama-repo>/`.
