<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int)$_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM audit_program WHERE id=$id");
$program = mysqli_fetch_assoc($q);
if(!$program) die("Program tidak ditemukan");
if($program['status'] != 'Selesai') die("Program belum selesai");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>
<main class="app-main">
  <div class="app-content">
    <div class="container-fluid">
   <div class="jxb-page-header">
    <div>
     <h1 class="jxb-page-title"><i class="fas fa-upload me-2 text-primary"></i>Upload LHA</h1>
     <div class="jxb-page-subtitle">Unggah Laporan Hasil Audit (LHA) untuk <?= htmlspecialchars($program['kode_program'] . ' - ' . $program['judul_program']) ?></div>
    </div>
    <div class="jxb-page-actions">
     <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
   </div>
   <div class="card mt-3">
    <form action="upload_lha_process.php" method="post" enctype="multipart/form-data">
     <input type="hidden" name="id" value="<?= $program['id'] ?>">
     <div class="card-body">
      <div class="mb-3">
       <label class="form-label">Program</label>
       <input type="text" class="form-control" value="<?= htmlspecialchars($program['kode_program'].' - '.$program['judul_program']) ?>" readonly>
      </div>
      <div class="mb-3">
       <label class="form-label">File LHA (PDF) <span class="jxb-required">*</span></label>
       <input type="file" name="lha_file" class="form-control" accept=".pdf" required>
       <small class="text-muted">Format file: PDF</small>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Upload</button>
      <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
     </div>
    </form>
    </div>
  </div>
</main>
<?php include "../templates/footer.php"; ?>
