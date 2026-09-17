<?php
session_start();

require_once "../config/app.php"; 
require_once "../config/database.php"; 
require_once "../config/functions.php"; 
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$id = (int)$_GET['id'];
$q = mysqli_query( $conn, "
	SELECT ap.*, uk.nama_unit, au.nama_auditor 
	FROM audit_pemeriksaan ap LEFT JOIN unit_kerja uk ON ap.unit_id=uk.id LEFT JOIN auditor au ON ap.ketua_auditor_id=au.id 
	WHERE ap.id='$id' " );
$audit = mysqli_fetch_assoc($q);
if(!$audit){ die('Data audit tidak ditemukan'); }

$qLibur = mysqli_query($conn,"SELECT tanggal FROM hari_libur");
$hariLibur = [];
while ($r = mysqli_fetch_assoc($qLibur)) {
    $hariLibur[] = $r['tanggal'];
}

include "../templates/header.php"; 
include "../templates/navbar.php"; 
include "../templates/sidebar.php"; 

if($audit['status']=='Selesai')
{
    die(
        'Audit sudah ditutup dan tidak dapat diubah.'
    );
}
?>


<main class="app-main"> <div class="app-content"> <div class="container-fluid">
<div class="jxb-page-header">
<div>
 <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Pemeriksaan Audit</h1>
 <div class="jxb-page-subtitle">Ubah data pemeriksaan audit <?= htmlspecialchars($audit['nomor_audit']) ?></div>
</div>
<div class="jxb-page-actions">
 <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>
</div>
<div class="card mt-3">
<form action="update.php" method="post">
<input type="hidden" name="id" value="<?= $audit['id'] ?>">
<div class="card-body">
<div class="row">
<div class="col-md-6"> <label class="form-label">Nomor Audit</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nomor_audit']) ?>" readonly> </div>
<div class="col-md-6"> <label class="form-label">Nomor Surat Tugas <span class="jxb-required">*</span></label> <input type="text" name="nomor_surat_tugas"class="form-control" value="<?= htmlspecialchars($audit['nomor_surat_tugas']) ?>" required> </div>
</div>
<br>
<div class="row">

<div class="col-md-4"> <label class="form-label">Unit Kerja</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nama_unit']) ?>" readonly> </div>
<div class="col-md-4"> <label class="form-label">Jenis Audit</label>
<input type="hidden" name="jenis_audit" value="<?= htmlspecialchars($audit['jenis_audit']) ?>">
<?php
$displayJenis = $audit['jenis_audit'];
$mapJenis = [
    'OPERASIONAL'=>'Operasional','Operasional'=>'Operasional',
    'KEUANGAN'=>'Keuangan','Keuangan'=>'Keuangan',
    'KEPATUHAN'=>'Kepatuhan','Kepatuhan'=>'Kepatuhan',
    'VERIFIKASI'=>'Verifikasi','INVESTIGASI'=>'Investigasi','KHUSUS'=>'Khusus'
];
if (isset($mapJenis[$displayJenis])) $displayJenis = $mapJenis[$displayJenis];
?>
<input type="text" class="form-control" value="<?= htmlspecialchars($displayJenis) ?>" readonly>
</div>
<div class="col-md-4"> <label class="form-label">Ketua Auditor</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nama_auditor']) ?>" readonly> </div>
</div>
<br>
<div class="row">
<div class="col-md-6"> <label class="form-label">Tanggal Surat Tugas <span class="jxb-required">*</span></label> <input type="date" name="tanggal_surat_tugas"class="form-control" value="<?= $audit['tanggal_surat_tugas'] ?>" required> </div>
<div class="col-md-6"> <label class="form-label">Judul Audit</label> <input type="hidden" name="judul_audit" value="<?= htmlspecialchars($audit['judul_audit']) ?>"> <input type="text" class="form-control" value="<?= htmlspecialchars($audit['judul_audit']) ?>" readonly> </div>
</div>
<br>
<div class="row">
<div class="col-md-4"> <label class="form-label">Tanggal Mulai Audit <span class="jxb-required">*</span></label> <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?= $audit['tanggal_mulai'] ?>" required> </div>
<div class="col-md-4"> <label class="form-label">Estimasi Hari</label> <input type="hidden" name="estimasi_hari" value="<?= $audit['estimasi_hari'] ?: 14 ?>"> <input type="number" id="estimasi_hari" value="<?= $audit['estimasi_hari'] ?: 14 ?>" class="form-control" readonly> </div>
<div class="col-md-4"> <label class="form-label">Tanggal Selesai Audit</label> <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?= $audit['tanggal_selesai'] ?>" readonly> </div>
</div>
<br>
<label class="form-label">Ruang Lingkup Audit</label>
<textarea name="ruang_lingkup" class="form-control" rows="5"><?=htmlspecialchars($audit['ruang_lingkup']) ?></textarea>
<br>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
<a href="index.php" class="btn btn-outline-secondary">Kembali</a>
</div>
</form>
</div>
</div> </div> </main>
<script>
var HARI_LIBUR = <?= json_encode($hariLibur) ?>;
document.addEventListener('DOMContentLoaded', function() {
    var tanggalMulai = document.getElementById('tanggal_mulai');
    var estimasiHari = document.getElementById('estimasi_hari');
    var tanggalSelesai = document.getElementById('tanggal_selesai');
    function isHariKerja(tanggal) {
        var d = new Date(tanggal + 'T00:00:00');
        if (d.getDay() === 0 || d.getDay() === 6) return false;
        return HARI_LIBUR.indexOf(tanggal) === -1;
    }
    function hitungTanggalSelesai() {
        if (!tanggalMulai.value || !estimasiHari.value) return;
        var start = new Date(tanggalMulai.value + 'T00:00:00');
        var days = parseInt(estimasiHari.value) || 0;
        var counted = 0;
        var s = '';
        while (counted < days) {
            var y = start.getFullYear();
            var m = String(start.getMonth() + 1).padStart(2, '0');
            var d = String(start.getDate()).padStart(2, '0');
            s = y + '-' + m + '-' + d;
            if (isHariKerja(s)) {
                counted++;
                if (counted >= days) break;
            }
            start.setDate(start.getDate() + 1);
        }
        tanggalSelesai.value = s;
    }
    tanggalMulai.addEventListener('change', function() {
        if (tanggalMulai.value && !isHariKerja(tanggalMulai.value)) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal mulai tidak boleh jatuh pada akhir pekan atau hari libur.'
            });
            tanggalMulai.value = '';
            tanggalSelesai.value = '';
            return;
        }
        hitungTanggalSelesai();
    });
    estimasiHari.addEventListener('input', hitungTanggalSelesai);
});
</script>
<?php include "../templates/footer.php"; ?>

