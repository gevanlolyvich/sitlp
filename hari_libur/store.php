<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$tanggal = $_POST['tanggal'];
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
$created_by = (int)$_SESSION['user_id'];

// Cek duplikat tanggal
$qCek = mysqli_query($conn, "SELECT id FROM hari_libur WHERE tanggal='$tanggal'");
if (mysqli_num_rows($qCek) > 0) {
    $_SESSION['error'] = "Tanggal $tanggal sudah terdaftar sebagai hari libur.";
    header("Location: create.php");
    exit;
}

mysqli_query($conn, "INSERT INTO hari_libur (tanggal, keterangan, created_by) VALUES ('$tanggal', '$keterangan', '$created_by')");

logActivity($conn, "Menambah hari libur: $tanggal - $keterangan", 'hari_libur', mysqli_insert_id($conn));

$_SESSION['success'] = "Hari libur berhasil ditambahkan.";
header("Location: index.php");
exit;
