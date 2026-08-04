<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$nomor_tindak_lanjut = mysqli_real_escape_string($conn, $_POST['nomor_tindak_lanjut']);
$pic = mysqli_real_escape_string($conn,$_POST['pic']);
$uraian = mysqli_real_escape_string($conn,$_POST['uraian_tindak_lanjut']);
$target = $_POST['target_selesai'];
$status = 'Proses';
$created_by = $_SESSION['user_id'];

// Unit kerja mengikuti PKPT
$qRek = mysqli_query($conn, "SELECT r.id, t.audit_id, p.unit_id AS pkpt_unit_id
    FROM audit_rekomendasi r
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    LEFT JOIN audit_pemeriksaan ap ON ap.id=t.audit_id
    LEFT JOIN audit_program p ON p.id=ap.program_id
    WHERE r.id=$rekomendasi_id");
$rek = mysqli_fetch_assoc($qRek);
$unit_id = $rek ? (int)$rek['pkpt_unit_id'] : (int)$_POST['unit_id'];

// Validate target_selesai berada di rentang tanggal audit
if ($rek) {
    $audit_id = (int)$rek['audit_id'];
    $qAudit = mysqli_query($conn, "SELECT tanggal_mulai, tanggal_selesai FROM audit_pemeriksaan WHERE id=$audit_id");
    $auditData = mysqli_fetch_assoc($qAudit);
    if ($auditData) {
        if ($target < $auditData['tanggal_mulai']) {
            $_SESSION['error'] = "Target selesai tidak boleh sebelum tanggal mulai audit (" . $auditData['tanggal_mulai'] . ").";
            header("Location: create.php?rekomendasi_id=" . $rekomendasi_id);
            exit;
        }
        if ($target > $auditData['tanggal_selesai']) {
            $_SESSION['error'] = "Target selesai tidak boleh melebihi tanggal selesai audit (" . $auditData['tanggal_selesai'] . ").";
            header("Location: create.php?rekomendasi_id=" . $rekomendasi_id);
            exit;
        }
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
