<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$temuan_id = (int)$_POST['temuan_id'];

$qAuditCheck = mysqli_query($conn,"SELECT audit_id FROM audit_temuan WHERE id=$temuan_id");
$auditCheck = mysqli_fetch_assoc($qAuditCheck);
blockLockedAudit($conn, (int)$auditCheck['audit_id']);

$ids  = $_POST['rekomendasi_id'] ?? [];
$vals = $_POST['rekomendasi'] ?? [];
$count = min(count($ids), count($vals));
for($i = 0; $i < $count; $i++){
    $rid = (int)$ids[$i];
    $isi = trim($vals[$i]);
    if($rid <= 0 || $isi === '') continue;

    $rekomendasi = mysqli_real_escape_string($conn, $isi);
    mysqli_query($conn, "UPDATE audit_rekomendasi SET
        rekomendasi = '$rekomendasi'
    WHERE id = $rid AND temuan_id = $temuan_id");

    logActivity(
        $conn,
        "Mengubah Rekomendasi Audit",
        "audit_rekomendasi",
        $rid
    );
}

$created_by = $_SESSION['user_id'];
$rekomendasi_baru = $_POST['rekomendasi_baru'] ?? [];
foreach($rekomendasi_baru as $isi){
    $isi = trim($isi);
    if($isi === '') continue;

    $rekom = mysqli_real_escape_string($conn,$isi);
    mysqli_query($conn,"INSERT INTO audit_rekomendasi(temuan_id, rekomendasi, created_by)
        VALUES('$temuan_id','$rekom','$created_by')");

    $new_id = mysqli_insert_id($conn);
    logActivity(
        $conn,
        "Membuat Rekomendasi Audit",
        "audit_rekomendasi",
        $new_id
    );
}

$allIds = [];
$qAll = mysqli_query($conn,"SELECT id FROM audit_rekomendasi WHERE temuan_id=$temuan_id");
while($row = mysqli_fetch_assoc($qAll)){ $allIds[] = (int)$row['id']; }

$submittedIds = [];
foreach($ids as $rid){
    $rid = (int)$rid;
    if($rid > 0){ $submittedIds[] = $rid; }
}

foreach(array_diff($allIds, $submittedIds) as $rid){
    mysqli_query($conn,"DELETE FROM audit_rekomendasi WHERE id=$rid AND temuan_id=$temuan_id");
    logActivity(
        $conn,
        "Menghapus Rekomendasi Audit",
        "audit_rekomendasi",
        $rid
    );
}

$_SESSION['success'] = "Rekomendasi berhasil diperbarui.";
header("Location: ../audit_temuan/detail.php?id=" . $temuan_id);
exit;