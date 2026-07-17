<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI']);

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT tl.*, u.nama_unit
	FROM audit_tindak_lanjut tl
	LEFT JOIN unit_kerja u ON tl.unit_id=u.id
	WHERE tl.id=$id");

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
<h3 class="card-title">Verifikasi Tindak Lanjut</h3>
</div>

<form action="verifikasi_process.php" method="post">

<input type="hidden" name="id" value="<?= $id ?>">

<input type="hidden" name="rekomendasi_id" value="<?= $tl['rekomendasi_id'] ?>">

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="250">Nomor TL</th>
<td><?= $tl['nomor_tindak_lanjut'] ?></td>
</tr>

<tr>
<th>Unit Kerja</th>
<td><?= $tl['nama_unit'] ?></td>
</tr>

<tr>
<th>PIC</th>
<td><?= $tl['pic'] ?></td>
</tr>

<tr>
<th>Status TL</th>
<td><?= $tl['status'] ?></td>
</tr>

<tr>
<th>Bukti</th>
<td>

<?php if($tl['bukti_file']){ ?>

<a href="../uploads/tindak_lanjut/<?= $tl['bukti_file'] ?>" target="_blank" class="btn btn-success btn-sm">Lihat Bukti</a>

<?php } else { ?>

<span class="text-danger">Belum Upload Bukti</span>

<?php } ?>

</td>
</tr>

</table>

<div class="mb-3">

<label>Status Verifikasi</label>

<select name="verifikasi_status" class="form-select" required>
<option value="DITERIMA">DITERIMA</option>
<option value="DITOLAK">DITOLAK</option>
</select>

</div>

<div class="mb-3">

<label>Catatan SPI</label>

<textarea name="verifikasi_catatan" class="form-control" rows="4"></textarea>

</div>

</div>

<div class="card-footer">

<button type="submit" class="btn btn-primary">Simpan Verifikasi</button>
<a href="index.php" class="btn btn-secondary">Kembali</a>

</div>

</form>

</div>

</div>
</div>
</main>

<?php
include "../templates/footer.php";
?>
