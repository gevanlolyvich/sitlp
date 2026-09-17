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
<div class="jxb-page-header">
    <div>
        <h1 class="jxb-page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Hari Libur</h1>
        <div class="jxb-page-subtitle">Buat data hari libur baru</div>
    </div>
    <div class="jxb-page-actions">
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>
<div class="card mt-3">
<form action="store.php" method="post">
<div class="card-body">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Tanggal Libur <span class="jxb-required">*</span></label>
<input type="date" name="tanggal" class="form-control" required>
</div>
<div class="col-md-6">
<label class="form-label">Keterangan <span class="jxb-required">*</span></label>
<input type="text" name="keterangan" class="form-control" placeholder="Contoh: Hari Raya Idul Fitri" required>
</div>
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
<a href="index.php" class="btn btn-outline-secondary">Kembali</a>
</div>
</form>
    </div>
</main>
<?php include "../templates/footer.php"; ?>
