<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int)$_POST['id'];
$tanggal = $_POST['tanggal'];
$keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

// Cek duplikat tanggal (kecuali dirinya sendiri)
$qCek = mysqli_query($conn, "SELECT id FROM hari_libur WHERE tanggal='$tanggal' AND id != $id");
if (mysqli_num_rows($qCek) > 0) {
    $_SESSION['error'] = "Tanggal $tanggal sudah terdaftar sebagai hari libur.";
    header("Location: edit.php?id=$id");
    exit;
}

mysqli_query($conn, "UPDATE hari_libur SET tanggal='$tanggal', keterangan='$keterangan' WHERE id=$id");

logActivity($conn, "Mengubah hari libur: $tanggal - $keterangan", 'hari_libur', $id);

$_SESSION['success'] = "Hari libur berhasil diubah.";
header("Location: index.php");
exit;
