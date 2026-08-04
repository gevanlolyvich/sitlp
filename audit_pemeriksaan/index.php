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
	ORDER BY ap.id DESC
	LIMIT $limit OFFSET $offset");

?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header d-flex justify-content-between">
     <h3 class="card-title">Pemeriksaan Audit</h3>
     <a href="../audit_program/index.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>Dari Program Audit</a>
    </div>
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
                    <a href="index.php" class="btn btn-secondary">Reset</a>
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
		if($status=='DRAFT'){
		    echo '<span class="badge bg-secondary">DRAFT</span>';
		} elseif($status=='BERJALAN'){
		    echo '<span class="badge bg-warning">BERJALAN</span>';
		} else{
		    echo '<span class="badge bg-success">SELESAI</span>';
		}
	?></td>
	<td>
	 <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Detail</a>
	 <?php if($row['status'] != 'SELESAI'): ?>
	 <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
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
    <a class="page-link" href="?page=<?= $page - 1 ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>">Sebelumnya</a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
    <a class="page-link" href="?page=<?= $i ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= $page + 1 ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>">Selanjutnya</a>
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
