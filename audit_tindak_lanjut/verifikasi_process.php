<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI']);

$id = (int)$_POST['id'];
$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$status = mysqli_real_escape_string($conn,$_POST['verifikasi_status']);
$catatan = mysqli_real_escape_string($conn,$_POST['verifikasi_catatan']);
$user_id = $_SESSION['user_id'];

mysqli_query($conn,"UPDATE audit_tindak_lanjut SET
	verifikasi_status='$status',
	verifikasi_catatan='$catatan',
	verifikasi_oleh='$user_id',
	verifikasi_tanggal=NOW()
	WHERE id='$id'");


$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Verifikasi Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);


header("Location:index.php?rekomendasi_id=".$rekomendasi_id);

exit;
