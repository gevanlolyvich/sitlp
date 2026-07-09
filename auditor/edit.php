<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
hasRole(['ADMIN']);

$id = (int)$_GET['id'];
$q = mysqli_query($conn,"SELECT * FROM auditor WHERE id=$id");
$auditor = mysqli_fetch_assoc($q);
if(!$auditor){
    die("User tidak ditemukan");
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="row mt-3">
    <div class="col-md-12">
     <div class="card">
      <form action="update.php" method="post">
      <div class="card-header">
       <h3 class="card-title">Edit Auditor</h3>
      </div>
      <input type="hidden" name="id" value="<?= $auditor['id'] ?>">
      <div class="card-body">
       <div class="row">
        <div class="col-md-6">
         <label>NIP</label>
         <input type="text" name="nip" value="<?= htmlspecialchars($auditor['nip']) ?>" class="form-control">
        </div>
        <div class="col-md-6">
         <label>Nama Auditor</label>
         <input type="text" name="nama" value="<?= htmlspecialchars($auditor['nama_auditor']) ?>" class="form-control" required>
        </div>
       </div>
       <br>
       <div class="row">
        <div class="col-md-6">
         <label>Jabatan</label>
         <input type="text" name="jabatan" value="<?= htmlspecialchars($auditor['jabatan']) ?>" class="form-control">
        </div>
        <div class="col-md-6">
         <label>Email</label>
         <input type="email" name="email" value="<?= htmlspecialchars($auditor['email']) ?>" class="form-control">
        </div>
       </div>
       <br>
       <div class="row">
        <div class="col-md-6">
         <label>Telepon</label>
         <input type="text" name="telepon" value="<?= htmlspecialchars($auditor['telepon']) ?>" class="form-control">
        </div>
        <div class="col-md-6">
         <div class="form-check mt-4">
          <input type="checkbox" name="aktif" value="1" checked class="form-check-input">
          <label class="form-check-label">Aktif</label>
         </div>
        </div>
       </div>
      </div>
      <div class="card-footer">
       <button type="submit" class="btn btn-primary">Update</button>
       <a href="index.php" class="btn btn-secondary">Kembali</a>
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
