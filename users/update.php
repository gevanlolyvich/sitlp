<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$id      = (int)$_POST['id'];
$nama    = mysqli_real_escape_string($conn, trim($_POST['nama']));
$email   = mysqli_real_escape_string($conn, trim($_POST['email']));
$role    = mysqli_real_escape_string($conn, trim($_POST['role']));

$unit_raw = trim($_POST['kode_unit'] ?? '');
if ($role == 'AUDITEE' && $unit_raw === '') {
    $_SESSION['error'] = "Unit kerja wajib dipilih untuk role AUDITEE";
    header("Location: edit.php?id=$id");
    exit;
}
$unit_id = ($unit_raw !== '' && ctype_digit($unit_raw)) ? (int)$unit_raw : "NULL";

$aktif = isset($_POST['aktif'])?1:0;
$foto_sql = "";
$foto_hapus = "";

if(isset($_FILES['foto']) && $_FILES['foto']['error']==0){
    $maxSize = 2 * 1024 * 1024;
    if ($_FILES['foto']['size'] > $maxSize) {
        $_SESSION['error'] = "Ukuran file foto maksimal 2MB";
        header("Location: edit.php?id=$id");
        exit;
    }
    $ext = strtolower(pathinfo($_FILES['foto']['name'],PATHINFO_EXTENSION));
    $allow = ['jpg','jpeg','png'];
    if(!in_array($ext,$allow)){
        $_SESSION['error'] = "Format foto hanya JPG/JPEG/PNG";
        header("Location: edit.php?id=$id");
        exit;
    }
    $foto = uniqid().'.'.$ext;
    if(!move_uploaded_file($_FILES['foto']['tmp_name'],"../uploads/users/".$foto)){
        $_SESSION['error'] = "Gagal mengunggah foto";
        header("Location: edit.php?id=$id");
        exit;
    }
    $foto_sql = ", foto='$foto'";
    $sql_cek = mysqli_query($conn, "SELECT foto FROM users WHERE id=$id LIMIT 1");
    $row_cek = mysqli_fetch_assoc($sql_cek);
    if($row_cek && !empty($row_cek['foto']) && $row_cek['foto'] !== $foto){
        $foto_hapus = $row_cek['foto'];
    }
}

$result = mysqli_query($conn,"UPDATE users
	SET nama='$nama', email='$email', unit_id=$unit_id, role='$role', aktif='$aktif' $foto_sql
	WHERE id='$id'");

if(!$result && !empty($foto)){
    @unlink("../uploads/users/".$foto);
}

if($result && $foto_hapus !== ""){
    $sql_check = mysqli_query($conn, "SELECT foto FROM users WHERE id=$id LIMIT 1");
    $row_check = mysqli_fetch_assoc($sql_check);
    if($row_check && $row_check['foto'] === $foto){
        $file_lama = "../uploads/users/".$foto_hapus;
        if(file_exists($file_lama)){
            @unlink($file_lama);
        }
    }
}

if($result){
    $_SESSION['success'] = "User berhasil diubah.";
} else {
    $_SESSION['error'] = "Gagal menyimpan perubahan user.";
}
header("Location: index.php");
exit;
