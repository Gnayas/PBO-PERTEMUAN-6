<?php
require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';

class DataMahasiswa {
    private const KEY = 'data_mahasiswa';

    public function __construct() {
        if (!isset($_SESSION[self::KEY])) $_SESSION[self::KEY] = [];
    }

    // parameter bertipe Mahasiswa: menerima Reguler maupun Beasiswa
    public function tambah(Mahasiswa $m): void {
        $nim = $m->getNim();
        if (isset($_SESSION[self::KEY][$nim])) {
            throw new InvalidArgumentException('NIM sudah terdaftar');
        }
        $_SESSION[self::KEY][$nim] = $m;
    }

    public function semua(): array { return $_SESSION[self::KEY]; }

    public function cari(string $nim): ?Mahasiswa {
        return $_SESSION[self::KEY][$nim] ?? null;
    }

    // jenis boleh berganti (Reguler <-> Beasiswa), NIM tidak boleh berubah
    public function ubah(string $nimLama, Mahasiswa $m): void {
        if (!isset($_SESSION[self::KEY][$nimLama])) {
            throw new InvalidArgumentException('Data mahasiswa tidak ditemukan');
        }
        if ($m->getNim() !== $nimLama) {
            throw new InvalidArgumentException('NIM tidak boleh diubah');
        }
        $_SESSION[self::KEY][$nimLama] = $m;
    }

    public function hapus(string $nim): void {
        if (!isset($_SESSION[self::KEY][$nim])) {
            throw new InvalidArgumentException('Data mahasiswa tidak ditemukan');
        }
        unset($_SESSION[self::KEY][$nim]);
    }
}
