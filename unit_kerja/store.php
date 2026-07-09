<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$kode_unit = trim($_POST['kode_unit']);
$nama_unit = trim($_POST['nama_unit']);
$email     = trim($_POST['email']);
$telepon   = trim($_POST['telepon']);
$alamat    = trim($_POST['alamat']);

$kode_unit = mysqli_real_escape_string($conn, $kode_unit);
$nama_unit = mysqli_real_escape_string($conn, $nama_unit);
$email     = mysqli_real_escape_string($conn, $email);
$telepon   = mysqli_real_escape_string($conn, $telepon);
$alamat    = mysqli_real_escape_string($conn, $alamat);
$sql = "
    INSERT INTO unit_kerja
    (
        kode_unit,
        nama_unit,
        email,
        telepon,
        alamat
    )
    VALUES
    (
        '$kode_unit',
        '$nama_unit',
        '$email',
        '$telepon',
        '$alamat'
    )
";

if (mysqli_query($conn, $sql)) {
    logActivity(
        $conn,
        "Menambahkan Unit Kerja",
        "unit_kerja",
        mysqli_insert_id($conn)
    );
    $_SESSION['success'] =
        "Data unit kerja berhasil ditambahkan.";
    header("Location:index.php");
} else {
   if (mysqli_errno($conn) == 1062) {
    $_SESSION['error'] =
        "Kode unit sudah digunakan.";
   } else {
    $_SESSION['error'] =
        "Gagal menyimpan data: " .
        mysqli_error($conn);
   }	
	$_SESSION['error'] =
        "Gagal menambahkan unit kerja: " .
        mysqli_error($conn);
    header("Location:create.php");
}

exit;

