<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
<div class="card mt-3">
<div class="card-header">
<h3 class="card-title">Tambah Hari Libur</h3>
</div>
<form action="store.php" method="post">
<div class="card-body">
<div class="mb-3">
<label>Tanggal Libur</label>
<input type="date" name="tanggal" class="form-control" required>
</div>
<div class="mb-3">
<label>Keterangan</label>
<input type="text" name="keterangan" class="form-control" placeholder="Contoh: Hari Raya Idul Fitri" required>
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
<a href="index.php" class="btn btn-secondary">Kembali</a>
</div>
</form>
    </div>
</main>
<?php include "../templates/footer.php"; ?>
