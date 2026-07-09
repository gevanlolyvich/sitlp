<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$program_id = (int)$_GET['program_id'];

$q = mysqli_query($conn,"SELECT p.*, u.nama_unit, a.nama_auditor
	FROM audit_program p
	LEFT JOIN unit_kerja u ON p.unit_id=u.id
	LEFT JOIN auditor a ON p.penanggung_jawab_id=a.id
	WHERE p.id=$program_id");

$pat = mysqli_fetch_assoc($q);

if(!$pat)
{
    die("Program Audit tidak ditemukan");
}

$tahun = date('Y');
$q2 = mysqli_query($conn,"SELECT COUNT(*) total FROM audit_pemeriksaan WHERE tahun_audit='$tahun'");
$d = mysqli_fetch_assoc($q2);
$urut = $d['total'] + 1;

$nomor_audit = 'AUD-' . $tahun . '-' . str_pad($urut,3,'0',STR_PAD_LEFT);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Buat Pemeriksaan Audit</h3>
    </div>
    <div class="card-body">
     <form action="store.php" method="post">
      <input type="hidden" name="program_id" value="<?= $pat['id'] ?>">
      <input type="hidden" name="unit_id" value="<?= $pat['unit_id'] ?>">
      <input type="hidden" name="ketua_auditor_id" value="<?= $pat['penanggung_jawab_id'] ?>">
      <input type="hidden" name="tahun_audit" value="<?= $pat['tahun'] ?>">
      <input type="hidden" name="jenis_audit" value="<?= $pat['jenis_audit'] ?>">
      <input type="hidden" name="nomor_audit" value="<?= $nomor_audit ?>">
      <div class="card-body">
       <div class="row">
        <div class="col-md-6">
         <label>Nomor Audit</label>
         <input type="text" class="form-control" value="<?= $nomor_audit ?>" readonly>
        </div>
        <div class="col-md-6">
         <label>Nomor Surat Tugas</label>
         <input type="text" name="nomor_surat_tugas" class="form-control" required>
        </div>
       </div>
       <br>
       <div class="row">
        <div class="col-md-4">
         <label>Unit Kerja</label>
         <input type="text" class="form-control" value="<?= htmlspecialchars($pat['nama_unit']) ?>" readonly>
        </div>
        <div class="col-md-4">
         <label>Jenis Audit</label>
         <input type="text" class="form-control" value="<?= htmlspecialchars($pat['jenis_audit']) ?>" readonly>
        </div>
        <div class="col-md-4">
         <label>Ketua Auditor</label>
         <input type="text" class="form-control" value="<?= htmlspecialchars($pat['nama_auditor']) ?>" readonly>
        </div>
       </div>
       <br>
       <div class="row">
        <div class="col-md-6">
         <label>Tanggal Surat Tugas</label>
         <input type="date" name="tanggal_surat_tugas" class="form-control" required>
        </div>
        <div class="col-md-6">
         <label>Judul Audit</label>
         <input type="text" name="judul_audit" value="<?= htmlspecialchars($pat['judul_program']) ?>" class="form-control" required>
        </div>
       </div>
       <br>
       <div class="row">
        <div class="col-md-6">
         <label>Tanggal Mulai</label>
         <input type="date" name="tanggal_mulai" class="form-control" required>
        </div>
        <div class="col-md-6">
         <label>Tanggal Selesai</label>
         <input type="date" name="tanggal_selesai" class="form-control" required>
        </div>
       </div>
       <br>
       <label>Ruang Lingkup Audit</label>
       <textarea name="ruang_lingkup" class="form-control" rows="4"></textarea>
      </div>
      <div class="card-footer">
       <button type="submit" class="btn btn-primary">Simpan Audit</button>
       <a href="../audit_program/index.php" class="btn btn-secondary">Kembali</a>
      </div>
     </form> 
    </div>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
