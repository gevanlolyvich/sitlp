<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$audit_id = (int)$_GET['audit_id'];

$qAudit = mysqli_query($conn,"SELECT nomor_audit,judul_audit,status FROM audit_pemeriksaan WHERE id=$audit_id");

$audit = mysqli_fetch_assoc($qAudit);
$locked = ($audit['status'] == 'Selesai');
$q = mysqli_query($conn,"SELECT * FROM audit_temuan WHERE audit_id=$audit_id ORDER BY id DESC");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header d-flex align-items-center">
     <h3 class="card-title mb-0">Temuan Audit : <?= htmlspecialchars($audit['nomor_audit']) ?></h3>
     <div class="ms-auto">
      <a href="../audit_pemeriksaan/detail.php?id=<?= $audit_id ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
      <?php if(!$locked): ?>
      <a href="create.php?audit_id=<?= $audit_id ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>Tambah Temuan</a>
      <?php endif; ?>
     </div>
    </div>
    <div class="card-body">
      <div class="table-responsive-wrapper"><table id="tblTemuan" class="table table-bordered table-striped">
      <thead>
       <tr>
	<th>No Temuan</th>
	<th>Judul Temuan</th>
	<th>Risiko</th>
	<th>Status</th>
	<th width="180">Aksi</th>
       </tr>
      </thead>
      <tbody>
       <?php while($row=mysqli_fetch_assoc($q)){ ?>
       <tr>
        <td><?= htmlspecialchars($row['nomor_temuan']) ?></td>
	<td><?= htmlspecialchars($row['judul_temuan']) ?></td>
	<td><?= htmlspecialchars($row['tingkat_risiko']) ?></td>
	<td><?= htmlspecialchars($row['status']) ?></td>
	<td>
	<a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Detail</a>
	<?php if(!$locked): ?>
	<a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
	<a href="javascript:void(0)" class="btn btn-danger btn-sm" onclick="hapusTemuan(<?= $row['id'] ?>, <?= $audit_id ?>)">Hapus</a>
	<?php endif; ?>
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

<script>
 $(document).ready(function(){
  $('#tblTemuan').DataTable({
  responsive:true
 });
});
</script>

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
function hapusTemuan(id, audit_id)
{
    Swal.fire({
        title: 'Hapus Temuan?',
        text: 'Data tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    })
    .then((result) => {
        if (result.isConfirmed) {
            window.location = 'delete.php?id=' + id + '&audit_id=' + audit_id;
        }
    });
}
</script>

<?php
include "../templates/footer.php";
?>
