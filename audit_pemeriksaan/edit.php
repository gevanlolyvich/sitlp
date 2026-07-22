<?php
session_start();

require_once "../config/app.php"; 
require_once "../config/database.php"; 
require_once "../config/functions.php"; 
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_GET['id'];
$q = mysqli_query( $conn, "
	SELECT ap.*, uk.nama_unit, au.nama_auditor 
	FROM audit_pemeriksaan ap LEFT JOIN unit_kerja uk ON ap.unit_id=uk.id LEFT JOIN auditor au ON ap.ketua_auditor_id=au.id 
	WHERE ap.id='$id' " );
$audit = mysqli_fetch_assoc($q);
if(!$audit){ die('Data audit tidak ditemukan'); }

include "../templates/header.php"; 
include "../templates/navbar.php"; 
include "../templates/sidebar.php"; 

if($audit['status']=='SELESAI')
{
    die(
        'Audit sudah ditutup dan tidak dapat diubah.'
    );
}
?>


<main class="app-main"> <div class="app-content"> <div class="container-fluid">
<div class="card mt-3">
<div class="card-header"> <h3 class="card-title"> Edit Pemeriksaan Audit </h3> </div>
<form action="update.php" method="post">
<input type="hidden" name="id" value="<?= $audit['id'] ?>">
<div class="card-body">
<div class="row">
<div class="col-md-6"> <label>Nomor Audit</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nomor_audit']) ?>" readonly> </div>
<div class="col-md-6"> <label>Nomor Surat Tugas</label> <input type="text" name="nomor_surat_tugas"class="form-control" value="<?= htmlspecialchars($audit['nomor_surat_tugas']) ?>" required> </div>
</div>
<br>
<div class="row">

<div class="col-md-4"> <label>Unit Kerja</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nama_unit']) ?>" readonly> </div>
<div class="col-md-4"> <label>Jenis Audit</label>
<input type="hidden" name="jenis_audit" value="<?= htmlspecialchars($audit['jenis_audit']) ?>">
<?php
$displayJenis = $audit['jenis_audit'];
$mapJenis = [
    'OPERASIONAL'=>'Operasional|Keuangan|Kepatuhan','KEUANGAN'=>'Operasional|Keuangan|Kepatuhan',
    'KEPATUHAN'=>'Operasional|Keuangan|Kepatuhan','Operasional'=>'Operasional|Keuangan|Kepatuhan',
    'Keuangan'=>'Operasional|Keuangan|Kepatuhan','Kepatuhan'=>'Operasional|Keuangan|Kepatuhan',
    'VERIFIKASI'=>'Verifikasi','INVESTIGASI'=>'Investigasi','KHUSUS'=>'Khusus'
];
if (isset($mapJenis[$displayJenis])) $displayJenis = $mapJenis[$displayJenis];
?>
<input type="text" class="form-control" value="<?= htmlspecialchars($displayJenis) ?>" readonly>
</div>
<div class="col-md-4"> <label>Ketua Auditor</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nama_auditor']) ?>" readonly> </div>
</div>
<br>
<div class="row">
<div class="col-md-6"> <label>Tanggal Surat Tugas</label> <input type="date" name="tanggal_surat_tugas"class="form-control" value="<?= $audit['tanggal_surat_tugas'] ?>" required> </div>
<div class="col-md-6"> <label>Judul Audit</label> <input type="hidden" name="judul_audit" value="<?= htmlspecialchars($audit['judul_audit']) ?>"> <input type="text" class="form-control" value="<?= htmlspecialchars($audit['judul_audit']) ?>" readonly> </div>
</div>
<br>
<div class="row">
<div class="col-md-4"> <label>Tanggal Mulai Audit</label> <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?= $audit['tanggal_mulai'] ?>" required> </div>
<div class="col-md-4"> <label>Estimasi Hari</label> <input type="hidden" name="estimasi_hari" value="<?= $audit['estimasi_hari'] ?: 14 ?>"> <input type="number" id="estimasi_hari" value="<?= $audit['estimasi_hari'] ?: 14 ?>" class="form-control" readonly> </div>
<div class="col-md-4"> <label>Tanggal Selesai Audit</label> <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?= $audit['tanggal_selesai'] ?>" readonly> </div>
</div>
<br>
<label>Ruang Lingkup Audit</label>
<textarea name="ruang_lingkup" class="form-control" rows="5"><?=htmlspecialchars($audit['ruang_lingkup']) ?></textarea>
<br>
<label>Keterangan</label>

<textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($audit['keterangan']) ?></textarea>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
<a href="index.php" class="btn btn-secondary">Kembali</a>
</div>
</form>
</div>
</div> </div> </main>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tanggalMulai = document.getElementById('tanggal_mulai');
    var estimasiHari = document.getElementById('estimasi_hari');
    var tanggalSelesai = document.getElementById('tanggal_selesai');
    function hitungTanggalSelesai() {
        if (tanggalMulai.value && estimasiHari.value) {
            var start = new Date(tanggalMulai.value);
            var days = parseInt(estimasiHari.value) || 0;
            start.setDate(start.getDate() + days);
            var y = start.getFullYear();
            var m = String(start.getMonth() + 1).padStart(2, '0');
            var d = String(start.getDate()).padStart(2, '0');
            tanggalSelesai.value = y + '-' + m + '-' + d;
        }
    }
    tanggalMulai.addEventListener('change', hitungTanggalSelesai);
    estimasiHari.addEventListener('input', hitungTanggalSelesai);
});
</script>
<?php include "../templates/footer.php"; ?>

