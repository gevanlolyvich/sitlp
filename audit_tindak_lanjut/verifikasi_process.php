<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$id = (int)$_POST['id'];
$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$status = mysqli_real_escape_string($conn, $_POST['status']);
$catatan_spi = mysqli_real_escape_string($conn, $_POST['catatan_spi']);
$user_id = $_SESSION['user_id'];

$nilai_penyerahan = null;
if ($status === 'Sesuai') {
    $nilai_input = isset($_POST['nilai_penyerahan']) ? str_replace(',', '.', trim($_POST['nilai_penyerahan'])) : '';
    $nilai_penyerahan = ($nilai_input === '' || !is_numeric($nilai_input)) ? 0.00 : (float)$nilai_input;
}

$qTemuanMap = mysqli_query($conn, "SELECT temuan_id FROM audit_rekomendasi WHERE id=$rekomendasi_id");
$tmap = mysqli_fetch_assoc($qTemuanMap);
$temuan_id = $tmap ? (int)$tmap['temuan_id'] : 0;

// Validate status is one of the allowed values
$allowedStatus = ['Proses', 'Sesuai', 'Belum Sesuai', 'Belum Ditindak Lanjut', 'Tidak Dapat Ditindak Lanjut'];
if (!in_array($status, $allowedStatus)) {
    $_SESSION['error'] = "Status tidak valid.";
    header("Location: verifikasi.php?id=" . $id);
    exit;
}

// Check current status
$qCheck = mysqli_query($conn, "SELECT status FROM audit_tindak_lanjut WHERE id=$id");
$tl = mysqli_fetch_assoc($qCheck);

if (!$tl) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$status_lama = $tl['status'];

// If already Sesuai, lock
if ($status_lama == 'Sesuai') {
    $_SESSION['error'] = "Status sudah Sesuai (final). Tidak dapat diubah lagi.";
    header("Location: index.php");
    exit;
}

// Insert history log entry
mysqli_query($conn, "INSERT INTO audit_tindak_lanjut_log (tindak_lanjut_id, aksi, status_lama, status_baru, catatan_spi, nilai_penyerahan, keterangan, dibuat_oleh, dibuat_pada) VALUES ('$id', 'verifikasi', '$status_lama', '$status', '$catatan_spi', " . ($nilai_penyerahan === null ? 'NULL' : number_format($nilai_penyerahan, 2, '.', '')) . ", 'Verifikasi Tim SIA', '$user_id', NOW())");

// Update main table status
mysqli_query($conn, "UPDATE audit_tindak_lanjut SET
    status = '$status',
    catatan_spi = '$catatan_spi',
    nilai_penyerahan = " . ($nilai_penyerahan === null ? 'NULL' : number_format($nilai_penyerahan, 2, '.', '')) . "
WHERE id = '$id'");

logActivity(
    $conn,
    "Review Tindak Lanjut - Status: " . $status,
    "audit_tindak_lanjut",
    $id
);

$_SESSION['success'] = "Review berhasil disimpan. Status: " . $status;
header("Location: index.php?temuan_id=" . $temuan_id);

exit;
