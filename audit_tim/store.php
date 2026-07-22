<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$audit_id   = (int)$_POST['audit_id'];
$auditor_id = (int)$_POST['auditor_id'];
$peran      = $_POST['peran'];
$allowedPeran = ['Anggota', 'Pengendali'];
if (!in_array($peran, $allowedPeran)) {
    $_SESSION['error'] = "Peran tidak valid.";
    header("Location: index.php?audit_id=" . $audit_id);
    exit;
}

$qCheck = mysqli_query($conn, "SELECT id FROM audit_tim WHERE audit_id=$audit_id AND auditor_id=$auditor_id");
if (mysqli_num_rows($qCheck) > 0) {
    $_SESSION['error'] = "Auditor sudah ditambahkan ke tim.";
    header("Location: index.php?audit_id=" . $audit_id);
    exit;
}

mysqli_query($conn, "INSERT INTO audit_tim (audit_id, auditor_id, peran) VALUES('$audit_id','$auditor_id','$peran')");

$_SESSION['success'] = "Anggota tim berhasil ditambahkan.";
header("Location: index.php?audit_id=" . $audit_id);
exit;
