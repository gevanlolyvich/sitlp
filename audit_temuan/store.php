<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$audit_id = (int)$_POST['audit_id'];

blockLockedAudit($conn, $audit_id);

$nomor_temuan =
mysqli_real_escape_string(
$conn,
$_POST['nomor_temuan']
);

$judul_temuan =
mysqli_real_escape_string(
$conn,
$_POST['judul_temuan']
);

$kondisi =
mysqli_real_escape_string(
$conn,
$_POST['kondisi']
);

$kriteria =
mysqli_real_escape_string(
$conn,
$_POST['kriteria']
);

$sebab =
mysqli_real_escape_string(
$conn,
$_POST['sebab']
);

$akibat =
mysqli_real_escape_string(
$conn,
$_POST['akibat']
);

$tingkat_risiko =
$_POST['tingkat_risiko'];

$status = 'OPEN';

$created_by = $_SESSION['user_id'];

mysqli_query($conn,"INSERT INTO audit_temuan
(audit_id,nomor_temuan,judul_temuan,kondisi,kriteria,sebab,akibat,tingkat_risiko,status,created_by)
VALUES
('$audit_id','$nomor_temuan','$judul_temuan','$kondisi','$kriteria','$sebab','$akibat','$tingkat_risiko','$status','$created_by')");


$id = mysqli_insert_id($conn);

mysqli_query($conn,"UPDATE audit_pemeriksaan SET status='BERJALAN' WHERE id='$audit_id' AND status='DRAFT'");

logActivity(
    $conn,
    "Membuat Temuan Audit",
    "audit_temuan",
    $id
);


header(
"Location:index.php?audit_id=".$audit_id
);

exit;
