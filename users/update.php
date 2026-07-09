<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$id      = (int)$_POST['id'];
$nama    = trim($_POST['nama']);
$email   = trim($_POST['email']);
$unit_id = trim($_POST['kode_unit']);
$role    = trim($_POST['role']);

$aktif = isset($_POST['aktif'])?1:0;
$foto_sql = "";

if(isset($_FILES['foto'])&&$_FILES['foto']['error']==0){
 $ext = strtolower(pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION));
 $allow = ['jpg','jpeg','png'];
 if(in_array($ext,$allow)){
  $foto = uniqid().'.'.$ext;
  move_uploaded_file($_FILES['foto']['tmp_name'],"../uploads/users/".$foto);
  $foto_sql = ", foto='$foto'";
 }
}

mysqli_query($conn,"UPDATE users
	SET nama='$nama', email='$email', unit_id='$unit_id', role='$role', aktif='$aktif' $foto_sql
	WHERE id='$id'");

header("Location: index.php");
exit;
