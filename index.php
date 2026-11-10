<?php
require_once __DIR__ . '/DataMahasiswa.php';   // class dimuat sebelum session_start()
session_start();

function aman(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Membuat object child sesuai jenis; kode identitas tidak diulang per child
function buatMahasiswa(array $p): Mahasiswa {
    $semester = filter_var($p['semester'] ?? '', FILTER_VALIDATE_INT);
    $ukt      = filter_var($p['ukt'] ?? '', FILTER_VALIDATE_INT);
    if ($semester === false) throw new InvalidArgumentException('Semester harus berupa angka bulat');
    if ($ukt === false)      throw new InvalidArgumentException('UKT harus berupa angka bulat');

    $nim      = (string)($p['nim'] ?? '');
    $nama     = (string)($p['nama'] ?? '');
    $fakultas = (string)($p['fakultas'] ?? '');
    $prodi    = (string)($p['prodi'] ?? '');
    $asal     = (string)($p['asal_sekolah'] ?? '');
    $hobi     = (string)($p['hobi'] ?? '');
    $agama    = (string)($p['agama'] ?? '');

    $jenis = (string)($p['jenis'] ?? '');
    if ($jenis === 'reguler') {
        return new MahasiswaReguler($nim, $nama, $fakultas, $prodi, $asal,
                                    $semester, $hobi, $agama, $ukt);
    }
    if ($jenis === 'beasiswa') {
        $potongan = filter_var($p['potongan'] ?? '', FILTER_VALIDATE_INT);
        if ($potongan === false) throw new InvalidArgumentException('Potongan harus berupa angka bulat');
        return new MahasiswaBeasiswa($nim, $nama, $fakultas, $prodi, $asal,
                                     $semester, $hobi, $agama, $ukt, $potongan);
    }
    throw new InvalidArgumentException('Jenis mahasiswa tidak dikenal');
}

$repo   = new DataMahasiswa();
$error  = '';
$sukses = $_SESSION['pesan'] ?? '';
unset($_SESSION['pesan']);
$aksi   = (string)($_POST['aksi'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($aksi === 'tambah') {
            $repo->tambah(buatMahasiswa($_POST));
            $_SESSION['pesan'] = 'Data mahasiswa berhasil ditambahkan';
        } elseif ($aksi === 'ubah') {
            // object baru dibuat dulu; disimpan hanya jika semua validasi lolos
            $repo->ubah((string)($_POST['nim_lama'] ?? ''), buatMahasiswa($_POST));
            $_SESSION['pesan'] = 'Data mahasiswa berhasil diperbarui';
        } elseif ($aksi === 'hapus') {
            $repo->hapus(trim((string)($_POST['nim'] ?? '')));
            $_SESSION['pesan'] = 'Data mahasiswa berhasil dihapus';
        }
        if ($aksi !== '') { header('Location: index.php'); exit; }
    } catch (InvalidArgumentException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// nilai awal form
$f = ['nim' => '', 'nama' => '', 'fakultas' => '', 'prodi' => '', 'asal_sekolah' => '',
      'semester' => '', 'hobi' => '', 'agama' => '', 'jenis' => 'reguler', 'ukt' => '', 'potongan' => '0'];
$nimLama = '';

$edit = $repo->cari(trim((string)($_GET['edit'] ?? '')));
if ($edit !== null) {
    $nimLama = $edit->getNim();
    $f = ['nim' => $edit->getNim(), 'nama' => $edit->getNama(), 'fakultas' => $edit->getFakultas(),
          'prodi' => $edit->getProdi(), 'asal_sekolah' => $edit->getAsalSekolah(),
          'semester' => $edit->getSemester(), 'hobi' => $edit->getHobi(), 'agama' => $edit->getAgama(),
          'jenis' => $edit instanceof MahasiswaBeasiswa ? 'beasiswa' : 'reguler',
          'ukt' => $edit->getUkt(),
          'potongan' => $edit instanceof MahasiswaBeasiswa ? $edit->getPotonganPersen() : 0];
}
if ($error !== '') {
    $f = array_merge($f, array_intersect_key($_POST, $f));
    if ($aksi === 'ubah') $nimLama = (string)($_POST['nim_lama'] ?? '');
}
$modeEdit = $nimLama !== '';

// pencarian berdasarkan NIM atau nama
$q = trim((string)($_GET['q'] ?? ''));
$tampil = array_filter($repo->semua(), function (Mahasiswa $m) use ($q) {
    return $q === '' || stripos($m->getNim(), $q) !== false || stripos($m->getNama(), $q) !== false;
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>CRUD Data Mahasiswa (Inheritance)</title>
<style>
  body { font-family: Arial, sans-serif; background: #f5f6fa; margin: 0; padding: 24px; color: #222; }
  .wrap { max-width: 1100px; margin: 0 auto; }
  h1, h2 { text-align: center; }
  .card { background: #fff; border: 1px solid #dde; border-radius: 8px; padding: 16px 24px; max-width: 520px; margin: 0 auto 20px; }
  label { display: block; font-weight: bold; margin-top: 10px; }
  input, select { width: 100%; padding: 8px; box-sizing: border-box; margin-top: 4px; }
  .msg-ok  { background: #dff5e3; color: #1d6b2f; padding: 8px 12px; max-width: 520px; margin: 0 auto 12px; }
  .msg-err { background: #fde2e2; color: #b02020; padding: 8px 12px; max-width: 520px; margin: 0 auto 12px; }
  table { border-collapse: collapse; width: 100%; font-size: 13px; background: #fff; }
  th { background: #1f3a5f; color: #fff; padding: 8px; }
  td { border-bottom: 1px solid #ddd; padding: 8px; }
  .center { text-align: center; }
  .inline { display: inline; }
</style>
</head>
<body>
<div class="wrap">
<h1>CRUD Data Mahasiswa (Inheritance)</h1>

<?php if ($sukses !== ''): ?><div class="msg-ok"><?= aman($sukses) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="msg-err"><?= aman($error) ?></div><?php endif; ?>

<div class="card">
  <h2><?= $modeEdit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h2>
  <form method="post" action="index.php">
    <input type="hidden" name="aksi" value="<?= $modeEdit ? 'ubah' : 'tambah' ?>">
    <input type="hidden" name="nim_lama" value="<?= aman($nimLama) ?>">

    <label>NIM</label>
    <input type="text" name="nim" value="<?= aman($f['nim']) ?>" <?= $modeEdit ? 'readonly' : '' ?>>
    <label>Nama</label>
    <input type="text" name="nama" value="<?= aman($f['nama']) ?>">
    <label>Fakultas</label>
    <input type="text" name="fakultas" value="<?= aman($f['fakultas']) ?>">
    <label>Prodi</label>
    <input type="text" name="prodi" value="<?= aman($f['prodi']) ?>">
    <label>Asal Sekolah</label>
    <input type="text" name="asal_sekolah" value="<?= aman($f['asal_sekolah']) ?>">
    <label>Semester</label>
    <input type="text" name="semester" value="<?= aman($f['semester']) ?>">
    <label>Hobi</label>
    <input type="text" name="hobi" value="<?= aman($f['hobi']) ?>">
    <label>Agama</label>
    <select name="agama">
      <option value="">-- pilih --</option>
      <?php foreach (Mahasiswa::AGAMA_VALID as $a): ?>
        <option value="<?= aman($a) ?>" <?= $f['agama'] === $a ? 'selected' : '' ?>><?= aman($a) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Jenis Mahasiswa</label>
    <select name="jenis" id="jenis" onchange="togglePotongan()">
      <option value="reguler"  <?= $f['jenis'] === 'reguler'  ? 'selected' : '' ?>>Reguler</option>
      <option value="beasiswa" <?= $f['jenis'] === 'beasiswa' ? 'selected' : '' ?>>Beasiswa</option>
    </select>
    <label>UKT (Rp)</label>
    <input type="text" name="ukt" value="<?= aman($f['ukt']) ?>">
    <div id="boxPotongan">
      <label>Potongan Beasiswa (%)</label>
      <input type="text" name="potongan" value="<?= aman($f['potongan']) ?>">
    </div>

    <p class="center">
      <button type="submit"><?= $modeEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
      <?php if ($modeEdit): ?><a href="index.php">Batal</a><?php endif; ?>
    </p>
  </form>
</div>

<h2>Daftar Mahasiswa</h2>
<form method="get" action="index.php" class="center" style="margin-bottom:10px">
  <input type="text" name="q" placeholder="Cari NIM / nama" value="<?= aman($q) ?>" style="width:240px">
  <button type="submit">Cari</button>
  <?php if ($q !== ''): ?><a href="index.php">Reset</a><?php endif; ?>
</form>

<table>
  <tr><th>NIM</th><th>Nama</th><th>Fakultas</th><th>Prodi</th><th>Asal Sekolah</th>
      <th>Semester</th><th>Hobi</th><th>Agama</th><th>Jenis</th><th>Biaya Akhir (Rp)</th><th>Aksi</th></tr>
  <?php foreach ($tampil as $m): ?>
  <tr>
    <td><?= aman($m->getNim()) ?></td>
    <td><?= aman($m->getNama()) ?></td>
    <td><?= aman($m->getFakultas()) ?></td>
    <td><?= aman($m->getProdi()) ?></td>
    <td><?= aman($m->getAsalSekolah()) ?></td>
    <td><?= aman($m->getSemester()) ?></td>
    <td><?= aman($m->getHobi()) ?></td>
    <td><?= aman($m->getAgama()) ?></td>
    <td><?= aman($m->getJenis()) ?></td>
    <td><?= aman(number_format($m->hitungBiaya(), 0, ',', '.')) ?></td>
    <td>
      <a href="index.php?edit=<?= urlencode($m->getNim()) ?>">Edit</a>
      <form method="post" action="index.php" class="inline" onsubmit="return confirm('Hapus data ini?')">
        <input type="hidden" name="aksi" value="hapus">
        <input type="hidden" name="nim" value="<?= aman($m->getNim()) ?>">
        <button type="submit">Hapus</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (count($tampil) === 0): ?><tr><td colspan="11" class="center">Belum ada data.</td></tr><?php endif; ?>
</table>
</div>

<script>
function togglePotongan() {
  document.getElementById('boxPotongan').style.display =
    document.getElementById('jenis').value === 'beasiswa' ? 'block' : 'none';
}
togglePotongan();
</script>
</body>
</html>
