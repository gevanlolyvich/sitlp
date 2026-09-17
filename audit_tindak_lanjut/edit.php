<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];
$q = mysqli_query($conn,"SELECT * FROM audit_tindak_lanjut WHERE id=$id");
$tl = mysqli_fetch_assoc($q);

if(!$tl)
{
    die("Data tidak ditemukan");
}

// Only allow edit if status is Proses
if ($tl['status'] != 'Proses') {
    $_SESSION['error'] = "Edit hanya diizinkan saat status masih Proses.";
    header("Location: index.php" . ($tl['rekomendasi_id'] ? "?rekomendasi_id=" . $tl['rekomendasi_id'] : ""));
    exit;
}

$qUnit = mysqli_query($conn,"SELECT * FROM unit_kerja ORDER BY nama_unit");
$isLocked = ($tl['status'] == 'Sesuai');

// Get audit start & end date for target validation
$qTL = mysqli_query($conn,"SELECT r.temuan_id FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id WHERE tl.id=$id");
$tlData = mysqli_fetch_assoc($qTL);
$tanggal_mulai_audit = '';
$tanggal_selesai_audit = '';
if ($tlData) {
    $qTemuan = mysqli_query($conn,"SELECT audit_id FROM audit_temuan WHERE id=" . (int)$tlData['temuan_id']);
    $temuanData = mysqli_fetch_assoc($qTemuan);
    if ($temuanData) {
        blockLockedAudit($conn, (int)$temuanData['audit_id']);
        $qAudit = mysqli_query($conn,"SELECT tanggal_mulai, tanggal_selesai FROM audit_pemeriksaan WHERE id=" . (int)$temuanData['audit_id']);
        $auditData = mysqli_fetch_assoc($qAudit);
        $tanggal_mulai_audit = $auditData['tanggal_mulai'];
        $tanggal_selesai_audit = $auditData['tanggal_selesai'];
    }
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">
<div class="jxb-page-header">
<div>
<h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Tindak Lanjut</h1>
<div class="jxb-page-subtitle">Ubah data tindak lanjut <?= htmlspecialchars($tl['nomor_tindak_lanjut']) ?></div>
</div>
<div class="jxb-page-actions">
<a href="index.php?<?= ($tlData && $tlData['temuan_id']) ? 'temuan_id=' . (int)$tlData['temuan_id'] : 'rekomendasi_id=' . $tl['rekomendasi_id'] ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>
</div>
<div class="card mt-3">
<form action="update.php" method="post">
<input type="hidden" name="id" value="<?= $tl['id'] ?>">
<input type="hidden" name="rekomendasi_id" value="<?= $tl['rekomendasi_id'] ?>">
<div class="card-body">
<div class="mb-3">
<label class="form-label">Unit Kerja <span class="jxb-required">*</span></label>
<select name="unit_id" class="form-select" <?= $isLocked ? 'disabled' : '' ?> required>
 <option value="">Pilih Unit</option>
 <?php
 while($u=mysqli_fetch_assoc($qUnit)){
 ?>
 <option value="<?= $u['id'] ?>" <?= $tl['unit_id']==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['nama_unit']) ?></option>
 <?php } ?>
</select>
<?php if($isLocked): ?>
<input type="hidden" name="unit_id" value="<?= $tl['unit_id'] ?>">
<?php endif; ?>
</div>
<div class="mb-3">
<label class="form-label">Uraian Tindak Lanjut <span class="jxb-required">*</span></label>
<textarea name="uraian_tindak_lanjut" class="form-control" rows="5" required><?= htmlspecialchars($tl['uraian_tindak_lanjut']) ?></textarea>
</div>
<div class="row">
<div class="col-md-6">
<label class="form-label">Target Selesai</label>
<input type="date" name="target_selesai" value="<?= $tl['target_selesai'] ?>" class="form-control" min="<?= $tanggal_mulai_audit ?>" max="<?= $tanggal_selesai_audit ?>" <?= $isLocked ? 'disabled' : '' ?>>
<small class="text-muted"><?= ($tanggal_mulai_audit && $tanggal_selesai_audit) ? 'Rentang ' . date('d-m-Y', strtotime($tanggal_mulai_audit)) . ' s/d ' . date('d-m-Y', strtotime($tanggal_selesai_audit)) : '-' ?></small>
<?php if($isLocked): ?>
<input type="hidden" name="target_selesai" value="<?= $tl['target_selesai'] ?>">
<?php endif; ?>
</div>
<div class="col-md-6">
<label class="form-label">Status</label>
<input type="text" class="form-control" value="<?= htmlspecialchars($tl['status']) ?>" readonly>
<input type="hidden" name="status" value="<?= htmlspecialchars($tl['status']) ?>">
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary">Update</button>
<a href="index.php?<?= ($tlData && $tlData['temuan_id']) ? 'temuan_id=' . (int)$tlData['temuan_id'] : 'rekomendasi_id=' . $tl['rekomendasi_id'] ?>" class="btn btn-outline-secondary">Kembali</a>
</div>
</form>
</div>
</div>
</div>
</main>

<?php
include "../templates/footer.php";
?>
