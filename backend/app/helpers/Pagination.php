<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Core\HttpException;
use App\Core\Request;

final class Pagination
{
    /** @return array{page:int, per_page:int, limit:int, offset:int} dari ?page= dan ?per_page= */
    public static function dari(Request $req, int $default = 50, int $max = 200): array
    {
        $page    = max(1, (int) $req->query('page', 1));
        $perPage = min($max, max(1, (int) $req->query('per_page', $default)));
        return ['page' => $page, 'per_page' => $perPage, 'limit' => $perPage, 'offset' => ($page - 1) * $perPage];
    }

    public static function hasil(array $items, int $total, array $p): array
    {
        return ['items' => $items, 'page' => $p['page'], 'per_page' => $p['per_page'], 'total' => $total];
    }

    /** Parameter query berupa angka (mis. ?id_kelas=3). Tidak ada => null; bukan angka => 400. */
    public static function angka(Request $req, string $nama): ?int
    {
        $v = $req->query($nama);
        if ($v === null || $v === '') {
            return null;
        }
        if (!is_string($v) || !ctype_digit($v)) {
            throw HttpException::badRequest('Parameter ' . $nama . ' harus berupa angka.');
        }
        return (int) $v;
    }

    /** Teks untuk LIKE: karakter % dan _ dinetralkan agar tidak menjadi wildcard dari input pengguna. */
    public static function like(string $q): string
    {
        return '%' . addcslashes($q, '%_\\') . '%';
    }
}
