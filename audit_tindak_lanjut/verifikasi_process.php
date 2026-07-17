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

$qCheck = mysqli_query($conn, "SELECT bukti_file, verifikasi_status FROM audit_tindak_lanjut WHERE id=$id");
$tl = mysqli_fetch_assoc($qCheck);

if (empty($tl['bukti_file'])) {
    $_SESSION['error'] = "Belum ada upload bukti. Verifikasi tidak dapat dilakukan.";
    header("Location: verifikasi.php?id=" . $id);
    exit;
}

if ($tl['verifikasi_status'] != 'BELUM') {
    $_SESSION['error'] = "Tindak lanjut sudah diverifikasi.";
    header("Location: index.php");
    exit;
}

mysqli_query($conn,"UPDATE audit_tindak_lanjut SET
	verifikasi_status='$status',
	verifikasi_catatan='$catatan',
	verifikasi_oleh='$user_id',
	verifikasi_tanggal=NOW()
	WHERE id='$id'");

logActivity(
    $conn,
    "Verifikasi Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);

$_SESSION['success'] = "Verifikasi berhasil disimpan.";
header("Location:index.php");

exit;
