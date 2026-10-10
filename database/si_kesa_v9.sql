-- =====================================================================
-- SI-KESA — Sistem Informasi SDN Kedungsari Satu
-- Skema database v9  |  selaras dengan URS v2.7, SRS v2.7, BRD v2.7, TDD v1.7
-- Database: `si_kesa`   |   DBMS target: MySQL 8.0.16+ (CHECK aktif).
-- Skema v9 BELUM dijalankan pada DBMS apa pun; uji ulang pada MySQL 8.0.16+
-- (CHECK, UNIQUE, FK) sebelum dipakai. MariaDB 10.4+ secara teori kompatibel
-- tetapi BELUM diuji.
--
-- RINGKASAN REVISI v9 (keputusan Okt 2026: siswa baru/pindahan, manajemen akun)
--  [DECISION] data_siswa.status_siswa ditambah nilai 'baru' dan menjadi DEFAULT.
--             Siswa baru (PPDB) dan siswa pindahan masuk berstatus 'baru'
--             (belum punya penempatan kelas). Status berubah menjadi 'aktif'
--             saat siswa ditempatkan: (a) lewat proses kenaikan kelas
--             (hasil_proses = 'masuk'), atau (b) lewat penempatan langsung
--             ke tahun ajaran AKTIF (/api/admin/riwayat-kelas). Siswa 'baru'
--             tidak memblokir proses kenaikan kelas (service).
--  [FIX] log_promosi_siswa: id_tahun_asal dan id_kelas_asal menjadi NULLABLE;
--        hasil_proses ditambah 'masuk' (siswa baru/pindahan yang ditempatkan
--        saat proses kenaikan kelas, tanpa asal). CHECK diperluas:
--          naik  -> asal (tahun+kelas) terisi, tujuan terisi
--          lulus -> asal (tahun+kelas) terisi, tujuan NULL
--          masuk -> asal (tahun+kelas) NULL,   tujuan terisi
--  [NEW] akun_pengguna.kata_sandi_diubah_pada (NULL = belum pernah diubah).
--        Dipakai middleware: token yang diterbitkan SEBELUM waktu ini ditolak
--        (token lama tidak berlaku setelah password diganti/di-reset).
--  [NOTE] Aturan rombel (service, tanpa constraint database): tingkat 2-5
--        tepat satu kelas per tahun ajaran; tingkat 1 dan tingkat 6 boleh
--        satu atau dua kelas (maksimal 2). Kenaikan 1->2 selalu menuju satu
--        kelas (dua kelas tingkat 1 digabung otomatis); pemecahan rombel
--        hanya pada 5->6.
--  [NOTE] Aktivasi tahun ajaran pertama diizinkan tanpa kenaikan kelas
--        (data awal diisi tim pengembang). Aktivasi berikutnya hanya setelah
--        kenaikan kelas ke tahun tsb dijalankan (service).
--  [NOTE] Akun Admin tidak dihapus, hanya dinonaktifkan; service menjamin
--        minimal satu Admin aktif dan Admin tidak dapat menonaktifkan dirinya.
--  [NOTE] catatan_kedisiplinan tidak dapat dihapus (tanpa DELETE di API).
--  [FIX] SET SQL_MODE dan START TRANSACTION/COMMIT dihapus: SQL_MODE sebelumnya
--        menimpa mode strict sesi, dan DDL di MySQL ter-commit otomatis
--        sehingga transaksi tidak berguna. Skrip kini tidak mengubah sql_mode.
--  Skrip migrasi v8 -> v9 (komentar) ada di akhir berkas.
--
-- CATATAN TAMBAHAN (Okt 2026, tanpa perubahan struktur tabel):
--  [NOTE] Akun Admin yang pernah memproses kenaikan kelas tidak dapat dihapus
--        (log_promosi_siswa.id_user_proses RESTRICT); nonaktifkan via status_akun.
--  [NOTE] Jadwal pelajaran dan piket tidak disalin otomatis ke tahun ajaran baru;
--        Guru menginput ulang tiap tahun ajaran (keputusan 3 Okt 2026).
--  [NOTE] log_promosi_siswa: FK pada kolom yang dipakai CHECK dipertahankan
--        RESTRICT (MySQL membatasi aksi referensial lain pada kolom CHECK);
--        verifikasi saat uji pada MySQL 8.0.16+ dan jangan diubah ke CASCADE.
--
-- CATATAN v2.4 (3 Okt 2026) — HANYA KOMENTAR, tanpa perubahan struktur tabel (nama berkas tetap v9):
--  [NOTE] tahun_ajaran.semester_aktif hanya boleh berubah dari 'ganjil' ke 'genap' pada tahun
--        ajaran aktif, dengan konfirmasi eksplisit (konfirmasi_semester = true di API). Genap ke
--        ganjil ditolak; tahun ajaran baru dibuat 'ganjil'. Ditegakkan di service, bukan constraint.
--  [NOTE] DELETE riwayat_kelas hanya untuk koreksi penempatan dan dicek di service (FK tidak
--        menolaknya karena absensi/nilai/kedisiplinan berelasi ke siswa dan kelas). Tolak 409 bila
--        ada absensi/nilai/kedisiplinan (id_siswa, id_kelas, id_tahun_ajaran), piket_siswa kelas
--        tsb, atau log_promosi_siswa (id_tahun_asal/id_tahun_tujuan) pada tahun yang sama. Bila
--        sukses dan siswa 'aktif' tanpa riwayat lain, status_siswa kembali 'baru'.
--  [NOTE] DELETE data_guru ditolak (service) bila masih ada kelas.id_guru_wali yang menunjuk Guru
--        tsb. kelas.id_guru_wali ON DELETE SET NULL dipertahankan hanya sebagai pengaman terakhir.
--  [NOTE] Kelas tingkat 1 boleh belum dibuat pada tahun ajaran tujuan kenaikan kelas bila tidak
--        ada siswa 'baru' yang ditempatkan ke tingkat 1 (service).
--
-- CATATAN v2.5 (3 Okt 2026) — HANYA KOMENTAR, tanpa perubahan struktur tabel (nama berkas tetap v9):
--  [NOTE] Token Wali diperiksa pada SETIAP permintaan: data_siswa.status_siswa = 'aktif' dan
--        ada riwayat_kelas pada tahun_ajaran.status_aktif = 1; bila tidak, 401 generik (service).
--  [NOTE] Logout tidak mencabut token di server. Token Admin/Guru berlaku 8 jam; pencabutan dini
--        lewat akun_pengguna.status_akun = 'nonaktif' atau kata_sandi_diubah_pada (service).
--  [NOTE] PUT /api/admin/akun/{id}/password ditolak (422) bila {id} = akun pemanggil (service).
--  [NOTE] Hari Minggu non-efektif otomatis: absensi ditolak (422) dan dasbor menampilkan libur,
--        tanpa baris pada hari_libur. Sabtu hari efektif (service).
--  [NOTE] Kenaikan kelas: penempatan_tingkat_6 yang dikirim padahal hanya ada satu kelas tingkat 6
--        ditolak (422); kelas tahun tujuan tanpa wali ditolak (service).
--  [NOTE] Koreksi hasil kenaikan kelas (log_promosi_siswa) dan pindah kelas tengah tahun belum ada
--        jalur API; perbaikan oleh pengembang melalui basis data.
--
-- CATATAN v2.6 (3 Okt 2026) — HANYA KOMENTAR, tanpa perubahan struktur tabel (nama berkas tetap v9):
--  [NOTE] Kenaikan kelas ditolak (422, tanpa data berubah) bila ada kelas pada tahun ajaran tujuan
--        yang belum memiliki wali (id_guru_wali NULL) ATAU yang walinya berakun nonaktif
--        (data_guru.id_user -> akun_pengguna.status_akun = 'nonaktif'). Ditegakkan di service.
--  [NOTE] DELETE kelas ditolak (service, 409) bila masih ada libur_target_kelas yang menunjuk kelas
--        tsb. FK libur_target_kelas -> kelas tetap ON DELETE CASCADE, sehingga tanpa pemeriksaan ini
--        hari libur khusus kelas yang kehilangan seluruh targetnya akan berlaku untuk semua kelas.
--        Admin menghapus/mengubah target hari libur lebih dulu.
--  [NOTE] Jeda antara kenaikan kelas dan aktivasi tahun ajaran: siswa tingkat 6 sudah berstatus
--        'lulus' (tidak dapat login Wali, tidak tampil pada daftar Guru). Aktivasi dilakukan segera
--        setelah kenaikan kelas berhasil, sebaiknya di luar jam sekolah.
--  [NOTE] Pemeriksaan token Admin/Guru membandingkan iat dengan kata_sandi_diubah_pada pada resolusi
--        detik; token lama pada detik yang sama dengan penggantian password tidak ditolak (batasan
--        yang diterima, token tetap dibatasi 8 jam).

-- CATATAN v2.7 (Okt 2026) — HANYA KOMENTAR, tanpa perubahan struktur tabel (nama berkas tetap v9):
--  [NOTE] Jeda kenaikan kelas dan aktivasi: selain siswa 'lulus', siswa 'baru' yang dipetakan lewat
--        penempatan_siswa_baru (hasil 'masuk') berstatus 'aktif' tetapi hanya memiliki riwayat_kelas
--        pada tahun tujuan yang belum aktif; selama jeda mereka belum dapat login Wali dan belum
--        tampil pada daftar Guru. Siswa tingkat 1-5 yang 'naik' tidak terdampak (masih punya riwayat
--        pada tahun aktif lama).
--  [NOTE] Penempatan langsung (POST /api/admin/riwayat-kelas): siswa 'aktif' tanpa riwayat pada tahun
--        ajaran aktif boleh ditempatkan dan tetap 'aktif' (selaras URS FR-KELAS-11 v2.7, AC-37).
--  [NOTE] Penonaktifan akun Guru yang masih menjadi wali (kelas.id_guru_wali) tidak diblokir
--        (keputusan terbuka, URS Lampiran B butir 5); kelas dengan wali nonaktif ditolak pada
--        kenaikan kelas (service).

-- RINGKASAN REVISI v8 (perlindungan data akademik; URS BR-22, SRS NFR-DAT-01)
--  [FIX] Seluruh relasi ke data akademik, jadwal, piket, dan riwayat kelas
--        diubah dari ON DELETE CASCADE menjadi ON DELETE RESTRICT.
--        Penghapusan siswa/kelas/mapel/tahun ajaran/akun yang masih
--        terkait data ditolak oleh database (service membalas 409).
--        Admin tidak lagi dapat menghapus data akademik Guru secara
--        tidak langsung. Siswa memakai status_siswa; Guru/Admin memakai
--        status_akun = 'nonaktif'.
--        Tabel terdampak: data_guru(id_user), riwayat_kelas, log_promosi_siswa,
--        absensi_siswa, jurnal_absen_kelas, data_nilai, jadwal_pelajaran,
--        jadwal_piket, piket_siswa(id_siswa), catatan_kedisiplinan(id_siswa).
--  [KEEP] CASCADE dipertahankan hanya untuk data turunan yang dimiliki induknya:
--        piket_siswa(id_piket), guru_ekskul, libur_target_kelas,
--        galeri_kegiatan(id_event). kelas.id_guru_wali tetap SET NULL.
--  [NOTE] Urutan hapus akun Guru: hapus data_guru lebih dulu, lalu akun_pengguna
--        (dulu otomatis via CASCADE). Hapus ditolak bila Guru punya data akademik.
--  [NOTE] Konsistensi id_tahun_ajaran pada tabel transaksi terhadap
--        kelas.id_tahun_ajaran ditegakkan di service (SRS §3.6).
--
-- RINGKASAN REVISI v7 (hasil review kesesuaian dokumen vs SQL)
--  [DECISION] Tahun ajaran & semester dipisah. `tahun_ajaran` = satu baris
--             per tahun ajaran (mis. 2026/2027). Semester = enum
--             ('ganjil','genap') pada tabel transaksi; semester yang sedang
--             berjalan disimpan di `tahun_ajaran.semester_aktif`.
--  [DECISION] `kelas` dibuat per tahun ajaran (kolom id_tahun_ajaran).
--             Satu Guru hanya menjadi wali satu kelas pada satu tahun
--             ajaran: UNIQUE(id_tahun_ajaran, id_guru_wali).
--  [DECISION] Kenaikan kelas: siswa kelas 6 -> status_siswa 'lulus'.
--             `log_promosi_siswa.id_kelas_tujuan` NULL untuk lulusan,
--             ditandai `hasil_proses` = 'lulus'.
--  [DECISION] Guru boleh menghapus nilai UH yang salah input (aturan
--             "hanya UH" ditegakkan di service/API; FK tidak membatasi).
--  [DECISION] Seluruh nama tabel/kolom memakai bahasa Indonesia, snake_case.
--             Konvensi: PK `id_<entitas>`, FK memakai nama PK induk,
--             `dibuat_pada` / `diubah_pada` untuk audit waktu.
--  [FIX] jadwal_pelajaran: id_guru DIHAPUS; ditambah semester & keterangan.
--  [FIX] jadwal_piket dipecah: header (kelas/tahun/hari) + `piket_siswa`.
--        id_guru -> `id_guru_penetap` (audit penetap).
--  [FIX] data_guru + email, no_telepon. akun_pengguna + status_akun.
--  [FIX] ekstrakurikuler + deskripsi. event_sekolah.kategori + 'prestasi'.
--  [FIX] riwayat_kelas + UNIQUE(id_kelas, nomor_absen).
--  [FIX] data_nilai: + id_kelas, + semester; nomor_penilaian -> nomor_uh;
--        kolom virtual `nomor_uh_kunci` agar ASTS/ASAS ganda tercegah.
--  [FIX] absensi_siswa + semester.
--  [FIX] galeri_kegiatan.id_event boleh NULL; galeri_mading + deskripsi,
--        nama_penulis; event_sekolah + status_tampil.
--  [FIX] pengaturan_sistem: + diubah_pada, id_user_pengubah; seed ppdb_form_url
--        & video_profil_url.
--  [KEEP] jurnal_absen_kelas dan log_promosi_siswa dipertahankan.
-- =====================================================================

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------
-- akun_pengguna : akun internal Admin/Guru. Wali Murid TIDAK punya akun.
-- [v9] Akun Admin tidak dihapus (nonaktifkan); minimal satu Admin aktif dan
-- Admin tidak menonaktifkan dirinya sendiri (ditegakkan di service).
-- --------------------------------------------------------
CREATE TABLE `akun_pengguna` (
  `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `kata_sandi_hash` varchar(255) NOT NULL,
  `role` enum('admin','guru') NOT NULL,
  `status_akun` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  `kata_sandi_diubah_pada` timestamp NULL DEFAULT NULL COMMENT 'token dengan iat sebelum waktu ini ditolak',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diubah_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- tahun_ajaran : satu baris = satu tahun ajaran.
-- semester_aktif = semester yang sedang berjalan pada tahun tsb.
-- Hanya SATU tahun ajaran boleh status_aktif = 1 (aktif_flag + UNIQUE).
-- [v2.4] semester_aktif hanya maju ganjil -> genap (service, dengan konfirmasi); tidak boleh mundur.
-- --------------------------------------------------------
CREATE TABLE `tahun_ajaran` (
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_tahun` varchar(9) NOT NULL COMMENT 'contoh: 2026/2027',
  `semester_aktif` enum('ganjil','genap') NOT NULL DEFAULT 'ganjil',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 0,
  `aktif_flag` tinyint(1) GENERATED ALWAYS AS (IF(`status_aktif` = 1, 1, NULL)) STORED,
  PRIMARY KEY (`id_tahun_ajaran`),
  UNIQUE KEY `uq_nama_tahun` (`nama_tahun`),
  UNIQUE KEY `uq_tahun_aktif_tunggal` (`aktif_flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- mata_pelajaran : tanpa guru pengampu.
-- --------------------------------------------------------
CREATE TABLE `mata_pelajaran` (
  `id_mapel` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(10) DEFAULT NULL,
  `nama_mapel` varchar(50) NOT NULL,
  PRIMARY KEY (`id_mapel`),
  UNIQUE KEY `uq_kode_mapel` (`kode_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ekstrakurikuler
-- --------------------------------------------------------
CREATE TABLE `ekstrakurikuler` (
  `id_ekskul` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_ekskul` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `ikon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_ekskul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- hari_libur (+ libur_target_kelas bila libur hanya untuk kelas tertentu;
-- tanpa baris target = berlaku untuk semua kelas)
-- --------------------------------------------------------
CREATE TABLE `hari_libur` (
  `id_libur` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tanggal_libur` date NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_libur`),
  KEY `idx_tanggal_libur` (`tanggal_libur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- data_guru : semua Guru = guru kelas/wali kelas.
-- [v8] id_user RESTRICT: akun tidak dapat dihapus selagi data_guru ada.
-- [v2.4] Guru yang masih menjadi wali (kelas.id_guru_wali) tidak dapat dihapus (service, 409).
-- --------------------------------------------------------
CREATE TABLE `data_guru` (
  `id_guru` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` int(10) UNSIGNED NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `nama_guru` varchar(100) NOT NULL,
  `jabatan` varchar(50) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `uq_nip` (`nip`),
  UNIQUE KEY `uq_guru_user` (`id_user`),
  CONSTRAINT `fk_guru_user` FOREIGN KEY (`id_user`) REFERENCES `akun_pengguna` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- kelas : dibuat per tahun ajaran; wali dapat berganti antar tahun.
-- Rombel (ditegakkan di service): tingkat 2-5 tepat satu kelas per tahun ajaran;
-- tingkat 1 dan tingkat 6 boleh satu atau dua kelas (maksimal 2). Tingkat 1 boleh belum dibuat
-- pada tahun tujuan kenaikan kelas bila tidak ada siswa baru ditempatkan ke tingkat 1.
-- --------------------------------------------------------
CREATE TABLE `kelas` (
  `id_kelas` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `tingkat_kelas` tinyint(3) UNSIGNED NOT NULL,
  `nama_kelas` varchar(10) NOT NULL,
  `id_guru_wali` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_kelas`),
  UNIQUE KEY `uq_kelas_nama_tahun` (`id_tahun_ajaran`,`nama_kelas`),
  UNIQUE KEY `uq_wali_per_tahun` (`id_tahun_ajaran`,`id_guru_wali`),
  KEY `fk_kelas_guru` (`id_guru_wali`),
  CONSTRAINT `chk_kelas_tingkat` CHECK (`tingkat_kelas` BETWEEN 1 AND 6),
  CONSTRAINT `fk_kelas_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_kelas_guru` FOREIGN KEY (`id_guru_wali`) REFERENCES `data_guru` (`id_guru`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- data_siswa : NISN = identitas unik & satu-satunya credential Wali.
-- Siswa tidak dihapus bila sudah punya data; gunakan status_siswa.
-- [v9] 'baru' = siswa PPDB/pindahan yang belum ditempatkan (DEFAULT). Menjadi
-- 'aktif' saat ditempatkan. 'baru' tidak dapat login Wali dan tidak muncul
-- pada daftar Guru. Daftar Guru hanya memuat siswa 'aktif' (filter service);
-- data historis siswa 'pindah' tetap tersimpan dan tampil pada laporan semester
-- yang memuat datanya.
-- --------------------------------------------------------
CREATE TABLE `data_siswa` (
  `id_siswa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_siswa` varchar(100) NOT NULL,
  `nisn_resmi` char(10) NOT NULL,
  `status_siswa` enum('baru','aktif','lulus','pindah') NOT NULL DEFAULT 'baru',
  `nama_wali` varchar(100) DEFAULT NULL,
  `no_hp_wali` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `uq_nisn_resmi` (`nisn_resmi`),
  KEY `idx_status_siswa` (`status_siswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- riwayat_kelas : penempatan siswa per tahun ajaran.
-- [v2.4] DELETE hanya untuk koreksi dan diperiksa di service (lihat CATATAN v2.4).
-- nomor_absen hanya atribut penempatan, BUKAN credential login.
-- --------------------------------------------------------
CREATE TABLE `riwayat_kelas` (
  `id_riwayat` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `nomor_absen` tinyint(3) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_riwayat`),
  UNIQUE KEY `uq_siswa_tahun` (`id_siswa`,`id_tahun_ajaran`),
  UNIQUE KEY `uq_kelas_nomor_absen` (`id_kelas`,`nomor_absen`),
  KEY `idx_riwayat_tahun_kelas` (`id_tahun_ajaran`,`id_kelas`),
  CONSTRAINT `fk_riwayat_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_riwayat_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_riwayat_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- log_promosi_siswa : audit kenaikan kelas (semua siswa aktif diproses) dan
-- penempatan siswa 'baru' yang dilakukan melalui proses kenaikan kelas.
-- hasil_proses = 'naik'  -> siswa aktif; asal DAN tujuan wajib terisi
-- hasil_proses = 'lulus' -> siswa kelas 6; asal terisi, id_kelas_tujuan NULL,
--                           status_siswa diubah menjadi 'lulus'.
-- hasil_proses = 'masuk' -> siswa 'baru'/pindahan; asal (tahun+kelas) NULL,
--                           tujuan terisi, status_siswa diubah menjadi 'aktif'.
-- Tidak ada hasil "tidak naik".
-- --------------------------------------------------------
CREATE TABLE `log_promosi_siswa` (
  `id_log_promosi` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_tahun_asal` smallint(5) UNSIGNED DEFAULT NULL,
  `id_tahun_tujuan` smallint(5) UNSIGNED NOT NULL,
  `id_kelas_asal` smallint(5) UNSIGNED DEFAULT NULL,
  `id_kelas_tujuan` smallint(5) UNSIGNED DEFAULT NULL,
  `hasil_proses` enum('naik','lulus','masuk') NOT NULL,
  `id_user_proses` int(10) UNSIGNED NOT NULL,
  `diproses_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log_promosi`),
  UNIQUE KEY `uq_promosi_siswa_tahun` (`id_siswa`,`id_tahun_tujuan`),
  KEY `fk_promosi_tahun_asal` (`id_tahun_asal`),
  KEY `fk_promosi_tahun_tujuan` (`id_tahun_tujuan`),
  KEY `fk_promosi_kelas_asal` (`id_kelas_asal`),
  KEY `fk_promosi_kelas_tujuan` (`id_kelas_tujuan`),
  KEY `fk_promosi_user` (`id_user_proses`),
  CONSTRAINT `chk_promosi_hasil` CHECK (
    (`hasil_proses` = 'naik'
       AND `id_tahun_asal` IS NOT NULL AND `id_kelas_asal` IS NOT NULL
       AND `id_kelas_tujuan` IS NOT NULL)
    OR
    (`hasil_proses` = 'lulus'
       AND `id_tahun_asal` IS NOT NULL AND `id_kelas_asal` IS NOT NULL
       AND `id_kelas_tujuan` IS NULL)
    OR
    (`hasil_proses` = 'masuk'
       AND `id_tahun_asal` IS NULL AND `id_kelas_asal` IS NULL
       AND `id_kelas_tujuan` IS NOT NULL)
  ),
  CONSTRAINT `fk_promosi_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_promosi_tahun_asal` FOREIGN KEY (`id_tahun_asal`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_promosi_tahun_tujuan` FOREIGN KEY (`id_tahun_tujuan`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_promosi_kelas_asal` FOREIGN KEY (`id_kelas_asal`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_promosi_kelas_tujuan` FOREIGN KEY (`id_kelas_tujuan`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_promosi_user` FOREIGN KEY (`id_user_proses`) REFERENCES `akun_pengguna` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- absensi_siswa : harian, TIDAK per mata pelajaran.
-- id_kelas, id_tahun_ajaran, semester & id_guru diturunkan server.
-- --------------------------------------------------------
CREATE TABLE `absensi_siswa` (
  `id_absensi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `semester` enum('ganjil','genap') NOT NULL,
  `id_guru` int(10) UNSIGNED NOT NULL,
  `tanggal_absen` date NOT NULL,
  `status_kehadiran` enum('hadir','sakit','izin','alpa') NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diubah_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_absensi`),
  UNIQUE KEY `uq_siswa_tanggal` (`id_siswa`,`tanggal_absen`),
  KEY `fk_absensi_guru` (`id_guru`),
  KEY `idx_absensi_kelas_tanggal` (`id_kelas`,`tanggal_absen`),
  KEY `idx_absensi_tahun_semester` (`id_tahun_ajaran`,`semester`,`id_kelas`),
  CONSTRAINT `fk_absensi_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_absensi_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_absensi_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_absensi_guru` FOREIGN KEY (`id_guru`) REFERENCES `data_guru` (`id_guru`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- jurnal_absen_kelas : penanda "kelas X sudah diabsen pada tanggal Y"
-- (untuk dasbor Guru/Admin). Bukan sumber kebenaran kehadiran siswa.
-- --------------------------------------------------------
CREATE TABLE `jurnal_absen_kelas` (
  `id_jurnal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `sudah_absen` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_jurnal`),
  UNIQUE KEY `uq_jurnal_kelas_tahun_tanggal` (`id_kelas`,`id_tahun_ajaran`,`tanggal`),
  KEY `fk_jurnal_tahun` (`id_tahun_ajaran`),
  CONSTRAINT `fk_jurnal_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_jurnal_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- data_nilai : UH (boleh berulang, bernomor), ASTS, ASAS.
-- nomor_uh_kunci = IFNULL(nomor_uh,0) -> membuat UNIQUE efektif untuk
-- ASTS/ASAS (satu ASTS dan satu ASAS per siswa-mapel-semester).
-- Otorisasi: kelas.id_guru_wali. Guru boleh DELETE khusus baris UH
-- (ditegakkan di service).
-- --------------------------------------------------------
CREATE TABLE `data_nilai` (
  `id_nilai` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_mapel` tinyint(3) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `semester` enum('ganjil','genap') NOT NULL,
  `id_guru` int(10) UNSIGNED NOT NULL,
  `jenis_penilaian` enum('UH','ASTS','ASAS') NOT NULL,
  `nomor_uh` tinyint(3) UNSIGNED DEFAULT NULL,
  `nomor_uh_kunci` tinyint(3) UNSIGNED GENERATED ALWAYS AS (IFNULL(`nomor_uh`, 0)) STORED,
  `nilai_angka` tinyint(3) UNSIGNED NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diubah_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_nilai`),
  UNIQUE KEY `uq_nilai` (`id_siswa`,`id_mapel`,`id_tahun_ajaran`,`semester`,`jenis_penilaian`,`nomor_uh_kunci`),
  KEY `fk_nilai_mapel` (`id_mapel`),
  KEY `fk_nilai_guru` (`id_guru`),
  KEY `idx_nilai_kelas_tahun_semester_mapel` (`id_kelas`,`id_tahun_ajaran`,`semester`,`id_mapel`),
  KEY `idx_nilai_siswa_tahun_semester` (`id_siswa`,`id_tahun_ajaran`,`semester`),
  CONSTRAINT `chk_nilai_range` CHECK (`nilai_angka` BETWEEN 0 AND 100),
  CONSTRAINT `chk_nilai_jenis_nomor` CHECK (
    (`jenis_penilaian` = 'UH' AND `nomor_uh` IS NOT NULL AND `nomor_uh` >= 1)
    OR
    (`jenis_penilaian` IN ('ASTS','ASAS') AND `nomor_uh` IS NULL)
  ),
  CONSTRAINT `fk_nilai_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_guru` FOREIGN KEY (`id_guru`) REFERENCES `data_guru` (`id_guru`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- guru_ekskul
-- --------------------------------------------------------
CREATE TABLE `guru_ekskul` (
  `id_guru_ekskul` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_guru` int(10) UNSIGNED NOT NULL,
  `id_ekskul` tinyint(3) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_guru_ekskul`),
  UNIQUE KEY `uq_guru_ekskul` (`id_guru`,`id_ekskul`),
  KEY `fk_guru_ekskul_ekskul` (`id_ekskul`),
  CONSTRAINT `fk_guru_ekskul_ekskul` FOREIGN KEY (`id_ekskul`) REFERENCES `ekstrakurikuler` (`id_ekskul`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_guru_ekskul_guru` FOREIGN KEY (`id_guru`) REFERENCES `data_guru` (`id_guru`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- libur_target_kelas : target kelas untuk hari libur (opsional)
-- --------------------------------------------------------
CREATE TABLE `libur_target_kelas` (
  `id_target` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_libur` smallint(5) UNSIGNED NOT NULL,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_target`),
  UNIQUE KEY `uq_libur_kelas` (`id_libur`,`id_kelas`),
  KEY `fk_target_kelas` (`id_kelas`),
  CONSTRAINT `fk_target_libur` FOREIGN KEY (`id_libur`) REFERENCES `hari_libur` (`id_libur`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_target_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- event_sekolah & galeri
-- --------------------------------------------------------
CREATE TABLE `event_sekolah` (
  `id_event` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_event` varchar(100) NOT NULL,
  `kategori` enum('kegiatan','event','prestasi','kolaborasi') NOT NULL,
  `tanggal` date DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `status_tampil` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `galeri_kegiatan` (
  `id_galeri` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_event` int(10) UNSIGNED DEFAULT NULL,
  `file_gambar` varchar(255) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_galeri`),
  KEY `fk_galeri_event` (`id_event`),
  CONSTRAINT `fk_galeri_event` FOREIGN KEY (`id_event`) REFERENCES `event_sekolah` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `galeri_mading` (
  `id_mading` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `judul_karya` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `nama_penulis` varchar(100) DEFAULT NULL,
  `file_gambar` varchar(255) NOT NULL,
  `status_tampil` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_mading`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- program_pembiasaan
-- --------------------------------------------------------
CREATE TABLE `program_pembiasaan` (
  `id_program` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL,
  `nama_program` varchar(50) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `ikon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_program`),
  UNIQUE KEY `uq_program_hari` (`hari`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- jadwal_pelajaran : dikelola Guru (kelas sendiri). TANPA id_guru
-- (tidak ada guru pengampu). Admin tidak punya akses. Tidak disalin otomatis
-- ke tahun ajaran baru; Guru input ulang tiap tahun ajaran.
-- --------------------------------------------------------
CREATE TABLE `jadwal_pelajaran` (
  `id_jadwal` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `semester` enum('ganjil','genap') NOT NULL,
  `id_mapel` tinyint(3) UNSIGNED NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_jadwal`),
  UNIQUE KEY `uq_jadwal_slot` (`id_kelas`,`id_tahun_ajaran`,`semester`,`hari`,`jam_mulai`),
  KEY `fk_jadwal_mapel` (`id_mapel`),
  KEY `fk_jadwal_tahun` (`id_tahun_ajaran`),
  CONSTRAINT `chk_jadwal_jam` CHECK (`jam_selesai` > `jam_mulai`),
  CONSTRAINT `fk_jadwal_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwal_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_jadwal_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- jadwal_piket (header per kelas/tahun/hari) + piket_siswa (siswa bertugas)
-- Piket = kebersihan kelas oleh SISWA. id_guru_penetap hanya audit.
-- Tidak disalin otomatis ke tahun ajaran baru; Guru input ulang.
-- --------------------------------------------------------
CREATE TABLE `jadwal_piket` (
  `id_piket` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `hari` enum('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `id_guru_penetap` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_piket`),
  UNIQUE KEY `uq_piket_kelas_tahun_hari` (`id_kelas`,`id_tahun_ajaran`,`hari`),
  KEY `fk_piket_tahun` (`id_tahun_ajaran`),
  KEY `fk_piket_guru` (`id_guru_penetap`),
  CONSTRAINT `fk_piket_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_piket_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_piket_guru` FOREIGN KEY (`id_guru_penetap`) REFERENCES `data_guru` (`id_guru`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `piket_siswa` (
  `id_piket_siswa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_piket` int(10) UNSIGNED NOT NULL,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_piket_siswa`),
  UNIQUE KEY `uq_piket_siswa` (`id_piket`,`id_siswa`),
  KEY `fk_piket_siswa_siswa` (`id_siswa`),
  CONSTRAINT `fk_piket_siswa_piket` FOREIGN KEY (`id_piket`) REFERENCES `jadwal_piket` (`id_piket`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_piket_siswa_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- catatan_kedisiplinan : jenis pelanggaran + tanggal + catatan.
-- Tanpa tingkat pelanggaran / tindak lanjut. [v9] Tidak dapat dihapus
-- (hanya POST dan PUT pada API); koreksi dilakukan lewat PUT.
-- --------------------------------------------------------
CREATE TABLE `catatan_kedisiplinan` (
  `id_catatan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_siswa` int(10) UNSIGNED NOT NULL,
  `id_guru` int(10) UNSIGNED NOT NULL,
  `id_kelas` smallint(5) UNSIGNED NOT NULL,
  `id_tahun_ajaran` smallint(5) UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_pelanggaran` varchar(100) NOT NULL,
  `catatan` text NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `diubah_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_catatan`),
  KEY `idx_disiplin_siswa` (`id_siswa`),
  KEY `fk_disiplin_guru` (`id_guru`),
  KEY `idx_disiplin_kelas_tahun` (`id_kelas`,`id_tahun_ajaran`),
  KEY `fk_disiplin_tahun` (`id_tahun_ajaran`),
  CONSTRAINT `fk_disiplin_siswa` FOREIGN KEY (`id_siswa`) REFERENCES `data_siswa` (`id_siswa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_disiplin_guru` FOREIGN KEY (`id_guru`) REFERENCES `data_guru` (`id_guru`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_disiplin_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_disiplin_tahun` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- pengaturan_sistem : key-value. Kunci minimal:
--   ppdb_form_url, video_profil_url  (hanya URL, bukan file video)
-- --------------------------------------------------------
CREATE TABLE `pengaturan_sistem` (
  `id_pengaturan` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kunci` varchar(50) NOT NULL,
  `nilai` text DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `diubah_pada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `id_user_pengubah` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_pengaturan`),
  UNIQUE KEY `uq_kunci` (`kunci`),
  KEY `fk_pengaturan_user` (`id_user_pengubah`),
  CONSTRAINT `fk_pengaturan_user` FOREIGN KEY (`id_user_pengubah`) REFERENCES `akun_pengguna` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pengaturan_sistem` (`kunci`, `nilai`, `keterangan`) VALUES
  ('ppdb_form_url',    NULL, 'URL/embed Google Form PPDB di website publik'),
  ('video_profil_url', NULL, 'URL embed video profil sekolah (mis. YouTube)');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =====================================================================
-- CATATAN LOGIN WALI MURID  (POST /api/wali/login  { nisn })
--   1. SELECT id_siswa FROM data_siswa WHERE nisn_resmi = :nisn
--      AND status_siswa = 'aktif'   -- 'baru','lulus','pindah' ditolak
--      AND EXISTS riwayat_kelas pada tahun_ajaran.status_aktif = 1
--   2. Valid  -> token read-only, scope KETAT ke id_siswa tersebut.
--   3. Tidak valid / lulus / pindah / tanpa penempatan -> 401 pesan generik
--      yang sama (tidak membocorkan apakah NISN ada).
--   4. WAJIB rate limit per IP dan per NISN (tanpa password).
--   Nomor absen BUKAN credential.
--
-- CATATAN KENAIKAN KELAS (POST /api/admin/kenaikan-kelas), satu transaksi:
--   Prasyarat: tahun ajaran tujuan, kelas, dan wali (akun berstatus aktif) sudah disiapkan
--   (tahun tujuan BELUM aktif). Pemeriksaan awal: bila ada siswa 'aktif' tanpa
--   riwayat_kelas pada tahun asal, seluruh proses ditolak (422). Siswa 'baru'
--   TIDAK diperiksa dan tidak memblokir proses.
--   Untuk tiap siswa aktif pada tahun asal:
--     tingkat 1-4 -> riwayat_kelas baru di satu-satunya kelas tingkat + 1
--                    pada tahun tujuan (otomatis; dua kelas tingkat 1 menuju
--                    satu kelas tingkat 2), log hasil_proses = 'naik'
--     tingkat 5   -> riwayat_kelas baru di kelas tingkat 6 tahun tujuan
--                    (otomatis bila hanya satu kelas; bila dua, Admin
--                    menetapkan kelas tujuan per siswa), log 'naik'
--     tingkat 6   -> data_siswa.status_siswa = 'lulus', tanpa riwayat baru,
--                    log hasil_proses = 'lulus', id_kelas_tujuan = NULL
--   Untuk tiap siswa 'baru' yang dipetakan Admin (penempatan_siswa_baru):
--     riwayat_kelas baru di kelas tujuan pilihan Admin (tingkat berapa pun)
--     pada tahun tujuan; status_siswa = 'aktif'; log hasil_proses = 'masuk',
--     id_tahun_asal = NULL, id_kelas_asal = NULL.
--     Siswa 'baru' yang tidak dipetakan tetap 'baru'.
--   Setelah selesai: PUT /api/admin/tahun-ajaran/{id}/aktifkan
--   Aktivasi pertama (belum ada tahun aktif) diizinkan tanpa kenaikan kelas;
--   data awal: aktifkan tahun ajaran, lalu tempatkan siswa lewat
--   /api/admin/riwayat-kelas (status otomatis menjadi 'aktif').
--
-- CATATAN PENEMPATAN LANGSUNG (POST /api/admin/riwayat-kelas)
--   Hanya ke tahun ajaran AKTIF. Siswa 'baru' -> menjadi 'aktif'. Siswa
--   'aktif' yang belum punya riwayat pada tahun tsb boleh ditempatkan.
--   Siswa 'lulus'/'pindah' ditolak (422). Penempatan 'baru' ke tahun yang
--   belum aktif hanya lewat proses kenaikan kelas.
--
-- CATATAN PENGHAPUSAN (URS BR-22)
--   Penghapusan data master yang masih terkait -> error FK -> HTTP 409.
--   Siswa: ubah status_siswa. Guru/Admin: ubah status_akun = 'nonaktif'.
--   Pengecualian hapus yang diizinkan: baris UH pada data_nilai (service).
--   Akun Admin pemroses kenaikan kelas tidak dapat dihapus (RESTRICT).
--   Akun Admin pada umumnya tidak dihapus sama sekali; nonaktifkan (v9).
--   catatan_kedisiplinan tidak dapat dihapus melalui API (v9).
--   Kelas yang masih menjadi target libur_target_kelas tidak dapat dihapus (service, 409; v2.6).
-- =====================================================================

-- =====================================================================
-- MIGRASI v8 -> v9 (untuk basis data v8 yang sudah berisi; BELUM diuji.
-- Cadangkan dulu. Jalankan satu per satu. Tidak perlu bila memakai skrip
-- ini pada basis data baru.)
--
-- ALTER TABLE `data_siswa`
--   MODIFY `status_siswa` enum('baru','aktif','lulus','pindah') NOT NULL DEFAULT 'baru';
--
-- ALTER TABLE `akun_pengguna`
--   ADD COLUMN `kata_sandi_diubah_pada` timestamp NULL DEFAULT NULL
--   COMMENT 'token dengan iat sebelum waktu ini ditolak' AFTER `status_akun`;
--
-- ALTER TABLE `log_promosi_siswa` DROP CHECK `chk_promosi_hasil`;
-- ALTER TABLE `log_promosi_siswa`
--   MODIFY `id_tahun_asal` smallint(5) UNSIGNED NULL,
--   MODIFY `id_kelas_asal` smallint(5) UNSIGNED NULL,
--   MODIFY `hasil_proses` enum('naik','lulus','masuk') NOT NULL;
-- ALTER TABLE `log_promosi_siswa` ADD CONSTRAINT `chk_promosi_hasil` CHECK (
--     (`hasil_proses` = 'naik'  AND `id_tahun_asal` IS NOT NULL AND `id_kelas_asal` IS NOT NULL AND `id_kelas_tujuan` IS NOT NULL)
--  OR (`hasil_proses` = 'lulus' AND `id_tahun_asal` IS NOT NULL AND `id_kelas_asal` IS NOT NULL AND `id_kelas_tujuan` IS NULL)
--  OR (`hasil_proses` = 'masuk' AND `id_tahun_asal` IS NULL AND `id_kelas_asal` IS NULL AND `id_kelas_tujuan` IS NOT NULL));
--
-- Data lama: siswa berstatus 'aktif' tetap 'aktif'. Hanya siswa yang belum
-- pernah ditempatkan yang sebaiknya diubah menjadi 'baru':
--   UPDATE data_siswa s SET status_siswa='baru'
--   WHERE status_siswa='aktif'
--     AND NOT EXISTS (SELECT 1 FROM riwayat_kelas r WHERE r.id_siswa = s.id_siswa);
-- =====================================================================
