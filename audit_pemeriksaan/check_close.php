<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];

header('Content-Type: application/json');

/* check apakah ada temuan */
$qTemuan = mysqli_query($conn, "SELECT COUNT(*) jml FROM audit_temuan WHERE audit_id='$id'");
$temuan = mysqli_fetch_assoc($qTemuan);
if($temuan['jml']==0){
    echo json_encode([
        'ok' => false,
        'message' => "Audit belum memiliki temuan."
    ]);
    exit;
}

/* check apakah semua tindak lanjut sudah Sesuai */
$qTL = mysqli_query($conn, "
    SELECT COUNT(*) jml
    FROM audit_tindak_lanjut tl
    JOIN audit_rekomendasi r ON tl.rekomendasi_id = r.id
    JOIN audit_temuan t ON r.temuan_id = t.id
    WHERE t.audit_id = '$id' AND tl.status != 'Sesuai'
");
$tlBelum = mysqli_fetch_assoc($qTL);

if($tlBelum['jml'] > 0){
    echo json_encode([
        'ok' => false,
        'message' => "Masih ada " . $tlBelum['jml'] . " tindak lanjut yang statusnya belum Sesuai."
    ]);
    exit;
}

echo json_encode(['ok' => true]);