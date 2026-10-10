<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\SqlHelper;
use PDO;

/** Foto kegiatan: terkait event (id_event) atau mandiri (id_event NULL, mis. foto fasilitas). */
final class GaleriKegiatanRepository
{
    private const SELECT = 'SELECT id_galeri, id_event, file_gambar, keterangan FROM galeri_kegiatan';

    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /** Admin. $idEvent: angka = galeri event itu; $mandiri true = hanya id_event NULL. */
    public function daftar(?int $idEvent, bool $mandiri, int $limit, int $offset): array
    {
        [$where, $params] = $this->filter($idEvent, $mandiri);
        $st = $this->db->prepare(self::SELECT . $where . ' ORDER BY id_galeri DESC LIMIT ? OFFSET ?');
        $i = 1;
        foreach ($params as $p) {
            $st->bindValue($i++, $p, PDO::PARAM_INT);
        }
        $st->bindValue($i++, $limit, PDO::PARAM_INT);
        $st->bindValue($i, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map([$this, 'rapikan'], $st->fetchAll());
    }

    public function total(?int $idEvent, bool $mandiri): int
    {
        [$where, $params] = $this->filter($idEvent, $mandiri);
        $st = $this->db->prepare('SELECT COUNT(*) FROM galeri_kegiatan' . $where);
        foreach ($params as $i => $p) {
            $st->bindValue($i + 1, $p, PDO::PARAM_INT);
        }
        $st->execute();
        return (int) $st->fetchColumn();
    }

    /** Publik: hanya galeri mandiri (event membawa galerinya sendiri; tidak tumpang tindih). */
    public function mandiriPublik(int $limit, int $offset): array
    {
        $st = $this->db->prepare('SELECT id_galeri, file_gambar, keterangan FROM galeri_kegiatan WHERE id_event IS NULL ORDER BY id_galeri DESC LIMIT ? OFFSET ?');
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->bindValue(2, $offset, PDO::PARAM_INT);
        $st->execute();
        return array_map(static function (array $r): array {
            $r['id_galeri'] = (int) $r['id_galeri'];
            return $r;
        }, $st->fetchAll());
    }

    public function totalMandiri(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM galeri_kegiatan WHERE id_event IS NULL')->fetchColumn();
    }

    /** @param int[] $idEvent @return array<int, array> id_event => daftar foto */
    public function untukEvent(array $idEvent): array
    {
        if (!$idEvent) {
            return [];
        }
        $in = implode(',', array_fill(0, count($idEvent), '?'));
        $st = $this->db->prepare("SELECT id_galeri, id_event, file_gambar, keterangan FROM galeri_kegiatan WHERE id_event IN ($in) ORDER BY id_galeri");
        $st->execute(array_values($idEvent));
        $hasil = [];
        foreach ($st->fetchAll() as $r) {
            $hasil[(int) $r['id_event']][] = ['id_galeri' => (int) $r['id_galeri'], 'file_gambar' => $r['file_gambar'], 'keterangan' => $r['keterangan']];
        }
        return $hasil;
    }

    public function cari(int $id): ?array
    {
        $st = $this->db->prepare(self::SELECT . ' WHERE id_galeri = ?');
        $st->execute([$id]);
        $r = $st->fetch();
        return $r === false ? null : $this->rapikan($r);
    }

    /** id_event tidak ada => FK 1452 => 422. */
    public function buat(?int $idEvent, string $file, ?string $keterangan): int
    {
        $st = $this->db->prepare('INSERT INTO galeri_kegiatan (id_event, file_gambar, keterangan) VALUES (?, ?, ?)');
        $st->execute([$idEvent, $file, $keterangan]);
        return (int) $this->db->lastInsertId();
    }

    public function ubah(int $id, array $changes): void
    {
        SqlHelper::update($this->db, 'galeri_kegiatan', 'id_galeri', $id, $changes, ['id_event', 'file_gambar', 'keterangan']);
    }

    public function hapus(int $id): void
    {
        $st = $this->db->prepare('DELETE FROM galeri_kegiatan WHERE id_galeri = ?');
        $st->execute([$id]);
    }

    private function filter(?int $idEvent, bool $mandiri): array
    {
        if ($idEvent !== null) {
            return [' WHERE id_event = ?', [$idEvent]];
        }
        return [$mandiri ? ' WHERE id_event IS NULL' : '', []];
    }

    private function rapikan(array $r): array
    {
        $r['id_galeri'] = (int) $r['id_galeri'];
        $r['id_event']  = $r['id_event'] === null ? null : (int) $r['id_event'];
        return $r;
    }
}
