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

    $files = [];
    $qFiles = mysqli_query($conn, "SELECT b.file FROM lhp_tl_bukti b JOIN lhp_tl t ON b.tl_id=t.id JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=$id");
    while ($f = mysqli_fetch_assoc($qFiles)) {
        $files[] = $f['file'];
    }
    foreach ($files as $f) {
        $path = dirname(__DIR__) . "/uploads/tl_lhp/" . $f;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

mysqli_query($conn, "DELETE FROM lhp WHERE id=$id");
logActivity($conn, "Menghapus data TL LHP (id=$id)", 'lhp', $id);

$_SESSION['success'] = "Data TL LHP berhasil dihapus.";
header("Location: $redirect");
exit;