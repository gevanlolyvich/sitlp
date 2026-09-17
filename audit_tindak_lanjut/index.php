<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR','AUDITEE']);

$rekomendasi = null;
$rekomendasi_id = 0;
$temuan = null;
$temuan_id = 0;
$rekomendasiTemuan = [];
$locked = false;
$status = '';
$allowedStatus = ['Proses','Sesuai','Belum Sesuai','Belum Ditindak Lanjut','Tidak Dapat Ditindak Lanjut'];
if(isset($_GET['status']) && in_array($_GET['status'], $allowedStatus, true))
{
    $status = $_GET['status'];
}
if(isset($_GET['rekomendasi_id']))
{
    $rekomendasi_id = (int)$_GET['rekomendasi_id'];
    $qRek = mysqli_query($conn,"
        SELECT r.*, t.nomor_temuan, t.judul_temuan, t.audit_id
        FROM audit_rekomendasi r
        LEFT JOIN audit_temuan t ON r.temuan_id=t.id
        WHERE r.id=$rekomendasi_id");
    $rekomendasi = mysqli_fetch_assoc($qRek);
    if(!$rekomendasi)
    {
        die("Rekomendasi tidak ditemukan");
    }

    $locked = isAuditLocked($conn, (int)$rekomendasi['audit_id']);
}
if(isset($_GET['temuan_id']))
{
    $temuan_id = (int)$_GET['temuan_id'];
    $qTemuan = mysqli_query($conn,"SELECT * FROM audit_temuan WHERE id=$temuan_id");
    $temuan = mysqli_fetch_assoc($qTemuan);
    if(!$temuan)
    {
        die("Temuan tidak ditemukan");
    }

    $locked = isAuditLocked($conn, (int)$temuan['audit_id']);

    $qRekTemuan = mysqli_query($conn,"SELECT * FROM audit_rekomendasi WHERE temuan_id=$temuan_id ORDER BY id");
    while($row = mysqli_fetch_assoc($qRekTemuan)){ $rekomendasiTemuan[] = $row; }
}

$where = [];
if($rekomendasi_id > 0)
{
    $where[] = "tl.rekomendasi_id=$rekomendasi_id";
}
if($temuan_id > 0)
{
    $where[] = "tl.rekomendasi_id IN (SELECT id FROM audit_rekomendasi WHERE temuan_id=$temuan_id)";
}
if($status !== '')
{
    $where[] = "tl.status='" . mysqli_real_escape_string($conn, $status) . "'";
}
if($_SESSION['role'] == 'AUDITEE'){
    $unit_id = (int)$_SESSION['unit_id'];
    $where[] = "tl.unit_id = ".$unit_id;
}
$sqlWhere = '';
if(count($where))
{
    $sqlWhere = 'WHERE ' . implode(' AND ', $where);
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($limit * ($page - 1));
$pageParam = '';
if($rekomendasi_id > 0){ $pageParam = '&rekomendasi_id=' . $rekomendasi_id; }
if($temuan_id > 0){ $pageParam .= '&temuan_id=' . $temuan_id; }
if($status !== ''){ $pageParam .= '&status=' . urlencode($status); }

$countResult = mysqli_query($conn,"SELECT COUNT(*) total
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    $sqlWhere");
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

$qTL = mysqli_query($conn,"SELECT tl.*, u.nama_unit, r.rekomendasi, r.nomor_rekomendasi, t.judul_temuan, t.nomor_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    $sqlWhere
    ORDER BY tl.created_at DESC
    LIMIT $limit OFFSET $offset");

$tlList = [];
while ($row = mysqli_fetch_assoc($qTL)) {
    // Get logs for this tindak lanjut
    $qLog = mysqli_query($conn,"SELECT * FROM audit_tindak_lanjut_log WHERE tindak_lanjut_id=" . $row['id'] . " ORDER BY id ASC");
    $logs = [];
    while ($log = mysqli_fetch_assoc($qLog)) {
        $logs[] = $log;
    }
    $row['logs'] = $logs;
    $tlList[] = $row;
}

$badgeMap = [
    'Proses' => 'bg-warning',
    'Sesuai' => 'bg-success',
    'Belum Sesuai' => 'bg-danger',
    'Belum Ditindak Lanjut' => 'bg-secondary text-white',
    'Tidak Dapat Ditindak Lanjut' => 'bg-dark text-white'
];

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<?php if(isset($_GET['upload']) && $_GET['upload']=='success'){ ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: 'Bukti tindak lanjut berhasil diupload',
    timer: 2500,
    showConfirmButton: false
});
});
</script>
<?php } ?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

<div class="card mt-3">
<div class="card-header d-flex align-items-center">
<h3 class="card-title mb-0">Tindak Lanjut Rekomendasi</h3>
<?php if($_SESSION['role'] != 'AUDITEE'): ?>
<?php if($temuan_id > 0): ?>
<a href="../audit_temuan/detail.php?id=<?= $temuan_id ?>" class="btn btn-secondary btn-sm ms-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
<?php elseif($rekomendasi_id > 0): ?>
<a href="../audit_temuan/detail.php?id=<?= $rekomendasi['temuan_id'] ?>" class="btn btn-secondary btn-sm ms-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
<?php endif; ?>
<?php endif; ?>
</div>
<div class="card-body">
<?php if($temuan_id > 0){ ?>
<div class="table-responsive-wrapper"><table class="table table-bordered">
<tr><th width="220">Nomor Temuan</th><td><?= htmlspecialchars($temuan['nomor_temuan']) ?></td></tr>
<tr><th>Judul Temuan</th><td><?= htmlspecialchars($temuan['judul_temuan']) ?></td></tr>
</table></div>
<h6 class="mt-2"><i class="fas fa-list-ol me-1"></i> Rekomendasi Temuan</h6>
<div class="table-responsive-wrapper"><table class="table table-bordered table-sm">
 <thead>
  <tr>
   <th width="40">No</th>
   <th>Rekomendasi</th>
   <th width="120">Aksi</th>
  </tr>
 </thead>
 <tbody>
  <?php $noRek = 1; foreach($rekomendasiTemuan as $rk): ?>
  <tr>
   <td><?= $noRek++ ?></td>
   <td style="white-space: pre-wrap;"><?= htmlspecialchars($rk['rekomendasi']) ?></td>
   <td>
    <?php if($_SESSION['role'] != 'AUDITEE' && !$locked): ?>
    <a href="create.php?rekomendasi_id=<?= $rk['id'] ?>&temuan_id=<?= $temuan_id ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> TL</a>
    <?php endif; ?>
   </td>
  </tr>
  <?php endforeach; ?>
 </tbody>
</table></div>
<?php }elseif(isset($_GET['rekomendasi_id'])){ ?>
<div class="table-responsive-wrapper"><table class="table table-bordered">
<tr><th width="220">Nomor Temuan</th><td><?= htmlspecialchars($rekomendasi['nomor_temuan']) ?></td></tr>
<tr><th>Judul Temuan</th><td><?= htmlspecialchars($rekomendasi['judul_temuan']) ?></td></tr>
<tr><th>Rekomendasi</th><td style="white-space: pre-wrap; word-break: break-word;"><?= nl2br(htmlspecialchars($rekomendasi['rekomendasi'])) ?></td></tr>
</table></div>
<?php } ?>
</div>
</div>

<div class="card">
<div class="card-header d-flex justify-content-between">
<h3 class="card-title">Daftar Tindak Lanjut</h3>
<?php if(isset($_GET['rekomendasi_id']) && $_SESSION['role'] != 'AUDITEE' && !$locked){ ?>
<a href="create.php?rekomendasi_id=<?= $rekomendasi_id ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Tindak Lanjut</a>
<?php } ?>
</div>

<div class="card-body">
<?php if($status !== ''): ?>
<div class="alert alert-warning py-2 d-flex justify-content-between align-items-center mb-3" role="alert">
    <span class="small"><i class="fas fa-filter me-1"></i>Menampilkan status: <strong><?= htmlspecialchars($status) ?></strong></span>
    <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times me-1"></i> Hapus filter</a>
</div>
<?php endif; ?>
<div class="table-responsive-wrapper"><table id="tblTL" class="table table-bordered table-hover">
 <thead>
  <tr>
   <th>Nomor TL</th>
   <th>Rekomendasi</th>
   <th>Unit Kerja</th>
   <th>Uraian</th>
   <th>Target</th>
<th>Status</th>
    <th>Catatan SIA</th>
    <th>Hasil Tindak Lanjut</th>
    <th>Nilai Penyetoran</th>
    <th width="200">Aksi</th>
  </tr>
 </thead>
 <tbody>
   <?php foreach ($tlList as $row):
    $hasUpload = false;
    $lastLogAksi = null;
    foreach ($row['logs'] as $l) { if ($l['aksi'] == 'upload_bukti') { $hasUpload = true; } }
    if (count($row['logs']) > 0) { $lastLogAksi = $row['logs'][count($row['logs'])-1]['aksi']; }
   ?>
  <tr>
<td><strong><?= htmlspecialchars($row['nomor_tindak_lanjut']) ?></strong>
        <small class="text-muted d-block" style="font-size:11px;font-weight:400;"><?= htmlspecialchars($row['judul_temuan'] ?? '') ?></small>
    </td>
    <td style="white-space: pre-wrap; word-break: break-word; max-width: 220px;"><?= htmlspecialchars($row['rekomendasi']) ?></td>
    <td><?= htmlspecialchars($row['nama_unit']) ?></td>
    <td style="white-space: pre-wrap; word-wrap: break-word; max-width: 250px;"><?= htmlspecialchars($row['uraian_tindak_lanjut']) ?></td>
   <td><?= $row['target_selesai'] ? date('d-m-Y', strtotime($row['target_selesai'])) : '-' ?></td>
   <td>
    <?php
    $sts = $row['status'];
    $bc = isset($badgeMap[$sts]) ? $badgeMap[$sts] : 'bg-info';
    echo '<span class="badge ' . $bc . '">' . htmlspecialchars($sts) . '</span>';
    ?>
   </td>
    <td><?= htmlspecialchars($row['catatan_spi'] ?? '-') ?></td>
    <td style="white-space: pre-wrap; word-wrap: break-word; max-width: 200px;"><?= htmlspecialchars($row['hasil_tindak_lanjut'] ?? '-') ?></td>
    <td><?= ($row['nilai_penyerahan'] !== null && $row['nilai_penyerahan'] !== '') ? 'Rp ' . number_format((float)$row['nilai_penyerahan'], 2, ',', '.') : '-' ?></td>
    <td>
    <?php if ($row['bukti_file']): ?>
    <a href="../uploads/tindak_lanjut/<?= basename($row['bukti_file']) ?>" target="_blank" class="btn btn-success btn-sm mb-1" title="Lihat Bukti"><i class="fas fa-file"></i></a>
    <?php endif; ?>
    <?php if ($_SESSION['role'] == 'AUDITEE'): ?>
     <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> Lihat</a>
    <?php elseif (in_array($row['status'], ['Sesuai', 'Tidak Dapat Ditindak Lanjut'])): ?>
     <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm" title="History"><i class="fas fa-history"></i> History</a>
    <?php else: ?>
      <?php if ($row['status'] == 'Proses' && !$hasUpload && !$locked): ?>
      <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm mb-1" title="Edit"><i class="fas fa-edit"></i></a>
      <a href="javascript:void(0)" class="btn btn-danger btn-sm mb-1" onclick="hapusTL(<?= $row['id'] ?>,<?= $row['rekomendasi_id'] ?>)" title="Hapus"><i class="fas fa-trash"></i></a>
      <?php endif; ?>
      <?php if ($lastLogAksi == 'upload_bukti'): ?>
      <a href="verifikasi.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm mb-1" title="Verifikasi"><i class="fas fa-check"></i> Verifikasi</a>
      <?php endif; ?>
    <?php endif; ?>
   </td>
  </tr>
  <?php endforeach; ?>
 </tbody>
</table>
</div>
<div class="card-footer d-flex justify-content-between align-items-center">
<small class="text-muted">Total: <strong><?= $totalRecords ?></strong></small>
<nav>
<ul class="pagination pagination-sm mb-0">
<li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $page - 1 ?><?= $pageParam ?>" aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i><span class="d-none d-sm-inline ps-1">Sebelumnya</span></a>
</li>
<?php
$range = [];
for ($i = 1; $i <= $totalPages; $i++) {
    if ($i == 1 || $i == $totalPages || abs($i - $page) <= 1) {
        $range[] = $i;
    } elseif (end($range) !== '...') {
        $range[] = '...';
    }
}
foreach ($range as $i):
    if ($i === '...'):
?>
<li class="page-item disabled"><span class="page-link">&hellip;</span></li>
<?php else: ?>
<li class="page-item <?= $i == $page ? 'active' : '' ?>">
<a class="page-link" href="?page=<?= $i ?><?= $pageParam ?>"><?= $i ?></a>
</li>
<?php endif; endforeach; ?>
<li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $page + 1 ?><?= $pageParam ?>" aria-label="Selanjutnya"><span class="d-none d-sm-inline pe-1">Selanjutnya</span><i class="fas fa-chevron-right"></i></a>
</li>
</ul>
</nav>
</div>
</div>

</div>
</div>
</main>



<?php if (isset($_SESSION['success'])) : ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= addslashes($_SESSION['success']) ?>',
    timer: 2500,
    showConfirmButton: false
});
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= addslashes($_SESSION['error']) ?>'
});
</script>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<script>
function hapusTL(id, rekomendasi_id)
{
    Swal.fire({
        title: 'Hapus Tindak Lanjut?',
        text: 'Data tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    })
    .then((result) => {
        if (result.isConfirmed) {
            window.location = 'delete.php?id=' + id + '&rekomendasi_id=' + rekomendasi_id;
        }
    });
}
</script>

<?php include "../templates/footer.php"; ?>
