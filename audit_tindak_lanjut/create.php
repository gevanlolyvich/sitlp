<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR','AUDITEE']);

$rekomendasi_id = (int)$_GET['rekomendasi_id'];
$qRek = mysqli_query($conn,"SELECT r.*, t.audit_id, t.nomor_temuan, ap.unit_id AS audit_unit_id, p.unit_id AS pkpt_unit_id, u.nama_unit
    FROM audit_rekomendasi r
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    LEFT JOIN audit_pemeriksaan ap ON ap.id=t.audit_id
    LEFT JOIN audit_program p ON p.id=ap.program_id
    LEFT JOIN unit_kerja u ON p.unit_id=u.id
    WHERE r.id=$rekomendasi_id");
$rek = mysqli_fetch_assoc($qRek);
if(!$rek){die("Rekomendasi tidak ditemukan");}

// Get audit end date for max target validation
$audit_id = (int)$rek['audit_id'];

blockLockedAudit($conn, $audit_id);

$qAudit = mysqli_query($conn,"SELECT tanggal_mulai, tanggal_selesai FROM audit_pemeriksaan WHERE id=$audit_id");
$auditData = mysqli_fetch_assoc($qAudit);
$tanggal_mulai_audit = $auditData['tanggal_mulai'];
$tanggal_selesai_audit = $auditData['tanggal_selesai'];

$selected_unit_id = (int)$rek['pkpt_unit_id'];
$nama_unit_pkpt = $rek['nama_unit'] ?? '';

// Generate nomor TL
$qCount = mysqli_query($conn,"SELECT COUNT(*) total FROM audit_tindak_lanjut WHERE rekomendasi_id=$rekomendasi_id");
$d = mysqli_fetch_assoc($qCount);
$urut = $d['total'] + 1;
$nomor_tl = 'TL-' . substr($rek['nomor_temuan'],3) . '-' . str_pad($urut,3,'0',STR_PAD_LEFT);

$isAuditee = ($_SESSION['role'] == 'AUDITEE');

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="jxb-page-header">
    <div>
     <h1 class="jxb-page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Tindak Lanjut</h1>
     <div class="jxb-page-subtitle">Buat tindak lanjut baru untuk rekomendasi <?= htmlspecialchars($rek['nomor_rekomendasi'] ?? '') ?></div>
    </div>
    <div class="jxb-page-actions">
     <a href="index.php?<?= isset($_GET['temuan_id']) ? 'temuan_id=' . (int)$_GET['temuan_id'] : 'rekomendasi_id=' . $rekomendasi_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
   </div>
   <div class="card mt-3">
    <form action="store.php" method="post">
     <input type="hidden" name="rekomendasi_id" value="<?= $rekomendasi_id ?>">
      <div class="card-body">
       <div class="row g-3">
        <div class="col-md-4">
         <label class="form-label">Nomor Tindak Lanjut</label>
         <input type="text" class="form-control" value="<?= $nomor_tl ?>" readonly>
         <input type="hidden" name="nomor_tindak_lanjut" value="<?= $nomor_tl ?>">
        </div>
        <div class="col-md-8">
         <label class="form-label">Unit Kerja</label>
         <input type="text" class="form-control" value="<?= htmlspecialchars($nama_unit_pkpt) ?>" readonly>
         <input type="hidden" name="unit_id" value="<?= $selected_unit_id ?>">
         <small class="text-muted">Unit kerja mengikuti PKPT</small>
        </div>
       </div>
       <div class="mt-3">
        <label class="form-label">Uraian Tindak Lanjut <span class="jxb-required">*</span></label>
        <textarea name="uraian_tindak_lanjut" class="form-control" rows="5" required></textarea>
       </div>
       <div class="row g-3 mt-1">
       <div class="col-md-6">
        <label class="form-label">Target Selesai <span class="jxb-required">*</span></label>
        <input type="date" name="target_selesai" id="target_selesai" class="form-control" min="<?= $tanggal_mulai_audit ?>" max="<?= $tanggal_selesai_audit ?>" required>
        <small class="text-muted">Rentang <?= date('d-m-Y', strtotime($tanggal_mulai_audit)) ?> s/d <?= date('d-m-Y', strtotime($tanggal_selesai_audit)) ?></small>
       </div>
       <div class="col-md-6">
        <label class="form-label">Status</label>
        <input type="text" class="form-control" value="Proses" readonly>
<input type="hidden" name="status" value="Proses">
       </div>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="index.php?<?= isset($_GET['temuan_id']) ? 'temuan_id=' . (int)$_GET['temuan_id'] : 'rekomendasi_id=' . $rekomendasi_id ?>" class="btn btn-outline-secondary">Kembali</a>
     </div>
    </form>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
