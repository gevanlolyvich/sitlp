<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$audit_id = (int)$_GET['audit_id'];

$qAudit = mysqli_query($conn,"SELECT nomor_audit, judul_audit FROM audit_pemeriksaan WHERE id=$audit_id");
$audit = mysqli_fetch_assoc($qAudit);
$qAuditor = mysqli_query($conn,"SELECT * FROM auditor WHERE aktif=1 ORDER BY nama_auditor");

$qTim = mysqli_query($conn,"SELECT t.*, a.nama_auditor FROM audit_tim t
	LEFT JOIN auditor a ON t.auditor_id=a.id
	WHERE t.audit_id=$audit_id
	ORDER BY t.id");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Tim Audit : <?= htmlspecialchars($audit['nomor_audit']) ?></h3>
    </div>
    <form action="store.php" method="post">
     <input type="hidden" name="audit_id" value="<?= $audit_id ?>">
     <div class="card-body">
      <div class="row">
       <div class="col-md-5">
        <label>Auditor</label>
        <select name="auditor_id" class="form-select" required>
	 <option value="">Pilih Auditor</option>
         <?php
          while($a=mysqli_fetch_assoc($qAuditor)){
         ?>
         <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_auditor']) ?></option>
         <?php } ?>
        </select>
       </div>
       <div class="col-md-4">
       <label>Peran</label>
       <select name="peran" class="form-select">
	<option value="KETUA">KETUA</option>
	<option value="ANGGOTA">ANGGOTA</option>
	<option value="PENGENDALI">PENGENDALI</option>
       </select>
       </div>
      <div class="col-md-3">
       <label>&nbsp;</label>
       <button type="submit" class="btn btn-primary w-100">Tambah Tim</button>
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
	<th>Nama Auditor</th>
	<th>Peran</th>
	<th width="100">Aksi</th>
      </tr>
     </thead>
     <tbody>
      <?php
       $no=1;
       while($t=mysqli_fetch_assoc($qTim)){
       ?>
        <tr>
	 <td><?= $no++ ?></td>
	 <td><?= htmlspecialchars($t['nama_auditor']) ?></td>
	 <td><?= htmlspecialchars($t['peran']) ?></td>
	 <td>
	  <a href="delete.php?id=<?= $t['id'] ?>&audit_id=<?= $audit_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus anggota tim?')">Hapus</a>
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
