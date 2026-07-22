<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$audit_id = (int)$_GET['audit_id'];
$qAudit = mysqli_query($conn,"SELECT nomor_audit, judul_audit FROM audit_pemeriksaan WHERE id=$audit_id");
$audit = mysqli_fetch_assoc($qAudit);
$qLampiran = mysqli_query($conn,"SELECT l.*, u.nama 
	FROM audit_lampiran l
	LEFT JOIN users u ON l.uploaded_by=u.id
	WHERE l.audit_id=$audit_id
	ORDER BY l.uploaded_at DESC");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header d-flex align-items-center">
     <h3 class="card-title mb-0">Lampiran Audit : <?= htmlspecialchars($audit['nomor_audit']) ?></h3>
     <a href="../audit_pemeriksaan/detail.php?id=<?= $audit_id ?>" class="btn btn-secondary btn-sm ms-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <form action="store.php" method="post" enctype="multipart/form-data">
     <input type="hidden" name="audit_id" value="<?= $audit_id ?>">
     <div class="card-body">
      <div class="row">
       <div class="col-md-4">
        <label>Jenis Dokumen</label>
        <select name="jenis_dokumen" class="form-select" required>
	<option value="">Pilih</option>
	<option>Surat Tugas</option>
	<option>Program Audit</option>
	<option>Kertas Kerja</option>
	<option>Bukti Audit</option>
	<option>Foto Temuan</option>
	<option>Lainnya</option>
	</select>
       </div>
       <div class="col-md-6">
        <label>File</label>
        <input type="file" name="file" class="form-control" required>
	<small class="text-muted">PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</small>
       </div>
       <div class="col-md-2">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary w-100"> Upload </button>
       </div>
      </div>
     </div>
    </form>
   </div>
   <div class="card">
    <div class="card-body">
     <div class="table-responsive-wrapper"><table class="table table-bordered">
      <thead>
       <tr>
        <th>No</th>
	<th>Jenis</th>
	<th>Nama File</th>
	<th>Upload Oleh</th>
	<th>Tanggal</th>
	<th width="150">Aksi</th>
       </tr>
      </thead>
      <tbody>
      <?php
       $no = 1;
       while($row = mysqli_fetch_assoc($qLampiran)){
      ?>
       <tr>
        <td><?= $no++ ?></td>
	<td><?= htmlspecialchars($row['jenis_dokumen']) ?></td>
	<td><?= htmlspecialchars($row['nama_file']) ?></td>
	<td><?= htmlspecialchars($row['nama']) ?></td>
	<td><?= $row['uploaded_at'] ?></td>
	<td>
          <a href="../<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-success btn-sm">Download</a>
          <a href="javascript:void(0)" class="btn btn-danger btn-sm" onclick="hapusLampiran(<?= $row['id'] ?>, <?= $audit_id ?>)">Hapus </a>
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
function hapusLampiran(id, audit_id)
{
    Swal.fire({
        title: 'Hapus Lampiran?',
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

<?php include "../templates/footer.php"; ?>
