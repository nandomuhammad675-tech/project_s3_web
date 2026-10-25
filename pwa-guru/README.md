# PWA Guru

> Pembagian kerja: **tampilan** (`views/`, `assets/css/`, ikon) dikerjakan tim Website. Folder `assets/js/` di sini hanya
> berisi **lapisan data** (pemanggil API, tanpa DOM) yang dikerjakan tim Backend agar kontraknya tidak melenceng.
> Detail endpoint: `../docs/KONTRAK_API.md`.

## Memuat skrip
Skrip biasa (bukan ES module), semuanya menempel pada `window.SIKESA`. **Muat `apiClient.js` lebih dulu**, lalu yang diperlukan halaman:

```html
<script>window.SIKESA_CONFIG = { apiBase: 'https://…/backend/public', loginUrl: 'index.php?hal=login' };</script>
<script src="assets/js/apiClient.js"></script>
<script src="assets/js/auth.js"></script>        <!-- login/logout -->
<script src="assets/js/profil.js"></script>      <!-- me(), gantiPassword() -->
<script src="assets/js/referensi.js"></script>   <!-- siswa(), mapel() -->
<script src="assets/js/absensi.js"></script> <!-- dst: nilai, jadwal, piket, disiplin, laporan -->
```
`SIKESA_CONFIG` opsional: `apiBase` (default `http://localhost/si-kesa/backend/public`), `tokenKey`, `loginUrl`.

## Yang dikerjakan lapisan ini
| Modul | Fungsi utama |
|---|---|
| `apiClient` | `request/get/post/put/del`, `unduh`, `simpanBlob`, token, `onSesiBerakhir(fn)`; semua galat menjadi `ApiError {status, message, errors}` |
| `auth` | `masuk` (menolak role selain guru), `keluar`, `adalahMasuk` |
| `profil` | `me()` (kelas `null` = belum jadi wali), `gantiPassword` (token baru menggantikan yang lama) |
| `referensi` | `siswa()`, `mapel()` |
| `absensi` | `lihat`, `simpan` (POST untuk baru + PUT untuk koreksi), `ringkasan`, `semuaHadir`, `statusKelas`, `adalahMinggu` |
| `nilai` | `buat`, `perbaiki`, `hapus` (hanya UH), `semua`, `rekapPerSiswa`, `nomorUhBerikutnya` |
| `jadwal` / `piket` | CRUD + `perHari()` untuk tabel mingguan / enam kartu hari |
| `disiplin` | `daftar`, `buat`, `ubah` — **sengaja tanpa hapus** |
| `laporan` | `preview`, `unduh`, `unduhDanSimpan` (hanya tahun ajaran aktif) |

## Perilaku untuk tampilan
- **401** pada permintaan bertoken: token dihapus lalu `onSesiBerakhir` dipanggil dan event `sikesa:sesi-berakhir` dikirim (tampilkan "Sesi berakhir, silakan masuk lagi"). Bila `loginUrl` diisi, halaman dialihkan otomatis.
- **403, 409, 422**: tampilkan `error.message` apa adanya; `error.errors` memetakan pesan per kolom.
- **status 0**: jaringan putus / respons tidak terbaca → "Terjadi gangguan, coba lagi".
- Token disimpan di `localStorage` (berlaku 8 jam di server). Jangan menaruh token di URL.
- Modul tidak menyentuh DOM, kecuali `api.simpanBlob` yang membuat tautan unduhan sementara.

## Uji (tanpa browser dan tanpa server)
```bash
cd pwa-guru
node --test tests/api.test.js
```
