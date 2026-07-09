<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];

$rekomendasi_id = (int)$_GET['rekomendasi_id'];

$q = mysqli_query($conn,"SELECT bukti_file FROM audit_tindak_lanjut WHERE id=$id");
$d = mysqli_fetch_assoc($q);

if(
!empty($d['bukti_file'])
)
{
    @unlink(
    "../uploads/tindak_lanjut/" .
    $d['bukti_file']
    );
}

mysqli_query($conn,"DELETE FROM audit_tindak_lanjut WHERE id=$id");


$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "menghapus Audit Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);


header("Location:index.php?rekomendasi_id=".$rekomendasi_id);

exit;
