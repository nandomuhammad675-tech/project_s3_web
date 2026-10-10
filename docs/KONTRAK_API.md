# Kontrak API SI-KESA (backend v1, langkah 1–6)

Dokumen ini menjelaskan **apa yang sebenarnya dilakukan backend** (bukan rencana). Untuk tim Website, Mobile, dan tester.
Rujukan resmi bila ada beda: URS/BRD/SRS v2.7, TDD v1.7, `si_kesa_v9.sql`. Penjelasan layar demi layar ada di Studi Kasus 01 (Website) dan 02 (Mobile).

---

## 0. Konvensi umum

| Hal | Aturan |
|---|---|
| Base URL | `{host}/si-kesa/backend/public` pada XAMPP lokal. Semua path di bawah diawali `/api/...`. |
| Format | JSON UTF-8. Kirim `Content-Type: application/json` pada POST/PUT. |
| Respons sukses | `{"success": true, "message": "...", "data": ...}` |
| Respons gagal | `{"success": false, "message": "...", "errors": {kolom: pesan}}` (`errors` hanya ada bila ada rincian per kolom) |
| Autentikasi Admin/Guru | Header `Authorization: Bearer <token>`. Token berlaku 8 jam. |
| Autentikasi Wali | Sama, tetapi token dari `/api/wali/login`; berlaku 60 menit. |
| Tanggal | Kirim dan terima `YYYY-MM-DD`. Jam `HH:MM`. Waktu server: `Asia/Jakarta`. |
| Paginasi | `?page=1&per_page=50` (maks 200 kecuali disebut lain). Respons: `data: {items, page, per_page, total}`. |
| Boolean | `true`/`false`; angka `1`/`0` diterima. |
| ID | Selalu angka bulat. |

### Kode status

| Kode | Arti | Yang harus dilakukan klien |
|---|---|---|
| 200 / 201 | Berhasil / berhasil dibuat | — |
| 400 | Format parameter salah (mis. `?date=abc`) | Perbaiki parameter |
| 401 | Token tidak ada/rusak/habis; akun nonaktif; password sudah diganti; (Wali) siswa tidak lagi aktif | Hapus token lokal, ke layar login |
| 403 | Role/scope tidak cocok (mis. Guru tanpa kelas, Guru meminta laporan tahun lama) | Tampilkan "tidak ada akses" |
| 404 | Data tidak ada atau bukan milik kelas Guru | Tampilkan "tidak ditemukan" |
| 405 | Metode tidak tersedia (mis. DELETE disiplin, POST pada rute baca-saja) | Bug klien |
| 409 | Konflik: duplikat, atau hapus data yang masih terkait | Tampilkan `message` apa adanya |
| 422 | Validasi bisnis gagal | Tampilkan `message`; `errors` untuk menandai kolom |
| 429 | Terlalu banyak percobaan (login) | Minta pengguna menunggu |
| 500 | Kesalahan server | "Terjadi gangguan, coba lagi" |

> Klien **tidak boleh** mengirim `id_kelas`, `id_tahun_ajaran`, atau `id_guru` pada endpoint Guru; server menentukan sendiri dari akun.

---

## 1. Autentikasi (Admin & Guru)

### `POST /api/auth/login`
Body: `{"username": "bu.rina", "password": "…"}`
Respons 200:
```json
{"success": true, "message": "Login berhasil.",
 "data": {"token": "eyJ…", "token_type": "Bearer", "expires_in": 28800,
          "user": {"id_user": 7, "username": "bu.rina", "role": "guru"}}}
```
Gagal: 401 dengan pesan yang sama untuk username salah, password salah, maupun akun nonaktif. 429 bila terlalu banyak percobaan.
**Klien PWA Guru wajib menolak `role != "guru"`** (arahkan ke PWA Admin); klien PWA Admin menolak `role != "admin"`.

### `POST /api/auth/logout` (token)
Hanya membalas sukses. Token **tidak dicabut di server**; klien cukup menghapus token lokal.

### `PUT /api/auth/password` (token)
Body: `{"password_lama": "…", "password_baru": "…"}` (baru minimal 8 karakter).
200: `data` berisi token baru (bentuk sama seperti login). **Simpan menggantikan token lama**; token lama langsung tidak berlaku. Password lama salah: 422. Dibatasi percobaan (429).

### `GET /api/auth/me` (token)
Identitas akun dari token. Dipakai klien untuk memeriksa sesi masih valid.

---

## 2. Publik (tanpa token, hanya GET)

Hanya field daftar-putih. Email/telepon Guru dan kontak wali **tidak pernah** ada di sini.

| Endpoint | Isi `data` |
|---|---|
| `GET /api/public/guru` | `[{nama_guru, nip, jabatan, foto_profil}]` (hanya akun aktif) |
| `GET /api/public/program-pembiasaan` | `[{hari, nama_program, deskripsi, ikon}]` urut Senin–Sabtu |
| `GET /api/public/ekstrakurikuler` | `[{id_ekskul, nama_ekskul, deskripsi, ikon}]` |
| `GET /api/public/event?kategori=&page=&per_page=` | paginasi (default 20, maks 100) `items: [{id_event, nama_event, kategori, tanggal, deskripsi, banner, galeri: [{id_galeri, file_gambar, keterangan}]}]`. Hanya event `status_tampil = 1`. `kategori`: `kegiatan`, `event`, `prestasi`, `kolaborasi` (lain: 400). |
| `GET /api/public/galeri?page=` | paginasi; galeri **mandiri** saja `[{id_galeri, file_gambar, keterangan}]` (foto event ikut di `/event`) |
| `GET /api/public/mading?page=` | paginasi; `[{id_mading, judul_karya, kategori, deskripsi, nama_penulis, file_gambar}]`, hanya `status_tampil = 1` |
| `GET /api/public/pengaturan` | `{ppdb_form_url, video_profil_url}` (nilai bisa `null`) |

Kolom gambar berisi path relatif (`uploads/<acak>.jpg`) atau URL https. Path relatif dibuka di `{base URL}/{path}`.

---

## 3. Wali Murid (read-only, satu siswa)

### `POST /api/wali/login`
Body: `{"nisn": "0000000001"}`. 200: `data: {token, token_type, expires_in: 3600, siswa: {nama_siswa}}`.
Semua kegagalan (NISN tak ada, siswa baru/lulus/pindah, tanpa kelas di tahun aktif) = **401 dengan pesan yang sama**. 429 bila terlalu banyak percobaan.
Token Wali **tidak berlaku** di endpoint Admin/Guru dan sebaliknya.

Pada **setiap** permintaan Wali server memeriksa ulang bahwa siswa masih aktif dan punya kelas di tahun aktif; bila tidak: 401.

| Endpoint | Isi `data` |
|---|---|
| `GET /api/wali/siswa` | `{nama_siswa, nisn, nama_kelas, tingkat_kelas, nomor_absen, tahun_ajaran, semester}` |
| `GET /api/wali/nilai?semester=ganjil\|genap` | `{tahun_ajaran, semester_aktif, items: [{id_mapel, nama_mapel, semester, uh: [{nomor_uh, nilai}], asts, asas}]}`; `asts`/`asas` bisa `null` |
| `GET /api/wali/absensi?semester=&bulan=YYYY-MM` | `{tahun_ajaran, semester_aktif, rekap: {hadir, sakit, izin, alpa, total}, items: [{tanggal, status, semester}]}` |

Filter yang salah format: 400. Parameter `id_siswa` dari klien **diabaikan**.

---

## 4. Guru (token role guru; kelas ditentukan server)

Semua endpoint di bagian ini membutuhkan Guru yang menjadi wali kelas pada tahun ajaran aktif; bila tidak: **403** — kecuali `GET /api/guru/me` yang tetap 200.

### `GET /api/guru/me`
```json
{"guru": {"id_guru": 3, "nama_guru": "Rina", "username": "bu.rina"},
 "tahun_ajaran": {"id_tahun_ajaran": 1, "nama_tahun": "2026/2027", "semester": "ganjil"},
 "kelas": {"id_kelas": 5, "nama_kelas": "3A", "tingkat_kelas": 3}}
```
`kelas` = `null` bila belum menjadi wali (tampilkan pesan "belum ditetapkan sebagai wali kelas", nonaktifkan menu lain). `tahun_ajaran` = `null` bila belum ada tahun aktif. `tahun_ajaran.id_tahun_ajaran` dipakai sebagai parameter laporan.

### `GET /api/guru/siswa`
`{kelas, total, items: [{id_siswa, nama_siswa, nisn_resmi, nomor_absen}]}` — hanya siswa **aktif** kelas sendiri, urut nomor absen. Dipakai untuk absensi, nilai, disiplin, piket.

### `GET /api/guru/mapel`
Daftar mata pelajaran (read-only; master milik Admin): paginasi default 200. `items: [{id_mapel, kode_mapel, nama_mapel}]`. Untuk pilihan di form Jadwal dan Nilai.

### Absensi
| Endpoint | Keterangan |
|---|---|
| `GET /api/guru/absensi?date=YYYY-MM-DD` | `{tanggal, status_kelas, items: [{id_siswa, nama_siswa, nomor_absen, id_absensi, status}]}`. `status_kelas`: `sudah`, `belum`, `libur` (Minggu atau libur kelas). `id_absensi` dan `status` = `null` bila belum diisi. `date` kosong = hari ini. |
| `POST /api/guru/absensi` | Body `{"tanggal": "2026-10-05", "items": [{"id_siswa": 101, "status": "hadir"}]}` (maks 100 item). Status: `hadir`, `sakit`, `izin`, `alpa`. 201: `{tanggal, jumlah, status_kelas: "sudah"}`. |
| `PUT /api/guru/absensi/{id_absensi}` | Body `{"status": "sakit"}`. Koreksi **satu** catatan. |
| `GET /api/guru/dasbor/absensi?date=` | `{tanggal, id_kelas, nama_kelas, status}` |

Aturan `POST`: seluruh batch ditolak (tidak ada yang tersimpan) bila: tanggal hari Minggu atau hari libur kelas → **422**; ada siswa yang bukan siswa aktif kelas → **422**; **ada siswa yang sudah punya absensi pada tanggal itu → 409** (`errors.id_siswa` berisi daftar id). Sabtu adalah hari efektif.
Tidak ada DELETE absensi (405).

> **Menyimpan ulang tanggal yang sama.** Server tidak menimpa lewat POST. Alur klien: `GET` tanggal itu → siswa tanpa `id_absensi` dikirim lewat **satu** `POST`; siswa yang sudah punya `id_absensi` dan statusnya berubah dikirim lewat `PUT` masing-masing. Modul `pwa-guru/assets/js/absensi.js` (`simpan`) sudah melakukan ini, sehingga bagi pengguna menyimpan dua kali tetap berhasil.

### Nilai
| Endpoint | Keterangan |
|---|---|
| `GET /api/guru/nilai?id_mapel=&semester=&id_siswa=&page=` | paginasi (default 100). `items: [{id_nilai, id_siswa, nama_siswa, id_mapel, nama_mapel, semester, jenis, nomor_uh, nilai}]` |
| `POST /api/guru/nilai` | `{"id_siswa", "id_mapel", "semester": "ganjil", "jenis": "UH", "nomor_uh": 2, "nilai": 87}`. `nomor_uh` wajib untuk UH dan **tidak boleh dikirim** untuk ASTS/ASAS (422). Nilai bulat 0–100. Duplikat (siswa+mapel+semester+jenis+nomor) → 409. Siswa non-aktif → 422. |
| `GET /api/guru/nilai/{id}` | satu nilai |
| `PUT /api/guru/nilai/{id}` | Body `{"nilai": 90}` — hanya angkanya yang bisa diperbaiki |
| `DELETE /api/guru/nilai/{id}` | Hanya baris **UH**; ASTS/ASAS → 422 |

### Jadwal pelajaran
`GET /api/guru/jadwal?semester=&hari=` (paginasi) · `POST` · `GET /{id}` · `PUT /{id}` · `DELETE /{id}`
Item: `{id_jadwal, id_kelas, id_tahun_ajaran, semester, id_mapel, nama_mapel, hari, jam_mulai, jam_selesai, keterangan}`.
Body POST: `{"semester": "ganjil", "id_mapel": 3, "hari": "senin", "jam_mulai": "07:00", "jam_selesai": "07:35", "keterangan": null}`. `hari`: `senin`…`sabtu`. `jam_selesai` harus > `jam_mulai` (422). Slot kembar (kelas+semester+hari+jam mulai) → 409. Jadwal tidak disalin ke tahun ajaran baru.

### Piket kebersihan (per hari, bukan per tanggal)
`GET /api/guru/piket` → `{items: [{id_piket, id_kelas, id_tahun_ajaran, hari, keterangan, siswa: [{id_siswa, nama_siswa}]}], total}` · `POST` · `GET /{id}` · `PUT /{id}` · `DELETE /{id}`
Body POST/PUT: `{"hari": "senin", "keterangan": "Piket kebersihan", "id_siswa": [101, 102]}`. Satu piket per hari per kelas (409 bila duplikat). Siswa harus siswa aktif kelas sendiri (422). Hapus piket ikut menghapus daftar siswanya.

### Kedisiplinan (tanpa DELETE)
`GET /api/guru/disiplin?id_siswa=` (paginasi default 100) · `POST` · `GET /{id}` · `PUT /{id}`
Item: `{id_catatan, id_siswa, nama_siswa, tanggal, jenis_pelanggaran, catatan}`. Body: `{"id_siswa", "tanggal", "jenis_pelanggaran" (≤100), "catatan" (≤2000)}`. `DELETE` → **405**. Tidak ada kolom tingkat/tindak lanjut.

### Laporan kelas sendiri
| Endpoint | Keterangan |
|---|---|
| `GET /api/guru/laporan?semester=ganjil&tahun_ajaran=1` | Preview JSON (lihat bagian 6) |
| `GET /api/guru/laporan/excel?semester=&tahun_ajaran=&jenis=absensi\|nilai\|semua` | Unduh `.xlsx` |

`semester` dan `tahun_ajaran` (id) wajib (400). Tahun ajaran selain yang aktif → **403**. Parameter `kelas` diabaikan untuk Guru.

---

## 5. Admin (token role admin)

Admin **tidak punya** akses ke jadwal/piket (403 dari middleware Guru) dan hanya **membaca** data akademik.

### Akun
| Endpoint | Keterangan |
|---|---|
| `GET /api/admin/akun` | daftar akun internal |
| `POST /api/admin/akun` | `{username, password, role: "admin"}`. `role: "guru"` → 422 (akun Guru dibuat bersama data Guru) |
| `PUT /api/admin/akun/{id}/status` | `{"status_akun": "aktif"\|"nonaktif"}`. 422 bila akun sendiri atau Admin aktif terakhir. Menonaktifkan Guru yang masih wali **tidak ditolak** (baru terdeteksi saat kenaikan kelas). |
| `PUT /api/admin/akun/{id}/password` | `{"password_baru"}`. 422 bila `{id}` = akun sendiri. Token lama akun itu langsung ditolak. |
| `DELETE` akun | tidak ada (405) |

### Data master (CRUD: `GET list`, `POST`, `GET /{id}`, `PUT /{id}`, `DELETE /{id}`; paginasi default 50)
| Sumber daya | Field utama |
|---|---|
| `/api/admin/guru` (`?q=`) | `nama_guru`*, `nip`, `jabatan`, `foto_profil`, `email`, `no_telepon`; saat `POST` juga `username`* dan `password`* (akun Guru dibuat bersamaan). Mengubah username/password/status akun lewat `/api/admin/akun`. |
| `/api/admin/siswa` (`?q=&status=`) | `nama_siswa`*, `nisn_resmi`* (10 digit), `nama_wali`, `no_hp_wali`. Siswa baru selalu berstatus `baru`. Satu-satunya ubah status manual: `PUT {"status_siswa": "pindah"}` dari `baru`/`aktif`. |
| `/api/admin/kelas` (`?id_tahun_ajaran=`) | `id_tahun_ajaran`*, `tingkat_kelas`* (1–6), `nama_kelas`*, `id_guru_wali` |
| `/api/admin/mapel` | `kode_mapel`, `nama_mapel`* |
| `/api/admin/tahun-ajaran` | `nama_tahun`* (`2026/2027`); `PUT {"semester_aktif": "genap", "konfirmasi_semester": true}`; `PUT /{id}/aktifkan` |
| `/api/admin/hari-libur` | `tanggal_libur`*, `keterangan`, `id_kelas` (daftar; kosong = semua kelas) |

`*` wajib. **DELETE** data yang masih terkait → **409** (gunakan status/nonaktif). Guru yang masih jadi wali, dan kelas yang masih jadi target hari libur, juga 409.

### Penempatan siswa — `/api/admin/riwayat-kelas`
`GET ?id_kelas=&id_tahun_ajaran=&id_siswa=` · `POST {id_siswa, id_kelas, nomor_absen}` (hanya tahun aktif) · `PUT /{id}` (nomor absen) · `DELETE /{id}` (koreksi; 409 bila sudah ada absensi/nilai/disiplin/piket/log kenaikan).

### Kenaikan kelas dan tahun ajaran
| Endpoint | Keterangan |
|---|---|
| `POST /api/admin/kenaikan-kelas` | `{"id_tahun_ajaran_tujuan": 2, "penempatan_tingkat_6": [{id_siswa, id_kelas_tujuan}], "penempatan_siswa_baru": [{id_siswa, id_kelas_tujuan}]}`. Satu transaksi. 422 + daftar masalah bila: ada siswa aktif tanpa penempatan, struktur rombel salah, kelas tujuan tanpa wali atau wali nonaktif, `penempatan_tingkat_6` dikirim padahal hanya satu kelas tingkat 6. |
| `GET /api/admin/kenaikan-kelas/log?tahun_ajaran_tujuan=` | ringkasan naik/lulus/masuk |
| `PUT /api/admin/tahun-ajaran/{id}/aktifkan` | aktivasi pertama boleh langsung; selanjutnya 422 bila belum ada kenaikan kelas ke tahun itu |

### Dasbor
`GET /api/admin/dasbor/absensi?date=` → `{tanggal, tahun_ajaran, ringkasan: {sudah, belum, libur}, items: [{id_kelas, nama_kelas, tingkat_kelas, nama_guru_wali, status}]}`

### Konten website
| Sumber daya | Field | Catatan |
|---|---|---|
| `POST /api/admin/upload` | multipart, field `file` | JPG/PNG/WEBP ≤ 2 MB → `{path: "uploads/<acak>.png"}` |
| `/api/admin/ekstrakurikuler` | `nama_ekskul`*, `deskripsi`, `ikon` | CRUD |
| `/api/admin/guru-ekskul` | `id_guru`, `id_ekskul` | `GET ?id_guru=&id_ekskul=`, `POST`, `DELETE /{id}`; duplikat 409 |
| `/api/admin/event` (`?kategori=`) | `nama_event`*, `kategori`*, `tanggal`, `deskripsi`, `banner`, `status_tampil` | CRUD; hapus event ikut menghapus galerinya |
| `/api/admin/galeri` (`?id_event=` / `?mandiri=1`) | `id_event` (null = mandiri), `file_gambar`*, `keterangan` | CRUD |
| `/api/admin/mading` | `judul_karya`*, `kategori`, `deskripsi`, `nama_penulis`, `file_gambar`*, `status_tampil` | CRUD |
| `/api/admin/program-pembiasaan` | `nama_program`, `deskripsi`, `ikon` | hanya `GET` dan `PUT /{id}` (enam baris tetap; `hari` tidak bisa diubah) |
| `/api/admin/pengaturan` | `ppdb_form_url`, `video_profil_url` | `PUT` parsial; harus URL http/https; `null` menghapus; respons memuat `diubah_pada` dan `diubah_oleh` |

Kolom gambar menerima path hasil unggah atau URL **https**; selain itu 422.

### Data akademik (HANYA GET)
`GET /api/admin/akademik/absensi|nilai|disiplin`, paginasi. Filter: `id_tahun_ajaran` (default aktif; tak ada → 404), `id_kelas`, `id_siswa`; absensi: `semester`, `status`, `dari`, `sampai`; nilai: `semester`, `id_mapel`, `jenis`; disiplin: `dari`, `sampai`. Metode lain → 405.

### Laporan
`GET /api/admin/laporan?semester=&tahun_ajaran=&kelas=` dan `/api/admin/laporan/excel?…&jenis=`. Seluruh tahun ajaran; `kelas` opsional (harus milik tahun itu, 422); tahun tak ada 404.

---

## 6. Laporan: bentuk preview JSON

```json
{"meta": {"sekolah": "SDN Kedungsari 1", "tahun_ajaran": "2026/2027", "id_tahun_ajaran": 1,
          "semester": "ganjil", "kelas": "3A", "periode_absensi": {"awal": "2026-07-13", "akhir": "2026-12-19"},
          "maks_uh": 2, "tanggal_ekspor": "2026-10-10 14:05"},
 "kelas": [{"id_kelas": 5, "nama_kelas": "3A", "tingkat_kelas": 3, "nama_guru_wali": "Rina",
            "absensi": [{"id_kelas": 5, "id_siswa": 101, "nisn": "0000000001", "nama_siswa": "…", "nomor_absen": 1,
                         "hadir": 80, "sakit": 2, "izin": 1, "alpa": 0, "total": 83}],
            "nilai": [{"nisn": "…", "nama_siswa": "…", "nomor_absen": 1, "id_mapel": 3, "nama_mapel": "Matematika",
                       "uh": {"1": 80, "2": 90}, "asts": 85, "asas": null}]}]}
```
Siswa yang kemudian pindah **tetap dimuat** selama punya data pada semester itu. `uh` adalah objek `{nomor: nilai}`.

**Unduhan Excel:** respons berupa berkas biner (`Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet`) dengan `Content-Disposition: attachment; filename="laporan-ganjil-2026-2027-3A.xlsx"`. Ambil dengan `fetch` + header `Authorization`, lalu simpan sebagai blob (jangan taruh token di URL). Sheet: *Informasi*, *Rekap Absensi*, *Rekap Nilai*. Kegagalan (401/403/422…) tetap berupa JSON, jadi periksa `response.ok` / `Content-Type` sebelum menyimpan sebagai berkas.

---

## 7. Catatan perilaku yang sering salah dipahami

1. **Semester absensi diisi server** dari semester berjalan; semester nilai dan jadwal dikirim klien.
2. **Siswa non-aktif** (baru/lulus/pindah) tidak muncul di daftar Guru dan ditolak 422 pada absensi/nilai/disiplin/piket.
3. **Logout tidak mencabut token.** Token mati karena habis (8 jam), akun dinonaktifkan, atau password diganti.
4. **Hari Minggu** otomatis libur (absensi 422, dasbor `libur`); Sabtu efektif.
5. **Semester berjalan** hanya maju ganjil → genap dan memerlukan `konfirmasi_semester: true`.
6. **Hapus data master yang terkait = 409**; tampilkan sebagai kotak informasi, bukan kesalahan merah.
7. **Gambar**: simpan path dari `/api/admin/upload`; menghapus konten tidak menghapus berkas gambarnya.
8. **CORS**: header `Content-Disposition` diekspos agar nama berkas unduhan terbaca klien web.

## 8. Selisih dengan Studi Kasus (perlu diketahui tim)

| Studi Kasus | Perilaku backend | Cara menyesuaikan |
|---|---|---|
| Dok. 01 tes **G6**: simpan absensi dua kali pada tanggal yang sama, "kedua kali berhasil sebagai koreksi" | `POST` kedua kali → 409 | Klien memakai alur POST (baru) + PUT (koreksi) seperti di bagian 4. Atau backend dijadikan upsert (keputusan terbuka). |
| Dok. 01 form Jadwal/Nilai: mapel "dari daftar mapel" | Sebelumnya hanya `/api/admin/mapel` | Ditambah `GET /api/guru/mapel`. |
