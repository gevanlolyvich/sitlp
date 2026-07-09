<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI']);

$q = mysqli_query($conn,"SELECT l.*,u.nama
		FROM audit_log l
		LEFT JOIN users u ON l.user_id = u.id
		ORDER BY l.created_at DESC
		LIMIT 500");

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
   </div>
  </div>
 </div>
</main>

<script>
$(document).ready(function(){
  $('#tblLog').DataTable({
    order:[[0,'desc']],
    pageLength:25
  });
});
</script>

<?php include "../templates/footer.php"; ?>
