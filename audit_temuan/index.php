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
    <div class="jxb-page-header">
     <div>
      <h1 class="jxb-page-title"><i class="fas fa-search me-2 text-primary"></i>Temuan Audit</h1>
      <div class="jxb-page-subtitle"><?= htmlspecialchars($audit['nomor_audit']) ?> &mdash; <?= htmlspecialchars($audit['judul_audit']) ?></div>
     </div>
     <div class="jxb-page-actions">
      <a href="../audit_pemeriksaan/detail.php?id=<?= $audit_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
      <?php if(!$locked): ?>
      <a href="create.php?audit_id=<?= $audit_id ?>" class="btn btn-primary"><i class="fas fa-plus"></i>Tambah Temuan</a>
      <?php endif; ?>
     </div>
    </div>
    <div class="card">
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
	<td class="fw-semibold"><?= htmlspecialchars($row['judul_temuan']) ?></td>
	<td>
	 <?php
	 $tr = $row['tingkat_risiko'];
	 if($tr=='Tinggi'){ echo '<span class="jxb-status-badge is-danger">Tinggi</span>'; }
	 elseif($tr=='Sedang'){ echo '<span class="jxb-status-badge is-warn">Sedang</span>'; }
	 elseif($tr=='Rendah'){ echo '<span class="jxb-status-badge is-success">Rendah</span>'; }
	 else { echo htmlspecialchars($tr); }
	 ?>
	</td>
	<td>
	 <?php
	 $ts = $row['status'];
	 if($ts=='Selesai'){ echo '<span class="jxb-status-badge is-success">Selesai</span>'; }
	 elseif($ts=='Draft'){ echo '<span class="jxb-status-badge is-neutral">Draft</span>'; }
	 else { echo htmlspecialchars($ts); }
	 ?>
	</td>
	<td>
	<a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm tb-icon btn-blink-border" title="Detail" aria-label="Detail"><i class="fas fa-eye"></i></a>
	<?php if(!$locked): ?>
	<a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm tb-icon btn-blink-border" title="Edit" aria-label="Edit"><i class="fas fa-edit"></i></a>
	<a href="javascript:void(0)" class="btn btn-danger btn-sm tb-icon btn-blink-border" title="Hapus" aria-label="Hapus" onclick="hapusTemuan(<?= $row['id'] ?>, <?= $audit_id ?>)"><i class="fas fa-trash"></i></a>
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
