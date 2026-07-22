<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR','AUDITEE']);

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT tl.*, u.nama_unit, r.rekomendasi, r.nomor_rekomendasi, t.nomor_temuan, t.judul_temuan, a.nomor_audit, a.judul_audit
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    LEFT JOIN audit_pemeriksaan a ON t.audit_id=a.id
    WHERE tl.id=$id");

$tl = mysqli_fetch_assoc($q);
if (!$tl) {
    die("Data tidak ditemukan");
}

// Get history logs
$qLog = mysqli_query($conn,"SELECT l.*, usr.nama
    FROM audit_tindak_lanjut_log l
    LEFT JOIN users usr ON l.dibuat_oleh=usr.id
    WHERE l.tindak_lanjut_id=$id
    ORDER BY l.id ASC");

$logs = [];
while ($log = mysqli_fetch_assoc($qLog)) {
    $logs[] = $log;
}

$isAuditee = ($_SESSION['role'] == 'AUDITEE');
$isFinal = in_array($tl['status'], ['Sesuai', 'Tidak Dapat Ditindak Lanjut']);

// Determine if auditee can upload bukti
$lastLogAksi = count($logs) > 0 ? $logs[count($logs)-1]['aksi'] : null;
$canUpload = !$isFinal && $lastLogAksi != 'upload_bukti';

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

<div class="card mt-3">
<div class="card-header">
<h3 class="card-title">Detail Tindak Lanjut</h3>
</div>
<div class="card-body">
<table class="table table-bordered">
 <tr><th width="200">Nomor TL</th><td><?= htmlspecialchars($tl['nomor_tindak_lanjut']) ?></td></tr>
 <tr><th>Nomor Audit</th><td><?= htmlspecialchars($tl['nomor_audit']) ?> - <?= htmlspecialchars($tl['judul_audit']) ?></td></tr>
 <tr><th>Nomor Temuan</th><td><?= htmlspecialchars($tl['nomor_temuan']) ?> - <?= htmlspecialchars($tl['judul_temuan']) ?></td></tr>
 <tr><th>Rekomendasi</th><td><?= nl2br(htmlspecialchars($tl['rekomendasi'])) ?></td></tr>
 <tr><th>Unit Kerja</th><td><?= htmlspecialchars($tl['nama_unit']) ?></td></tr>
 <tr><th>PIC</th><td><?= htmlspecialchars($tl['pic']) ?></td></tr>
 <tr><th>Uraian Tindak Lanjut</th><td style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($tl['uraian_tindak_lanjut'])) ?></td></tr>
 <tr><th>Target Selesai</th><td><?= $tl['target_selesai'] ? date('d-m-Y', strtotime($tl['target_selesai'])) : '-' ?></td></tr>
 <tr><th>Status Terakhir</th>
  <td>
   <?php
   $sts = $tl['status'];
   $badgeMap = ['Proses'=>'bg-warning','Sesuai'=>'bg-success','Belum Sesuai'=>'bg-danger','Belum Ditindak Lanjut'=>'bg-secondary','Tidak Dapat Ditindak Lanjut'=>'bg-dark'];
   $bc = isset($badgeMap[$sts]) ? $badgeMap[$sts] : 'bg-info';
   echo '<span class="badge ' . $bc . '">' . htmlspecialchars($sts) . '</span>';
   ?>
  </td>
 </tr>
</table>
</div>
</div>

<!-- History Timeline -->
<div class="card mt-3">
<div class="card-header">
<h3 class="card-title">Riwayat Tindak Lanjut</h3>
</div>
<div class="card-body">
<div class="timeline">
<?php
$prevAksi = null;
foreach ($logs as $idx => $log):
    $dt = date('d-m-Y H:i', strtotime($log['dibuat_pada']));
    $nama = $log['nama'] ?: 'Sistem';
    $icon = 'fa-circle';
    $color = 'bg-gray';
    $title = '';

    if ($log['aksi'] == 'buat') {
        $icon = 'fa-plus-circle';
        $color = 'bg-primary';
        $title = 'Tindak Lanjut Dibuat';
    } elseif ($log['aksi'] == 'upload_bukti') {
        $icon = 'fa-upload';
        $color = 'bg-info';
        $title = 'Upload Bukti';
    } elseif ($log['aksi'] == 'verifikasi') {
        $icon = 'fa-check-circle';
        $color = 'bg-success';
        $title = 'Verifikasi Kepala SPI';
    }

    // Arrow between items
    if ($idx > 0) {
        echo '<div style="text-align:center;color:#aaa;font-size:18px;">↓</div>';
    }
?>
<div class="timeline-item mb-3">
    <div class="row">
        <div class="col-auto text-center" style="width:50px;">
            <i class="fas <?= $icon ?> <?= $color ?> text-white rounded-circle p-2" style="font-size:18px;"></i>
        </div>
        <div class="col">
            <div class="card card-outline card-<?= str_replace('bg-','',$color) ?>">
                <div class="card-header py-2">
                    <strong><?= $title ?></strong>
                    <span class="float-right text-muted small"><?= $dt ?> oleh <?= htmlspecialchars($nama) ?></span>
                </div>
                <div class="card-body py-2">
                    <?php if ($log['aksi'] == 'verifikasi'): ?>
                        <div>
                            Status: 
                            <?php if ($log['status_lama']): ?>
                                <span class="badge bg-secondary"><?= htmlspecialchars($log['status_lama']) ?></span>
                                <i class="fas fa-arrow-right mx-2"></i>
                            <?php endif; ?>
                            <span class="badge <?= isset($badgeMap[$log['status_baru']]) ? $badgeMap[$log['status_baru']] : 'bg-info' ?>"><?= htmlspecialchars($log['status_baru']) ?></span>
                        </div>
                        <?php if ($log['catatan_spi']): ?>
                            <div class="mt-1"><strong>Catatan SPI:</strong> <?= nl2br(htmlspecialchars($log['catatan_spi'])) ?></div>
                        <?php endif; ?>
                    <?php elseif ($log['aksi'] == 'upload_bukti'): ?>
                        <div>Upload bukti baru</div>
                        <?php if ($log['hasil_tindak_lanjut']): ?>
                            <div class="mt-1"><strong>Hasil:</strong> <?= nl2br(htmlspecialchars($log['hasil_tindak_lanjut'])) ?></div>
                        <?php endif; ?>
                        <?php if ($log['file_bukti']): ?>
                            <div class="mt-1"><a href="../uploads/tindak_lanjut/<?= $log['file_bukti'] ?>" target="_blank" class="btn btn-success btn-sm"><i class="fas fa-file"></i> Lihat Bukti</a></div>
                        <?php endif; ?>
                    <?php elseif ($log['aksi'] == 'buat'): ?>
                        <div>Status awal: <span class="badge bg-warning">Proses</span></div>
                    <?php endif; ?>
                    <?php if ($log['keterangan'] && $log['aksi'] != 'buat'): ?>
                        <div class="mt-1 small text-muted"><?= htmlspecialchars($log['keterangan']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>

<!-- Upload Section for Auditee -->
<?php if ($isAuditee): ?>
<div class="card mt-3">
<div class="card-header">
<h3 class="card-title">Aksi</h3>
</div>
<div class="card-body">
    <?php if ($canUpload): ?>
    <form action="upload_bukti_process.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="rekomendasi_id" value="<?= $tl['rekomendasi_id'] ?>">
        <?php if (!empty($tl['catatan_spi'])): ?>
        <div class="mb-3">
            <label>Catatan SPI</label>
            <textarea class="form-control" rows="3" readonly style="background-color:#f8f9fa;"><?= htmlspecialchars($tl['catatan_spi']) ?></textarea>
        </div>
        <?php endif; ?>
        <div class="mb-3">
            <label>File Bukti</label>
            <input type="file" name="bukti" class="form-control" required>
            <small>Format: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</small>
        </div>
        <div class="mb-3">
            <label>Hasil Tindak Lanjut</label>
            <textarea name="hasil_tindak_lanjut" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
    </form>
    <?php elseif ($isFinal): ?>
        <?php
        $hasFile = false;
        foreach ($logs as $log) {
            if ($log['file_bukti']) {
                $hasFile = true;
                echo '<a href="../uploads/tindak_lanjut/' . $log['file_bukti'] . '" target="_blank" class="btn btn-success btn-sm mr-2"><i class="fas fa-file"></i> Lihat Bukti</a>';
            }
        }
        if (!$hasFile) {
            echo '<span class="text-muted">Tidak ada bukti</span>';
        }
        ?>
    <?php else: ?>
        <div class="alert alert-info mb-0">
            <i class="fas fa-clock"></i> Menunggu review Kepala SPI. Upload bukti belum dapat dilakukan.
        </div>
        <?php
        $hasFile = false;
        foreach ($logs as $log) {
            if ($log['file_bukti']) {
                if (!$hasFile) { echo '<div class="mt-2"><strong>Bukti terakhir:</strong><br>'; $hasFile = true; }
                echo '<a href="../uploads/tindak_lanjut/' . $log['file_bukti'] . '" target="_blank" class="btn btn-success btn-sm mr-2 mt-1"><i class="fas fa-file"></i> Lihat Bukti</a>';
            }
        }
        if ($hasFile) { echo '</div>'; }
        ?>
    <?php endif; ?>
</div>
</div>
<?php endif; ?>

<div class="mt-3 mb-3">
    <a href="<?= $isAuditee ? '../auditee_temuan/index.php' : 'index.php?rekomendasi_id=' . $tl['rekomendasi_id'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

</div>
</div>
</main>

<style>
.timeline-item .card {
    border-left: 3px solid #dee2e6;
}
.timeline-item .card-outline.card-primary { border-left-color: #007bff; }
.timeline-item .card-outline.card-info { border-left-color: #17a2b8; }
.timeline-item .card-outline.card-success { border-left-color: #28a745; }
</style>

<?php include "../templates/footer.php"; ?>
