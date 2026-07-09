<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_POST['id'];

$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
$ext = strtolower(pathinfo($_FILES['bukti']['name'],PATHINFO_EXTENSION));

if(!in_array($ext,$allowed))
{
    die("Format file tidak diperbolehkan");
}

$nama_file = date('YmdHis') . '_' . uniqid() . '.' . $ext;

if(
    move_uploaded_file(
        $_FILES['bukti']['tmp_name'],
        '../uploads/tindak_lanjut/' . $nama_file
    )
)
{
    mysqli_query($conn,"UPDATE audit_tindak_lanjut SET bukti_file='$nama_file', status='SELESAI' WHERE id=$id");

  logActivity(
    $conn,
    "Upload bukti Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
  );

}
else
{
    die("Upload file gagal");
}

header("Location:index.php?rekomendasi_id=".$rekomendasi_id."&upload=success");

exit;
