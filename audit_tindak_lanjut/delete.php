<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$id = (int)$_GET['id'];

$rekomendasi_id = (int)$_GET['rekomendasi_id'];

$qTemuanMap = mysqli_query($conn, "SELECT temuan_id FROM audit_rekomendasi WHERE id=$rekomendasi_id");
$tmap = mysqli_fetch_assoc($qTemuanMap);
$temuan_id = $tmap ? (int)$tmap['temuan_id'] : 0;

// Check if allowed to delete (only if status = Proses)
$qCheck = mysqli_query($conn,"SELECT status, bukti_file, rekomendasi_id FROM audit_tindak_lanjut WHERE id=$id");
$d = mysqli_fetch_assoc($qCheck);
if (!$d) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$qAuditTrace = mysqli_query($conn,"SELECT t.audit_id FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=" . (int)$d['rekomendasi_id']);
$auditTrace = mysqli_fetch_assoc($qAuditTrace);
blockLockedAudit($conn, (int)$auditTrace['audit_id']);
if ($d['status'] != 'Proses') {
    $_SESSION['error'] = "Hapus hanya diizinkan saat status masih Proses.";
    header("Location: index.php?temuan_id=" . $temuan_id);
    exit;
}

if(
!empty($d['bukti_file'])
)
{
    @unlink(
    "../uploads/tindak_lanjut/" .
    basename($d['bukti_file'])
    );
}

mysqli_query($conn,"DELETE FROM audit_tindak_lanjut WHERE id=$id");

logActivity(
    $conn,
    "menghapus Audit Tindak Lanjut",
    "audit_tindak_lanjut",
    $id
);

$_SESSION['success'] = "Tindak lanjut berhasil dihapus.";
header("Location:index.php?temuan_id=".$temuan_id);

exit;
