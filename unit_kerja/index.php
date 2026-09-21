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

$sql =mysqli_query($conn,"SELECT * FROM unit_kerja ORDER BY nama_unit");

?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
<div class="row mt-3 mb-3">
     <div class="col-md-6">
      <div class="jxb-page-header">
       <div>
        <h1 class="jxb-page-title"><i class="fas fa-building me-2 text-primary"></i>Master Unit Kerja</h1>
        <div class="jxb-page-subtitle">Kelola unit kerja auditee</div>
       </div>
      </div>
     </div>
     <div class="col-md-6 text-end">
      <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i>Tambah Unit</a>
     </div>
    </div>
   <div class="card">
    <div class="card-body">
     <div class="table-responsive-wrapper"><table id="tblUnit" class="table table-bordered table-striped">
      <thead>
       <tr>
        <th>ID</th>
	<th>Kode</th>
	<th>Nama Unit</th>
	<th>Email</th>
	<th>Status</th>
	<th>Aksi</th>
       </tr>
      </thead>
      <tbody>
       <?php
        while($r=mysqli_fetch_assoc($sql)){
       ?>
       <tr>
	<td><?= $r['id'] ?></td>
	<td><?= htmlspecialchars($r['kode_unit']) ?></td>
	<td><?= htmlspecialchars($r['nama_unit']) ?></td>
	<td><?= htmlspecialchars($r['email']) ?></td>
	<td>
	<?=$r['aktif']?'<span class="jxb-status-badge is-success">Aktif</span>':'<span class="jxb-status-badge is-danger">Nonaktif</span>'?>
	</td>
<!--
<td>
<a
href="edit.php?id=<?= $r['id'] ?>"
class="btn btn-warning btn-sm">
Edit
</a>
</td>
-->
	<td>
	 <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-outline-warning btn-sm tb-icon btn-blink-border" title="Edit" aria-label="Edit"><i class="fas fa-edit"></i></a>
	 <?php if($r['aktif']){ ?>
	 <a href="status.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm tb-icon btn-blink-border" title="Nonaktif" aria-label="Nonaktif" onclick="return confirm('Nonaktifkan unit ini?')"><i class="fas fa-ban"></i></a>
	 <?php } else { ?>
	 <a href="status.php?id=<?= $r['id'] ?>" class="btn btn-outline-success btn-sm tb-icon btn-blink-border" title="Aktifkan" aria-label="Aktifkan"><i class="fas fa-check"></i></a>
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

<?php
include "../templates/footer.php";
?>

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

<script>
$(document).ready(function(){
  $('#tblUnit').DataTable({
   responsive:true,
   pageLength:10,
   dom:'Bfrtip',
   buttons:['excel','pdf','print']
  });
});
</script>
