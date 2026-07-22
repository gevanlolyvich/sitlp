<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$nomor_tindak_lanjut = mysqli_real_escape_string($conn, $_POST['nomor_tindak_lanjut']);
$unit_id = (int)$_POST['unit_id'];
$pic = mysqli_real_escape_string($conn,$_POST['pic']);
$uraian = mysqli_real_escape_string($conn,$_POST['uraian_tindak_lanjut']);
$target = $_POST['target_selesai'];
$status = 'Proses';
$created_by = $_SESSION['user_id'];

// Validate target_selesai <= tanggal_selesai audit
$qRek = mysqli_query($conn, "SELECT r.id, t.audit_id FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=$rekomendasi_id");
$rek = mysqli_fetch_assoc($qRek);
if ($rek) {
    $audit_id = (int)$rek['audit_id'];
    $qAudit = mysqli_query($conn, "SELECT tanggal_selesai FROM audit_pemeriksaan WHERE id=$audit_id");
    $auditData = mysqli_fetch_assoc($qAudit);
    if ($auditData && $target > $auditData['tanggal_selesai']) {
        $_SESSION['error'] = "Target selesai tidak boleh melebihi tanggal selesai audit (" . $auditData['tanggal_selesai'] . ").";
        header("Location: create.php?rekomendasi_id=" . $rekomendasi_id);
        exit;
    }
}

mysqli_query($conn,"
INSERT INTO audit_tindak_lanjut
(
 rekomendasi_id,
 nomor_tindak_lanjut,
 unit_id,
 pic,
 uraian_tindak_lanjut,
 target_selesai,
 status,
 created_by
)
VALUES
(
 '$rekomendasi_id',
 '$nomor_tindak_lanjut',
 '$unit_id',
 '$pic',
 '$uraian',
 '$target',
 '$status',
 '$created_by'
)");


$id = mysqli_insert_id($conn);

// Insert initial history log
mysqli_query($conn,"INSERT INTO audit_tindak_lanjut_log (tindak_lanjut_id, aksi, status_baru, keterangan, dibuat_oleh, dibuat_pada) VALUES ('$id', 'buat', 'Proses', 'Tindak lanjut dibuat', '$created_by', NOW())");

logActivity(
    $conn,
    "Membuat Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);


header("Location:index.php?rekomendasi_id=".$rekomendasi_id);

exit;
