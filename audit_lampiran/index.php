<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

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
    <div class="card-header">
     <h3 class="card-title">Lampiran Audit : <?= htmlspecialchars($audit['nomor_audit']) ?></h3>
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
          <a href="../<?= $row['file_path'] ?>" target="_blank" class="btn btn-success btn-sm">Download</a>
          <a href="delete.php?id=<?= $row['id'] ?>&audit_id=<?= $audit_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus lampiran?')">Hapus </a>
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
