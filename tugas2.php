<?php
declare(strict_types=1);
 
abstract class Pegawai {
    public function __construct(private string $id, private string $nama) {
        if (trim($id) === '' || trim($nama) === '') {
            throw new InvalidArgumentException('ID dan nama wajib diisi');
        }
    }
    public function getId(): string { return $this->id; }
    public function getNama(): string { return $this->nama; }
 
    abstract public function hitungGaji(): int;
    abstract public function getJenis(): string;
}
 
final class PegawaiTetap extends Pegawai {
    public function __construct(string $id, string $nama, private int $gajiPokok) {
        parent::__construct($id, $nama);
        if ($gajiPokok < 0) throw new InvalidArgumentException('Gaji pokok tidak boleh negatif');
    }
    public function hitungGaji(): int { return $this->gajiPokok; }
    public function getJenis(): string { return 'Tetap'; }
}
 
final class PegawaiKontrak extends Pegawai {
    public function __construct(string $id, string $nama,
                                private int $upahPerJam, private int $jamKerja) {
        parent::__construct($id, $nama);
        if ($upahPerJam < 0 || $jamKerja < 0) {
            throw new InvalidArgumentException('Upah/jam kerja tidak boleh negatif');
        }
    }
    public function hitungGaji(): int { return $this->upahPerJam * $this->jamKerja; }
    public function getJenis(): string { return 'Kontrak'; }
}
 
$daftar = [
    new PegawaiTetap('P001', 'Budi', 6000000),
    new PegawaiKontrak('P002', 'Rina', 40000, 100),
];
foreach ($daftar as $p) {
    echo $p->getId() . ' / ' . $p->getNama() . ' / ' . $p->getJenis()
       . ' / Rp' . number_format($p->hitungGaji(), 0, ',', '.') . PHP_EOL;
}
