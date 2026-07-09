<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$nomor_tindak_lanjut = mysqli_real_escape_string($conn,$_POST['nomor_tindak_lanjut']);
$unit_id = (int)$_POST['unit_id'];
$pic = mysqli_real_escape_string($conn,$_POST['pic']);
$uraian = mysqli_real_escape_string($conn,$_POST['uraian_tindak_lanjut']);
$target = $_POST['target_selesai'];
$status = $_POST['status'];
$created_by = $_SESSION['user_id'];

mysqli_query($conn,"
INSERT INTO audit_tindak_lanjut
(
 rekomendasi_id,
 nomor_tindak_lanjut,
 unit_id,
 pic,
 uraian_tindak_lanjut,
 target_selesai,
 status,
 created_by
)
VALUES
(
 '$rekomendasi_id',
 '$nomor_tindak_lanjut',
 '$unit_id',
 '$pic',
 '$uraian',
 '$target',
 '$status',
 '$created_by'
)");


$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Membuat Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);


header("Location:index.php?rekomendasi_id=".$rekomendasi_id);

exit;
