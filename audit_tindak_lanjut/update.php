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
$unit_id = (int)$_POST['unit_id'];
$pic = '';
$uraian = mysqli_real_escape_string($conn,$_POST['uraian_tindak_lanjut']);
$target = $_POST['target_selesai'];
$status = $_POST['status'];

$qTemuanMap = mysqli_query($conn, "SELECT temuan_id FROM audit_rekomendasi WHERE id=$rekomendasi_id");
$tmap = mysqli_fetch_assoc($qTemuanMap);
$temuan_id = $tmap ? (int)$tmap['temuan_id'] : 0;

// Only Kepala SPI/Admin can change status
if (!in_array($_SESSION['role'], ['ADMIN','KEPALA_SIA'])) {
    $qCur = mysqli_query($conn, "SELECT status FROM audit_tindak_lanjut WHERE id=$id");
    $cur = mysqli_fetch_assoc($qCur);
    $status = $cur['status'];
}

// Check if locked (status = Sesuai)
$qCheck = mysqli_query($conn, "SELECT status FROM audit_tindak_lanjut WHERE id=$id");
$current = mysqli_fetch_assoc($qCheck);
if ($current['status'] == 'Sesuai') {
    $_SESSION['error'] = "Tidak dapat mengubah. Status sudah Sesuai (final).";
    header("Location: index.php?temuan_id=" . $temuan_id);
    exit;
}

// Validate target_selesai berada di rentang tanggal audit
$qRek = mysqli_query($conn, "SELECT r.id, t.audit_id FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=$rekomendasi_id");
$rek = mysqli_fetch_assoc($qRek);
if ($rek) {
    $audit_id = (int)$rek['audit_id'];

    blockLockedAudit($conn, $audit_id);

    $qAudit = mysqli_query($conn, "SELECT tanggal_mulai, tanggal_selesai FROM audit_pemeriksaan WHERE id=$audit_id");
    $auditData = mysqli_fetch_assoc($qAudit);
    if ($auditData) {
        if ($target < $auditData['tanggal_mulai']) {
            $_SESSION['error'] = "Target selesai tidak boleh sebelum tanggal mulai audit (" . $auditData['tanggal_mulai'] . ").";
            header("Location: edit.php?id=" . $id);
            exit;
        }
        if ($target > $auditData['tanggal_selesai']) {
            $_SESSION['error'] = "Target selesai tidak boleh melebihi tanggal selesai audit (" . $auditData['tanggal_selesai'] . ").";
            header("Location: edit.php?id=" . $id);
            exit;
        }
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
header("Location:index.php?temuan_id=" . $temuan_id);

exit;
