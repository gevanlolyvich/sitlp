<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
$audit_id = (int)$_POST['audit_id'];

$jenis = mysqli_real_escape_string($conn,$_POST['jenis_dokumen']);
$allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
$ext = strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));

if(!in_array($ext,$allowed))
{
    die("Format file tidak diizinkan");
}

$namaAsli = $_FILES['file']['name'];

$namaBaru =
date('YmdHis')
. '_'
. uniqid()
. '.'
. $ext;

$folder =
'uploads/audit_lampiran/';

move_uploaded_file(
$_FILES['file']['tmp_name'],
'../'.$folder.$namaBaru
);

mysqli_query($conn,"INSERT INTO audit_lampiran(audit_id,jenis_dokumen,nama_file,file_path,uploaded_by)
       VALUES('$audit_id','$jenis','$namaAsli','$folder$namaBaru','".$_SESSION['user_id']."')");

header("Location:index.php?audit_id=".$audit_id);

exit;
