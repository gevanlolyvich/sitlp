<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT * FROM audit_temuan WHERE id=$id");
$temuan = mysqli_fetch_assoc($q);

if(!$temuan){
    die("Temuan tidak ditemukan");
}

blockLockedAudit($conn, $temuan['audit_id']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="jxb-page-header">
    <div>
     <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Temuan Audit</h1>
     <div class="jxb-page-subtitle">Ubah data temuan <?= htmlspecialchars($temuan['nomor_temuan']) ?></div>
    </div>
    <div class="jxb-page-actions">
     <a href="index.php?audit_id=<?= $temuan['audit_id'] ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
   </div>
   <div class="card mt-3">
    <form action="update.php" method="post">
     <input type="hidden" name="id" value="<?= $temuan['id'] ?>">
     <input type="hidden" name="audit_id" value="<?= $temuan['audit_id'] ?>">
     <div class="card-body">
      <div class="row">
       <div class="col-md-6">
        <label class="form-label">Nomor Temuan <span class="jxb-required">*</span></label>
        <input type="text" name="nomor_temuan" value="<?= htmlspecialchars($temuan['nomor_temuan']) ?>" class="form-control" required>
       </div>
        <input type="hidden" name="status" value="Open">
        <div class="col-md-6">
         <label class="form-label">Status</label>
         <input type="text" class="form-control" value="Open" readonly>
        </div>
      </div>
      <br>
      <label class="form-label">Judul Temuan <span class="jxb-required">*</span></label>
      <input type="text" name="judul_temuan" value="<?= htmlspecialchars($temuan['judul_temuan']) ?>" class="form-control" required>
      <br>
      <label class="form-label">Kondisi <span class="jxb-required">*</span></label>
      <textarea name="kondisi" id="kondisi" class="form-control" rows="4" required><?= htmlspecialchars($temuan['kondisi']) ?></textarea>
      <br>
      <label class="form-label">Kriteria <span class="jxb-required">*</span></label>
      <textarea name="kriteria" class="form-control" rows="4" required><?= htmlspecialchars($temuan['kriteria']) ?></textarea>
      <br>
      <label class="form-label">Sebab <span class="jxb-required">*</span></label>
      <textarea name="sebab" class="form-control" rows="4" required><?= htmlspecialchars($temuan['sebab']) ?></textarea>
      <br>
      <label class="form-label">Akibat <span class="jxb-required">*</span></label>
      <textarea name="akibat" class="form-control" rows="4" required><?= htmlspecialchars($temuan['akibat']) ?></textarea>
      <br>
      <div class="row">
       <div class="col-md-4">
        <label class="form-label">Tingkat Risiko</label>
        <select name="tingkat_risiko" class="form-select">
         <option value="Rendah" <?= $temuan['tingkat_risiko']=='Rendah'?'selected':'' ?>>Rendah</option>
         <option value="Sedang" <?= $temuan['tingkat_risiko']=='Sedang'?'selected':'' ?>>Sedang</option>
         <option value="Tinggi" <?= $temuan['tingkat_risiko']=='Tinggi'?'selected':'' ?>>Tinggi</option>
        </select>
       </div>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="index.php?audit_id=<?= $temuan['audit_id'] ?>" class="btn btn-outline-secondary">Kembali</a>
     </div>
    </form>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
<script>
$(document).ready(function() {
    $('#kondisi').summernote({height:250});
});
</script>
