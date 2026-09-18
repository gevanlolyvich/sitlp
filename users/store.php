<?php

session_start();

require_once "../config/app.php";

require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$nama       = mysqli_real_escape_string($conn, trim($_POST['nama']));
$username   = mysqli_real_escape_string($conn, trim($_POST['username']));
$email      = mysqli_real_escape_string($conn, trim($_POST['email']));
$role       = mysqli_real_escape_string($conn, trim($_POST['role']));

$password   = $_POST['password'];
$confirm    = $_POST['confirm_password'];

$aktif = isset($_POST['aktif'])?1:0;

if($password !== $confirm)
{
    die("Konfirmasi password tidak sama");
}

$cek = mysqli_query($conn,"SELECT id FROM users WHERE username='$username'");

if(mysqli_num_rows($cek)>0){
    die("Username sudah digunakan");
}

$hash = password_hash($password,PASSWORD_DEFAULT);

$foto = null;

if(isset($_FILES['foto']) && $_FILES['foto']['error']==0){
    $maxSize = 2 * 1024 * 1024;
    if ($_FILES['foto']['size'] > $maxSize) {
        $_SESSION['error'] = "Ukuran file foto maksimal 2MB";
        header("Location: create.php");
        exit;
    }
    $ext = strtolower(pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION));
    $allow = ['jpg','jpeg','png'];
    if(!in_array($ext,$allow)){
        $_SESSION['error'] = "Format foto hanya JPG/JPEG/PNG";
        header("Location: create.php");
        exit;
    }
    $foto = uniqid().'.'.$ext;
    if(!move_uploaded_file($_FILES['foto']['tmp_name'],"../uploads/users/".$foto)){
        $_SESSION['error'] = "Gagal mengunggah foto";
        header("Location: create.php");
        exit;
    }
}

$role =
    $_POST['role'];
$unit_id =
    !empty($_POST['unit_id'])
    ?
    (int)$_POST['unit_id']
    :
    "NULL";
if(
    $role=='AUDITEE'
    &&
    empty($_POST['unit_id'])
)
{
    $_SESSION['error'] =
        "Unit kerja wajib dipilih untuk role AUDITEE";
    header(
        "Location:create.php"
    );
    exit;
}

//echo "INSERT INTO users(nama,username,email,password,role,foto,aktif,unit_id)
//	VALUES('$nama','$username','$email','$hash','$role','$foto','$aktif',$unit_id)";
//exit;
//mysql_real_escape_string()
mysqli_query($conn,"INSERT INTO users(nama,username,email,password,role,foto,aktif,unit_id)
	                       VALUES('$nama','$username','$email','$hash','$role','$foto','$aktif',$unit_id)");

$_SESSION['success'] = "User berhasil ditambahkan.";
header("Location: index.php");
