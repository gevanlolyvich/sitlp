<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
hasRole(['ADMIN','KEPALA_SIA']);
include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

$sql = mysqli_query($conn,"SELECT * FROM auditor ORDER BY nama_auditor");
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="row mt-3 mb-3">
     <div class="col-md-6">
      <div class="jxb-page-header">
       <div>
        <h1 class="jxb-page-title"><i class="fas fa-user-shield me-2 text-primary"></i>Master Auditor</h1>
        <div class="jxb-page-subtitle">Kelola data auditor internal</div>
       </div>
      </div>
     </div>
     <div class="col-md-6 text-end">
      <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i>Tambah Auditor</a>
     </div>
    </div>
   <div class="card">
   <div class="card-body">
    <div class="table-responsive-wrapper"><table id="tblAuditor" class="table table-bordered table-striped">
     <thead>
      <tr>
	<th>ID</th>
	<th>NIP</th>
	<th>Nama Auditor</th>
	<th>Jabatan</th>
	<th>Email</th>
	<th>Status</th>
	<th>Aksi</th>
      </tr>
     </thead>
     <tbody>
      <?php while($r=mysqli_fetch_assoc($sql)){ ?>
      <tr>
	<td><?= $r['id'] ?></td>
	<td><?= htmlspecialchars($r['nip']) ?></td>
	<td><?= htmlspecialchars($r['nama_auditor']) ?></td>
	<td><?= htmlspecialchars($r['jabatan']) ?></td>
	<td><?= htmlspecialchars($r['email']) ?></td>
	<td><?= $r['aktif'] ? '<span class="jxb-status-badge is-success">Aktif</span>' : '<span class="jxb-status-badge is-danger">Nonaktif</span>'?></td>
	<td>
	 <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-outline-warning btn-sm">Edit</a>
	<?php if($r['aktif']){ ?>
	 <a href="status.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm">Nonaktif</a>
	<?php } else { ?>
	 <a href="status.php?id=<?= $r['id'] ?>" class="btn btn-outline-success btn-sm">Aktifkan</a>
	<?php } ?>
	</td>
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

<?php include "../templates/footer.php"; ?>

<script>
 $(document).ready(function(){
  $('#tblAuditor').DataTable({
   responsive:true,
   pageLength:10,
   dom:'Bfrtip',
   buttons:[
   'excel',
   'pdf',
   'print'
  ]});
 });
</script>
