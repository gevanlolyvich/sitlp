<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN']);

$keyword = '';
$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($limit * ($page - 1));

$countResult = mysqli_query($conn,"SELECT COUNT(*) total
		FROM audit_log l
		LEFT JOIN users u ON l.user_id = u.id");
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

$q = mysqli_query($conn,"SELECT l.*,u.nama
		FROM audit_log l
		LEFT JOIN users u ON l.user_id = u.id
		ORDER BY l.created_at DESC
		LIMIT $limit OFFSET $offset");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Audit Trail</h3>
    </div>
    <div class="card-body">
     <div class="table-responsive-wrapper"><table id="tblLog" class="table table-bordered table-striped">
      <thead>
       <tr>
         <th>Waktu</th>
 	 <th>User</th>
	 <th>Aktivitas</th>
	 <th>Modul</th>
	 <th>Referensi</th>
       </tr>
      </thead>
      <tbody>
       <?php while($row = mysqli_fetch_assoc($q)){ ?>
       <tr>
        <td><?= $row['created_at'] ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['aktivitas']) ?></td>
        <td><?= htmlspecialchars($row['tabel_referensi']) ?></td>
        <td><?= $row['referensi_id'] ?></td>
       </tr>
       <?php } ?>
      </tbody>
      </table>
      </div>
    </div>
    <div class="card-footer mt-3 d-flex justify-content-between align-items-center">
    <small class="text-muted">Total: <strong><?= $totalRecords ?></strong></small>
    <nav>
    <ul class="pagination pagination-sm mb-0">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= $page - 1 ?>">Sebelumnya</a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
    <a class="page-link" href="?page=<?= $page + 1 ?>">Selanjutnya</a>
    </li>
    </ul>
    </nav>
    </div>
   </div>
  </div>
 </div>
</main>

<?php include "../templates/footer.php"; ?>
