<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];
$q = mysqli_query($conn,"SELECT * FROM audit_tindak_lanjut WHERE id=$id");
$tl = mysqli_fetch_assoc($q);

if(!$tl)
{
    die("Data tidak ditemukan");
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">
<div class="card mt-3">
<div class="card-header">
<h3 class="card-title">Edit Tindak Lanjut</h3>
</div>
<form action="update.php" method="post">
<input type="hidden" name="id" value="<?= $tl['id'] ?>">
<input type="hidden" name="rekomendasi_id" value="<?= $tl['rekomendasi_id'] ?>">
<div class="card-body">
<div class="mb-3">
<label>Nomor Tindak Lanjut</label>
<input type="text" class="form-control" value="<?= $tl['nomor_tindak_lanjut'] ?>" readonly>
</div>
<div class="mb-3">
<label>PIC</label>
<input type="text" name="pic" class="form-control" value="<?= htmlspecialchars($tl['pic']) ?>" required>
</div>
<div class="mb-3">
<label>Uraian Tindak Lanjut</label>
<textarea name="uraian_tindak_lanjut" class="form-control" rows="5" required><?= htmlspecialchars($tl['uraian_tindak_lanjut']) ?></textarea>
</div>
<div class="row">
<div class="col-md-4">
<label>Target Selesai</label>
<input type="date" name="target_selesai" value="<?= $tl['target_selesai'] ?>" class="form-control">
</div>
<div class="col-md-4">
<label>Tanggal Realisasi</label>
<input type="date" name="tanggal_realisasi" value="<?= $tl['tanggal_realisasi'] ?>" class="form-control">
</div>
<div class="col-md-4">
<label>Status</label>
<select name="status" class="form-select">
 <option value="OPEN" <?= $tl['status']=='OPEN'?'selected':'' ?>>OPEN</option>
 <option value="PROSES"<?= $tl['status']=='PROSES'?'selected':'' ?>>PROSES</option>
 <option value="SELESAI"<?= $tl['status']=='SELESAI'?'selected':'' ?>>SELESAI</option>
</select>
</div>
</div>
</div>
<div class="card-footer">
<button type="submit" class="btn btn-primary">Update</button>
<a href="index.php?rekomendasi_id=<?= $tl['rekomendasi_id'] ?>" class="btn btn-secondary">Kembali</a>
</div>
</form>
</div>
</div>
</div>
</main>

<?php
include "../templates/footer.php";
?>
