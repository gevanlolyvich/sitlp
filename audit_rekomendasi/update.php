<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_POST['id'];
$temuan_id = (int)$_POST['temuan_id'];

$nomor_rekomendasi = mysqli_real_escape_string($conn, $_POST['nomor_rekomendasi']);
$rekomendasi = mysqli_real_escape_string($conn, $_POST['rekomendasi']);
$prioritas = mysqli_real_escape_string($conn, $_POST['prioritas']);

mysqli_query($conn, "UPDATE audit_rekomendasi SET
    nomor_rekomendasi = '$nomor_rekomendasi',
    rekomendasi = '$rekomendasi',
    prioritas = '$prioritas'
WHERE id = $id");

logActivity(
    $conn,
    "Mengubah Rekomendasi Audit",
    "audit_rekomendasi",
    $id
);

$_SESSION['success'] = "Rekomendasi berhasil diperbarui.";
header("Location: ../audit_temuan/detail.php?id=" . $temuan_id);
exit;
