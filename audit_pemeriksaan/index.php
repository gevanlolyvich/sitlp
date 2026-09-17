<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";


$where = "";
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
if ($keyword != '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where = "
        WHERE
            ap.nomor_audit LIKE '%$kw%'
            OR
            ap.judul_audit LIKE '%$kw%'
    ";
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($limit * ($page - 1));

$countResult = mysqli_query($conn,"SELECT COUNT(*) total
	FROM audit_pemeriksaan ap
	LEFT JOIN unit_kerja uk ON ap.unit_id = uk.id
	LEFT JOIN auditor au ON ap.ketua_auditor_id = au.id
        $where");
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

$q = mysqli_query($conn,"SELECT ap.*, uk.nama_unit, au.nama_auditor
	FROM audit_pemeriksaan ap
	LEFT JOIN unit_kerja uk ON ap.unit_id = uk.id
	LEFT JOIN auditor au ON ap.ketua_auditor_id = au.id
        $where
	ORDER BY ap.created_at DESC
	LIMIT $limit OFFSET $offset");

?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="jxb-page-header">
    <div>
     <h1 class="jxb-page-title"><i class="fas fa-clipboard-check me-2 text-primary"></i>Pemeriksaan Audit</h1>
     <div class="jxb-page-subtitle">Daftar audit yang sedang atau telah dilaksanakan</div>
    </div>
   </div>
   <div class="card">
    <div class="card-body">


<!-- -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="search-form">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Cari berdasarkan Nomor atau Judul..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i>Cari</button>
                    <?php if(isset($_GET['keyword']) && $_GET['keyword'] != ''): ?>
                    <a href="index.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- -->


     <div class="table-responsive-wrapper"><table id="tblAudit" class="table table-bordered table-striped">
      <thead>
       <tr>
	<th>No Audit</th>
	<th>Judul Audit</th>
	<th>Unit Kerja</th>
	<th>Ketua Auditor</th>
	<th>Status</th>
	<th>Aksi</th>
       </tr>
      </thead>
      <tbody>
       <?php while($row=mysqli_fetch_assoc($q)){ ?>
       <tr>
        <td><?= htmlspecialchars($row['nomor_audit']) ?></td>
	<td><?= htmlspecialchars($row['judul_audit']) ?></td>
	<td><?= htmlspecialchars($row['nama_unit']) ?></td>
	<td><?= htmlspecialchars($row['nama_auditor']) ?></td>
        <td><?php
		$status = $row['status'];
		if($status=='Draft'){
		    echo '<span class="jxb-status-badge is-neutral">Draft</span>';
		} elseif($status=='Berjalan'){
		    echo '<span class="jxb-status-badge is-info">Berjalan</span>';
		} else{
		    echo '<span class="jxb-status-badge is-success">Selesai</span>';
		}
	?></td>
	<td>
	 <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm">Detail</a>
	 <?php if($row['status'] != 'Selesai'): ?>
	 <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm">Edit</a>
	 <?php endif; ?>

	</td>
       </tr>
      <?php } ?>
      </tbody>
     </table>
     </div>
    </div>

    <div class="mt-3 d-flex justify-content-between align-items-center">
    <small class="text-muted">Total: <strong><?= $totalRecords ?></strong></small>
    <nav>
    <ul class="pagination pagination-sm mb-0">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= $page - 1 ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>" aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i><span class="d-none d-sm-inline ps-1">Sebelumnya</span></a>
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
    <a class="page-link" href="?page=<?= $i ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>"><?= $i ?></a>
    </li>
    <?php endif; endforeach; ?>
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= $page + 1 ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>" aria-label="Selanjutnya"><span class="d-none d-sm-inline pe-1">Selanjutnya</span><i class="fas fa-chevron-right"></i></a>
    </li>
    </ul>
    </nav>
    </div>


   </div>
  </div>
 </div>
</main>



<?php
include "../templates/footer.php";
?>
