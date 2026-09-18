<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR', 'DIREKSI', 'KOMISARIS', 'AUDITEE']);

$role = $_SESSION['role'];
$unitFilter = '';
if($role == 'AUDITEE')
{
    $unitFilter = ' AND unit_id=' . (int)$_SESSION['unit_id'];
}
$isDireksi = in_array($role, ['DIREKSI', 'KOMISARIS']);

$allowedStatus = [
    'Proses'                    => 'bg-warning',
    'Sesuai'                    => 'bg-success',
    'Belum Sesuai'              => 'bg-danger',
    'Belum Ditindak Lanjut'     => 'bg-secondary text-white',
    'Tidak Dapat Ditindak Lanjut' => 'bg-dark text-white'
];

$status = isset($_GET['status']) ? $_GET['status'] : '';
if(!isset($allowedStatus[$status]))
{
    http_response_code(400);
    echo '<div class="px-3 py-2 text-danger">Status tidak valid.</div>';
    exit;
}
$badge = $allowedStatus[$status];

$total = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='".mysqli_real_escape_string($conn,$status)."' $unitFilter"))[0];

$q = mysqli_query($conn,"SELECT tl.*, u.nama_unit, r.rekomendasi, r.nomor_rekomendasi,
    t.judul_temuan, t.nomor_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    WHERE tl.status='".mysqli_real_escape_string($conn,$status)."' $unitFilter
    ORDER BY tl.updated_at DESC, tl.id DESC
    LIMIT 20");

function potongTeks($teks, $max = 90)
{
    $teks = trim($teks);
    if(function_exists('mb_strlen')){
        if(mb_strlen($teks) > $max) { return mb_substr($teks, 0, $max) . '...'; }
    }else{
        if(strlen($teks) > $max) { return substr($teks, 0, $max) . '...'; }
    }
    return $teks;
}
?>

<div class="alert alert-light border py-2 mb-2 d-flex justify-content-between align-items-center">
    <span class="small fw-bold"><i class="fas fa-tasks me-1"></i>Daftar tindak lanjut</span>
    <span class="small text-muted">Status: <span class="badge <?= $badge ?>"><?= htmlspecialchars($status) ?></span> &mdash; <strong><?= $total ?></strong> TL</span>
</div>

<div class="table-responsive-wrapper">
<table class="table table-bordered table-hover align-middle mb-0 w-100">
 <thead>
  <tr class="table-light">
   <th width="40" class="text-center">No</th>
   <th>Nomor TL</th>
   <th>Rekomendasi</th>
   <th style="min-width:120px;">Unit</th>
   <th style="min-width:100px;">Target</th>
   <th>Status</th>
   <?php if(!$isDireksi): ?><th width="90" class="text-center">Aksi</th><?php endif; ?>
  </tr>
 </thead>
 <tbody>
 <?php if(mysqli_num_rows($q) > 0):
    $no = 1;
    while($tl = mysqli_fetch_assoc($q)):
        $nomor = !empty($tl['nomor_tindak_lanjut']) ? $tl['nomor_tindak_lanjut'] : ('TL-' . $tl['id']);
        $rk = $tl['rekomendasi'] ?: 'Rekomendasi tidak tersedia';
 ?>
  <tr>
   <td class="text-center"><?= $no++ ?></td>
   <td style="min-width:200px;"><strong><?= htmlspecialchars($nomor) ?></strong>
    <?php if(!empty($tl['judul_temuan'])): ?>
    <div class="small text-muted mt-1" style="line-height:1.45;"><?= htmlspecialchars(potongTeks($tl['judul_temuan'], 90)) ?></div>
    <?php endif; ?>
   </td>
   <td style="min-width:200px;white-space:pre-wrap;word-break:break-word;"><?= htmlspecialchars(potongTeks($rk)) ?></td>
   <td style="min-width:120px;"><?= htmlspecialchars($tl['nama_unit'] ?? '-') ?></td>
   <td style="min-width:100px;"><?= $tl['target_selesai'] ? date('d-m-Y', strtotime($tl['target_selesai'])) : '-' ?></td>
   <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($status) ?></span></td>
   <?php if(!$isDireksi): ?>
   <td class="text-center"><a href="../audit_tindak_lanjut/detail.php?id=<?= (int)$tl['id'] ?>" class="btn btn-primary btn-sm" title="Detail"><i class="fas fa-eye"></i></a></td>
   <?php endif; ?>
  </tr>
 <?php endwhile; else: ?>
  <tr>
   <td colspan="<?= $isDireksi ? 6 : 7 ?>" class="text-center text-muted py-4">
    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
    Belum ada tindak lanjut dengan status <strong><?= htmlspecialchars($status) ?></strong>.
   </td>
  </tr>
 <?php endif; ?>
 </tbody>
</table>
</div>

<div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2 small">
    <span class="text-muted">Menampilkan maksimal 20 dari <strong><?= $total ?></strong> tindak lanjut.</span>
    <?php if($isDireksi): ?>
    <span class="text-muted fw-bold"><i class="fas fa-lock me-1"></i>Lihat semua (<?= $total ?>)</span>
    <?php else: ?>
    <a href="../audit_tindak_lanjut/index.php?status=<?= urlencode($status) ?>" class="fw-bold text-primary text-decoration-none">Lihat semua (<?= $total ?>) <i class="fas fa-arrow-right"></i></a>
    <?php endif; ?>
</div>