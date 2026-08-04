<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$limit = 50;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where = '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
if ($keyword) {
    $where = "WHERE keterangan LIKE '%$keyword%' OR tanggal LIKE '%$keyword%'";
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) total FROM hari_libur $where");
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

$q = mysqli_query($conn, "SELECT h.*, u.nama FROM hari_libur h LEFT JOIN users u ON h.created_by=u.id $where ORDER BY h.tanggal DESC LIMIT $limit OFFSET $offset");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
<div class="card mt-3">
<div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
<h3 class="card-title mb-0">Daftar Hari Libur</h3>
<div class="d-flex gap-2">
<form method="get" class="search-form">
<div class="input-group">
<input type="text" name="keyword" class="form-control" placeholder="Cari..." value="<?= htmlspecialchars($keyword) ?>">
<button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
</div>
</form>
<a href="create.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Hari Libur</a>
</div>
</div>
<div class="card-body">
<div class="table-responsive-wrapper">
<table class="table table-bordered table-hover">
<thead>
<tr>
<th>No</th>
<th>Tanggal</th>
<th>Keterangan</th>
<th>Dibuat Oleh</th>
<th width="150">Aksi</th>
</tr>
</thead>
<tbody>
<?php if (mysqli_num_rows($q) > 0): $no = $offset + 1; while ($r = mysqli_fetch_assoc($q)): ?>
<tr>
<td><?= $no++ ?></td>
<td><?= date('d-m-Y', strtotime($r['tanggal'])) ?></td>
<td><?= htmlspecialchars($r['keterangan']) ?></td>
<td><?= htmlspecialchars($r['nama'] ?? '-') ?></td>
<td>
<a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
<a href="javascript:void(0)" class="btn btn-danger btn-sm" onclick="if(confirm('Yakin hapus?')){window.location='delete.php?id=<?= $r['id'] ?>'}"><i class="fas fa-trash"></i></a>
</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="5" class="text-center">Belum ada data hari libur</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
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
</main>
<?php include "../templates/footer.php"; ?>
