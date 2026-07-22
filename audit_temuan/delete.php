<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_GET['id'];
$audit_id = (int)$_GET['audit_id'];

$q = mysqli_query($conn,"SELECT id FROM audit_temuan WHERE id=$id");
if (!mysqli_num_rows($q)) {
    $_SESSION['error'] = "Temuan tidak ditemukan.";
    header("Location: index.php?audit_id=" . $audit_id);
    exit;
}

mysqli_query($conn,"DELETE FROM audit_rekomendasi WHERE temuan_id=$id");
mysqli_query($conn,"DELETE FROM audit_temuan WHERE id=$id");

logActivity(
    $conn,
    "Menghapus Temuan Audit",
    "audit_temuan",
    $id
);

$_SESSION['success'] = "Temuan berhasil dihapus.";
header("Location: index.php?audit_id=" . $audit_id);
exit;