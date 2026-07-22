<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR','AUDITEE']);

$rekomendasi_id = (int)$_GET['rekomendasi_id'];
$qRek = mysqli_query($conn,"SELECT r.*, t.audit_id FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=$rekomendasi_id");
$rek = mysqli_fetch_assoc($qRek);
if(!$rek){die("Rekomendasi tidak ditemukan");}

// Get audit end date for max target validation
$audit_id = (int)$rek['audit_id'];
$qAudit = mysqli_query($conn,"SELECT tanggal_selesai FROM audit_pemeriksaan WHERE id=$audit_id");
$auditData = mysqli_fetch_assoc($qAudit);
$tanggal_selesai_audit = $auditData['tanggal_selesai'];

$selected_unit_id = isset($_SESSION['unit_id']) ? (int)$_SESSION['unit_id'] : 0;
$qUnit = mysqli_query($conn,"SELECT * FROM unit_kerja ORDER BY nama_unit");

// Generate nomor TL
$qCount = mysqli_query($conn,"SELECT COUNT(*) total FROM audit_tindak_lanjut WHERE rekomendasi_id=$rekomendasi_id");
$d = mysqli_fetch_assoc($qCount);
$urut = $d['total'] + 1;
$nomor_tl = 'TL-' . substr($rek['nomor_rekomendasi'],3) . '-' . str_pad($urut,3,'0',STR_PAD_LEFT);

$isAuditee = ($_SESSION['role'] == 'AUDITEE');

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Tambah Tindak Lanjut</h3>
    </div>
    <form action="store.php" method="post">
     <input type="hidden" name="rekomendasi_id" value="<?= $rekomendasi_id ?>">
      <div class="card-body">
       <div class="mb-3">
        <label>Nomor Tindak Lanjut</label>
        <input type="text" class="form-control" value="<?= $nomor_tl ?>" readonly>
        <input type="hidden" name="nomor_tindak_lanjut" value="<?= $nomor_tl ?>">
       </div>
       <div class="mb-3">
        <label>Unit Kerja</label>
        <select name="unit_id" class="form-select" <?= $isAuditee ? 'disabled' : '' ?> required>
	<option value="">Pilih Unit</option>
	<?php
	while($u=mysqli_fetch_assoc($qUnit)){
	?>
	<option value="<?= $u['id'] ?>" <?= $u['id']==$selected_unit_id?'selected':'' ?>><?= htmlspecialchars($u['nama_unit']) ?></option>
	<?php } ?>
        </select>
        <?php if($isAuditee): ?>
        <input type="hidden" name="unit_id" value="<?= $selected_unit_id ?>">
        <?php endif; ?>
      </div>
      <div class="mb-3">
       <label>PIC</label>
       <input type="text" name="pic" class="form-control" required>
      </div>
      <div class="mb-3">
       <label>Uraian Tindak Lanjut</label>
       <textarea name="uraian_tindak_lanjut" class="form-control" rows="5" required></textarea>
      </div>
      <div class="row">
       <div class="col-md-6">
        <label>Target Selesai</label>
        <input type="date" name="target_selesai" id="target_selesai" class="form-control" max="<?= $tanggal_selesai_audit ?>" required>
        <small class="text-muted">Maksimal <?= date('d-m-Y', strtotime($tanggal_selesai_audit)) ?></small>
       </div>
       <div class="col-md-6">
        <label>Status</label>
        <input type="text" class="form-control" value="Proses" readonly>
        <input type="hidden" name="status" value="Proses">
       </div>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="index.php?rekomendasi_id=<?= $rekomendasi_id ?>" class="btn btn-secondary">Kembali</a>
     </div>
    </form>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
