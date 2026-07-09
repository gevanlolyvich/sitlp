<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$temuan_id = (int)$_POST['temuan_id'];

$nomor_rekomendasi = mysqli_real_escape_string($conn,$_POST['nomor_rekomendasi']);
$rekomendasi = mysqli_real_escape_string($conn,$_POST['rekomendasi']);
$prioritas = mysqli_real_escape_string($conn,$_POST['prioritas']);
$created_by = $_SESSION['user_id'];
mysqli_query($conn,"INSERT INTO audit_rekomendasi(temuan_id, nomor_rekomendasi, rekomendasi, prioritas, created_by)
	VALUES('$temuan_id','$nomor_rekomendasi','$rekomendasi','$prioritas','$created_by')");

$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Membuat Rekomendasi Audit",
    "audit_rekomendasi",
    $id
);

header("Location:../audit_temuan/detail.php?id=".$temuan_id);

exit;
