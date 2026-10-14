<?php
declare(strict_types=1);

namespace App\Validators;

use App\Core\HttpException;
use App\Helpers\Validasi;

final class AbsensiValidator
{
    public const STATUS = ['hadir', 'sakit', 'izin', 'alpa'];

    /**
     * Body batch: {"tanggal":"YYYY-MM-DD","items":[{"id_siswa":1,"status":"hadir"}, ...]}
     * @return array{0:?string, 1:array<int, array{id_siswa:int, status:string}>}
     */
    public static function batch(Validasi $v, array $in): array
    {
        $tanggal = $v->tanggal('tanggal', 'Tanggal', true);
        $items   = $in['items'] ?? null;
        $hasil   = [];

        if (!is_array($items) || !$items) {
            $v->error('items', 'Daftar absensi (items) wajib diisi.');
            return [$tanggal, $hasil];
        }
        if (count($items) > 100) {
            $v->error('items', 'Maksimal 100 siswa per permintaan.');
            return [$tanggal, $hasil];
        }

        $terlihat = [];
        foreach (array_values($items) as $i => $it) {
            $kunci = 'items[' . $i . ']';
            if (!is_array($it)) {
                $v->error($kunci, 'Setiap item harus berupa objek {id_siswa, status}.');
                continue;
            }
            $id = $it['id_siswa'] ?? null;
            if (is_string($id) && ctype_digit($id)) {
                $id = (int) $id;
            }
            if (!is_int($id) || $id < 1) {
                $v->error($kunci . '.id_siswa', 'id_siswa harus berupa angka positif.');
                continue;
            }
            $status = $it['status'] ?? null;
            if (!is_string($status) || !in_array($status, self::STATUS, true)) {
                $v->error($kunci . '.status', 'Status harus salah satu dari: ' . implode(', ', self::STATUS) . '.');
                continue;
            }
            if (isset($terlihat[$id])) {
                $v->error($kunci . '.id_siswa', 'Siswa ' . $id . ' muncul lebih dari sekali.');
                continue;
            }
            $terlihat[$id] = true;
            $hasil[] = ['id_siswa' => $id, 'status' => $status];
        }
        return [$tanggal, $hasil];
    }

    public static function status(Validasi $v): ?string
    {
        return $v->enum('status', 'Status', self::STATUS, true);
    }

    /** Parameter ?date=YYYY-MM-DD. Kosong = hari ini (zona waktu aplikasi). Salah format = 400. */
    public static function tanggalQuery(mixed $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return date('Y-m-d');
        }
        if (!is_string($nilai) || !Validasi::adalahTanggal($nilai)) {
            throw HttpException::badRequest('Parameter date harus berformat YYYY-MM-DD.');
        }
        return $nilai;
    }
}
