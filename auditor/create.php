<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
hasRole(['ADMIN']);
include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">Tambah Auditor</div>
     <form action="store.php" method="post">
      <div class="card-body">
       <div class="mb-3">
        <label>NIP</label>
        <input type="text" name="nip" class="form-control">
       </div>
       <div class="mb-3">
        <label>Nama Auditor</label>
        <input type="text" name="nama" class="form-control" required>
       </div>
       <div class="mb-3">
        <label>Jabatan</label>
        <input type="text" name="jabatan" class="form-control">
       </div>
       <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
       </div>
       <div class="mb-3">
        <label>Telepon</label>
        <input type="text" name="telepon" class="form-control">
       </div>
      </div>
      <div class="card-footer">
       <button class="btn btn-primary"> Simpan</button>
       <a href="index.php" class="btn btn-secondary">Kembali</a>
      </div>
     </form>
    </div>
   </div>
  </div>
</main>

<?php include "../templates/footer.php"; ?>
