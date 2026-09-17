<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int)$_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM hari_libur WHERE id=$id");
$r = mysqli_fetch_assoc($q);
if (!$r) {
    die("Data tidak ditemukan");
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
        <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Hari Libur</h1>
        <div class="jxb-page-subtitle">Ubah data hari libur</div>
    </div>
    <div class="jxb-page-actions">
        <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
</div>
<div class="card mt-3">
<form action="update.php" method="post">
<input type="hidden" name="id" value="<?= $r['id'] ?>">
<div class="card-body">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Tanggal Libur <span class="jxb-required">*</span></label>
<input type="date" name="tanggal" class="form-control" value="<?= $r['tanggal'] ?>" required>
</div>
<div class="col-md-6">
<label class="form-label">Keterangan <span class="jxb-required">*</span></label>
<input type="text" name="keterangan" class="form-control" value="<?= htmlspecialchars($r['keterangan']) ?>" required>
</div>
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
<a href="index.php" class="btn btn-outline-secondary">Kembali</a>
</div>
</form>
    </div>
</main>
<?php include "../templates/footer.php"; ?>
