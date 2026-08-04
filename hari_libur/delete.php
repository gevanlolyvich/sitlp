<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int)$_GET['id'];
$q = mysqli_query($conn, "SELECT tanggal, keterangan FROM hari_libur WHERE id=$id");
$r = mysqli_fetch_assoc($q);
if (!$r) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

mysqli_query($conn, "DELETE FROM hari_libur WHERE id=$id");

logActivity($conn, "Menghapus hari libur: {$r['tanggal']} - {$r['keterangan']}", 'hari_libur', $id);

$_SESSION['success'] = "Hari libur berhasil dihapus.";
header("Location: index.php");
exit;
