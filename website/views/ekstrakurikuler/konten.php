<?php
/**
 * Konten TAMBAHAN ekstrakurikuler (tentang, kegiatan, materi, manfaat), dicocokkan dengan nama ekskul di database
 * (tidak peka huruf besar/kecil). Nama, deskripsi, dan gambar tetap dari database (diubah Admin di PWA Admin).
 * Ekskul yang tidak ada di sini tetap tampil dengan deskripsi dari database.
 * CATATAN: isi di bawah adalah CONTOH AWAL dari tim pengembang - sesuaikan dengan kegiatan sekolah atau hapus bagian yang tidak sesuai.
 */
$daftar = [
    [
        'nama'         => 'Tari',
        'ringkas'      => 'Wadah bagi siswa untuk melatih keluwesan gerak, keberanian tampil di depan umum, serta mengenalkan dan melestarikan kekayaan seni tari daerah sejak dini.',
        'tentang'      => 'Ekstrakurikuler Tari merupakan kegiatan pengembangan bakat dan minat siswa dalam bidang seni tari. Kegiatan ini menjadi wadah bagi siswa untuk mengenal, mempelajari, dan melestarikan berbagai macam seni tari serta mengembangkan kreativitas dan rasa percaya diri.',
        'kegiatan'     => ['Latihan Gerakan Tari', 'Pengenalan Musik Pengiring', 'Latihan Kostum & Properti', 'Persiapan Pentas'],
        'materi_judul' => 'Tari yang Dipelajari',
        'materi'       => [
            ['nama' => 'Tari Tradisional Jawa', 'deskripsi' => 'Siswa mempelajari berbagai gerakan dan teknik dasar tari tradisional dengan memperhatikan wiraga, wirama, dan wirasa.'],
        ],
        'manfaat'      => ['Meningkatkan kreativitas', 'Melatih kepercayaan diri', 'Mengenal dan melestarikan budaya', 'Melatih kedisiplinan dan kerja sama'],
    ],
    [
        'nama'         => 'Hadroh',
        'ringkas'      => 'Kegiatan seni musik Islami dalam menepuk irama terbang/rebana, melatih kekompakan tim, serta mempertebal rasa cinta kepada sholawat.',
        'tentang'      => 'Ekstrakurikuler Hadroh adalah kegiatan seni musik Islami yang menggunakan alat musik terbang atau rebana. Siswa belajar memainkan irama dan melantunkan sholawat secara bersama-sama.',
        'kegiatan'     => ['Latihan Memainkan Terbang/Rebana', 'Belajar Melantunkan Sholawat', 'Latihan Kekompakan Tim', 'Persiapan Penampilan'],
        'materi_judul' => 'Materi yang Dipelajari',
        'materi'       => [
            ['nama' => 'Dasar Pukulan Rebana', 'deskripsi' => 'Siswa mengenal jenis pukulan dasar dan cara menjaga tempo agar irama kompak.'],
            ['nama' => 'Sholawat', 'deskripsi' => 'Siswa belajar melantunkan sholawat dengan nada dan irama yang tepat.'],
        ],
        'manfaat'      => ['Melatih kekompakan dan kerja sama', 'Mempertebal kecintaan pada sholawat', 'Melatih rasa percaya diri', 'Mengasah kepekaan terhadap irama'],
    ],
    [
        'nama'         => 'Seni Rupa',
        'ringkas'      => 'Tempat anak-anak bebas berkreasi melalui gambar dan warna. Kegiatan ini melatih motorik halus, kerapian, konsentrasi, serta keberanian mengekspresikan imajinasi.',
        'tentang'      => 'Ekstrakurikuler Seni Rupa memberi ruang bagi siswa untuk mengungkapkan imajinasi lewat gambar dan warna, sekaligus melatih kesabaran dan ketelitian.',
        'kegiatan'     => ['Menggambar Bebas', 'Mewarnai dengan Berbagai Media', 'Membuat Kolase dan Kerajinan', 'Pameran Karya Siswa'],
        'materi_judul' => 'Materi yang Dipelajari',
        'materi'       => [
            ['nama' => 'Menggambar dan Mewarnai', 'deskripsi' => 'Siswa belajar komposisi, garis, dan perpaduan warna menggunakan pensil warna, krayon, dan cat.'],
        ],
        'manfaat'      => ['Melatih motorik halus', 'Mengembangkan imajinasi dan kreativitas', 'Melatih konsentrasi dan kerapian', 'Menumbuhkan keberanian mengekspresikan diri'],
    ],
    [
        'nama'         => 'Pramuka',
        'ringkas'      => 'Kegiatan wajib yang seru untuk melatih kedisiplinan, kemandirian, kerja sama tim, serta kepedulian terhadap lingkungan dan sesama melalui berbagai permainan edukatif.',
        'tentang'      => 'Pramuka adalah kegiatan kepanduan yang membentuk karakter siswa melalui permainan, latihan baris-berbaris, dan kegiatan di alam terbuka.',
        'kegiatan'     => ['Upacara dan Baris-Berbaris', 'Permainan Edukatif', 'Belajar Tali-Temali dan Sandi', 'Perkemahan'],
        'materi_judul' => 'Materi yang Dipelajari',
        'materi'       => [
            ['nama' => 'Keterampilan Kepramukaan', 'deskripsi' => 'Siswa mempelajari tali-temali, sandi, semaphore, dan dasar-dasar bertahan di alam terbuka.'],
        ],
        'manfaat'      => ['Melatih kedisiplinan dan kemandirian', 'Melatih kerja sama tim', 'Menumbuhkan kepedulian pada lingkungan', 'Membentuk karakter dan kepemimpinan'],
    ],
    [
        'nama'         => 'Menyanyi',
        'ringkas'      => 'Mengajarkan teknik dasar bernyanyi, mengolah pernapasan, serta melatih rasa percaya diri anak untuk berani menyanyikan lagu di atas panggung.',
        'tentang'      => 'Ekstrakurikuler Menyanyi melatih siswa bernyanyi dengan teknik yang benar dan berani tampil di depan banyak orang.',
        'kegiatan'     => ['Latihan Pernapasan dan Vokal', 'Belajar Lagu Wajib dan Daerah', 'Latihan Tampil di Panggung', 'Persiapan Lomba'],
        'materi_judul' => 'Lagu yang Dipelajari',
        'materi'       => [
            ['nama' => 'Lagu Anak dan Lagu Daerah', 'deskripsi' => 'Siswa belajar menyanyikan lagu dengan nada, tempo, dan ekspresi yang tepat.'],
        ],
        'manfaat'      => ['Melatih pernapasan dan teknik vokal', 'Menumbuhkan rasa percaya diri', 'Mengasah kepekaan musikal', 'Mempersiapkan siswa untuk lomba'],
    ],
    [
        'nama'         => 'Komputer',
        'ringkas'      => 'Mengenalkan dasar-dasar penggunaan komputer dan perangkat digital secara sehat, aman, dan menyenangkan untuk menunjang keterampilan belajar anak di era modern.',
        'tentang'      => 'Ekstrakurikuler Komputer mengenalkan siswa pada perangkat digital dan aplikasi dasar secara bertahap, sehat, dan aman.',
        'kegiatan'     => ['Mengenal Perangkat Komputer', 'Latihan Mengetik', 'Belajar Aplikasi Dasar', 'Etika dan Keamanan Internet'],
        'materi_judul' => 'Materi yang Dipelajari',
        'materi'       => [
            ['nama' => 'Aplikasi Perkantoran Dasar', 'deskripsi' => 'Siswa belajar membuat dokumen sederhana dan presentasi.'],
            ['nama' => 'Internet Sehat', 'deskripsi' => 'Siswa mengenal cara menggunakan internet dengan aman dan bertanggung jawab.'],
        ],
        'manfaat'      => ['Mengenal teknologi sejak dini', 'Melatih ketelitian dan logika', 'Menunjang tugas belajar', 'Terbiasa menggunakan internet dengan aman'],
    ],
];

$hasil = [];
foreach ($daftar as $e) { $hasil[strtolower(trim($e['nama']))] = $e; }
return $hasil;
