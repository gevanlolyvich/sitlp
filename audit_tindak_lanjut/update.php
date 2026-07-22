<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_POST['id'];
$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$unit_id = (int)$_POST['unit_id'];
$pic = mysqli_real_escape_string($conn,$_POST['pic']);
$uraian = mysqli_real_escape_string($conn,$_POST['uraian_tindak_lanjut']);
$target = $_POST['target_selesai'];
$status = $_POST['status'];

// Only Kepala SPI/Admin can change status
if (!in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI'])) {
    $qCur = mysqli_query($conn, "SELECT status FROM audit_tindak_lanjut WHERE id=$id");
    $cur = mysqli_fetch_assoc($qCur);
    $status = $cur['status'];
}

// Check if locked (status = Sesuai)
$qCheck = mysqli_query($conn, "SELECT status FROM audit_tindak_lanjut WHERE id=$id");
$current = mysqli_fetch_assoc($qCheck);
if ($current['status'] == 'Sesuai') {
    $_SESSION['error'] = "Tidak dapat mengubah. Status sudah Sesuai (final).";
    header("Location: index.php?rekomendasi_id=" . $rekomendasi_id);
    exit;
}

// Validate target_selesai
$qRek = mysqli_query($conn, "SELECT r.id, t.audit_id FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=$rekomendasi_id");
$rek = mysqli_fetch_assoc($qRek);
if ($rek) {
    $audit_id = (int)$rek['audit_id'];
    $qAudit = mysqli_query($conn, "SELECT tanggal_selesai FROM audit_pemeriksaan WHERE id=$audit_id");
    $auditData = mysqli_fetch_assoc($qAudit);
    if ($auditData && $target > $auditData['tanggal_selesai']) {
        $_SESSION['error'] = "Target selesai tidak boleh melebihi tanggal selesai audit (" . $auditData['tanggal_selesai'] . ").";
        header("Location: edit.php?id=" . $id);
        exit;
    }
}

// Validate status is one of the allowed values
$allowedStatus = ['Proses', 'Sesuai', 'Belum Sesuai', 'Belum Ditindak Lanjut', 'Tidak Dapat Ditindak Lanjut'];
if (!in_array($status, $allowedStatus)) {
    $status = 'Proses';
}

mysqli_query($conn,"UPDATE audit_tindak_lanjut SET unit_id='$unit_id', pic='$pic', uraian_tindak_lanjut='$uraian', target_selesai='$target', status='$status' WHERE id='$id'");

logActivity(
    $conn,
    "Mengubah Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);

$_SESSION['success'] = "Tindak lanjut berhasil diperbarui.";
header("Location:index.php?rekomendasi_id=" . $rekomendasi_id);

exit;
