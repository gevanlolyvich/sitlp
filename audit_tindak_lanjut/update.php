<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_POST['id'];
$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$unit_id = (int)$_POST['unit_id'];
$pic = mysqli_real_escape_string($conn,$_POST['pic']);
$uraian = mysqli_real_escape_string($conn,$_POST['uraian_tindak_lanjut']);
$target = $_POST['target_selesai'];
$realisasi = $_POST['tanggal_realisasi'];
$status = $_POST['status'];

if(
$status=='SELESAI'
&&
empty($realisasi)
)
{
    $realisasi = date('Y-m-d');
}

mysqli_query($conn,"UPDATE audit_tindak_lanjut SET unit_id='$unit_id', pic='$pic', uraian_tindak_lanjut='$uraian', target_selesai='$target', tanggal_realisasi='$realisasi', status='$status' WHERE id='$id'");

logActivity(
    $conn,
    "Mengubah Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);

$_SESSION['success'] = "Tindak lanjut berhasil diperbarui.";
header("Location:index.php");

exit;
