<?php
session_start();

require_once "../config/app.php"; 
require_once "../config/database.php"; 
require_once "../config/functions.php"; 
require_once "../auth/check.php";

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
<div class="col-md-4"> <label>Jenis Audit</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['jenis_audit']) ?>" readonly> </div>
<div class="col-md-4"> <label>Ketua Auditor</label> <input type="text" class="form-control" value="<?=htmlspecialchars($audit['nama_auditor']) ?>" readonly> </div>
</div>
<br>
<div class="row">
<div class="col-md-6"> <label>Tanggal Surat Tugas</label> <input type="date" name="tanggal_surat_tugas"class="form-control" value="<?= $audit['tanggal_surat_tugas'] ?>" required> </div>
<div class="col-md-6"> <label>Judul Audit</label> <input type="text" name="judul_audit" class="form-control" value="<?= htmlspecialchars($audit['judul_audit']) ?>" required> </div>
</div>
<br>
<div class="row">
<div class="col-md-6"> <label>Tanggal Mulai</label> <input type="date" name="tanggal_mulai" class="form-control" value="<?= $audit['tanggal_mulai'] ?>" required> </div>
<div class="col-md-6"> <label>Tanggal Selesai</label> <input type="date" name="tanggal_selesai"class="form-control" value="<?= $audit['tanggal_selesai'] ?>" required> </div>
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
<?php include "../templates/footer.php"; ?>

