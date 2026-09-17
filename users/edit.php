<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$id = (int)$_GET['id'];
$q = mysqli_query(
    $conn,"SELECT a.*, (select nama_unit from unit_kerja where id = a.unit_id) as nama_unit
     FROM users a WHERE a.id=$id"
);
$user = mysqli_fetch_assoc($q);

if(!$user){
    die("User tidak ditemukan");
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">
<div class="jxb-page-header">
    <div>
        <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit User</h1>
        <div class="jxb-page-subtitle">Ubah data pengguna</div>
    </div>
    <div class="jxb-page-actions">
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>
<div class="row mt-3">
<div class="col-md-12">
<div class="card">

<form action="update.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?= $user['id'] ?>">
<div class="card-body">
 <div class="row">
  <div class="col-md-6">
   <label class="form-label">Nama Lengkap <span class="jxb-required">*</span></label>
   <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-control" required>
  </div>
  <div class="col-md-6">
   <label class="form-label">Username</label>
   <input type="text" value="<?= htmlspecialchars($user['username']) ?>" class="form-control" readonly>
  </div>


  <div class="col-md-6">
   <label class="form-label">Unit Kerja</label>
   <select name="kode_unit" class="form-select">
     <option value="<?= $user['unit_id'] ?>" selected><?= $user['nama_unit'] ?></option>
    <?php
     $sql1 = mysqli_query($conn,"select id, nama_unit from unit_kerja where id != " . (int)$user['unit_id']);
     while($res=mysqli_fetch_assoc($sql1)){
    ?>
     <option value="<?= $res['id'] ?>"><?= $res['nama_unit'] ?></option>
    <?php }; ?>

   </select>
  </div>


 </div>
 <br>
 <div class="row">
  <div class="col-md-6">
   <label class="form-label">Email</label>
   <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control">
  </div>
  <div class="col-md-6">
   <label class="form-label">Role</label>
   <select name="role" class="form-select">
    <option value="">Pilih Role</option>
    <option value="ADMIN"<?= $user['role']=='ADMIN'?'selected':'' ?>>ADMIN</option>
    <option value="KEPALA_SIA"<?= $user['role']=='KEPALA_SIA'?'selected':'' ?>>KEPALA SIA</option>
    <option value="AUDITOR"<?= $user['role']=='AUDITOR'?'selected':'' ?>>AUDITOR</option>
    <option value="AUDITEE"<?= $user['role']=='AUDITEE'?'selected':'' ?>>AUDITEE</option>
    <option value="DIREKSI"<?= $user['role']=='DIREKSI'?'selected':'' ?>>DIREKSI</option>
    <option value="KOMISARIS"<?= $user['role']=='KOMISARIS'?'selected':'' ?>>KOMISARIS</option>
   </select>
  </div>
 </div>
 <br>
 <div class="row">
  <div class="col-md-6">
   <label class="form-label">Foto Baru</label>
   <input type="file" name="foto" class="form-control">
  </div>
  <div class="col-md-6">
   <label class="form-label">Status</label>
   <div class="form-check">
    <input type="checkbox" name="aktif" value="1" <?= $user['aktif'] ? 'checked' : '' ?> class="form-check-input">
   <label class="form-check-label">Aktif</label>
  </div>
 </div>
</div>
</div>
<div class="card-footer">
 <button type="submit" class="btn btn-primary"> Update</button>
 <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</main>

<?php
include "../templates/footer.php";
?>
