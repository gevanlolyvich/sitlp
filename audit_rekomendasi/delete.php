<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT r.id, r.temuan_id, t.audit_id FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=$id");
$rekomendasi = mysqli_fetch_assoc($q);

if(!$rekomendasi){
    $_SESSION['error'] = "Rekomendasi tidak ditemukan.";
    header("Location: ../audit_temuan/index.php");
    exit;
}

blockLockedAudit($conn, (int)$rekomendasi['audit_id']);

mysqli_query($conn,"DELETE FROM audit_rekomendasi WHERE id=$id");

logActivity(
    $conn,
    "Menghapus Rekomendasi Audit",
    "audit_rekomendasi",
    $id
);

$_SESSION['success'] = "Rekomendasi berhasil dihapus.";
header("Location: ../audit_temuan/detail.php?id=" . $rekomendasi['temuan_id']);
exit;