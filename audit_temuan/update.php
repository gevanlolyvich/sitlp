<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_POST['id'];
$audit_id = (int)$_POST['audit_id'];

$nomor_temuan = mysqli_real_escape_string($conn, $_POST['nomor_temuan']);
$judul_temuan = mysqli_real_escape_string($conn, $_POST['judul_temuan']);
$kondisi      = mysqli_real_escape_string($conn, $_POST['kondisi']);
$kriteria     = mysqli_real_escape_string($conn, $_POST['kriteria']);
$sebab        = mysqli_real_escape_string($conn, $_POST['sebab']);
$akibat       = mysqli_real_escape_string($conn, $_POST['akibat']);
$tingkat_risiko = $_POST['tingkat_risiko'];
$status       = 'OPEN';

mysqli_query($conn, "UPDATE audit_temuan SET
    nomor_temuan = '$nomor_temuan',
    judul_temuan = '$judul_temuan',
    kondisi = '$kondisi',
    kriteria = '$kriteria',
    sebab = '$sebab',
    akibat = '$akibat',
    tingkat_risiko = '$tingkat_risiko',
    status = '$status'
WHERE id = $id");

logActivity(
    $conn,
    "Mengubah Temuan Audit",
    "audit_temuan",
    $id
);

$_SESSION['success'] = "Temuan berhasil diperbarui.";
header("Location: index.php?audit_id=" . $audit_id);
exit;
