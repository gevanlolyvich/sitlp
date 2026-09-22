<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$qRow = mysqli_query($conn, "SELECT * FROM lhp WHERE id=$id");
$r = mysqli_fetch_assoc($qRow);
$redirect = 'index.php';
if ($r) {
    $redirect = "index.php?sumber=" . $r['sumber'];
    if ($r['bukti_file']) {
        $file = __DIR__ . "/../uploads/tl_lhp/" . $r['bukti_file'];
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}

mysqli_query($conn, "DELETE FROM lhp WHERE id=$id");
logActivity($conn, "Menghapus data TL LHP (id=$id)", 'lhp', $id);

$_SESSION['success'] = "Data TL LHP berhasil dihapus.";
header("Location: $redirect");
exit;