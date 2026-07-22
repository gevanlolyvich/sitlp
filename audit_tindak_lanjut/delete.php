<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_GET['id'];

$rekomendasi_id = (int)$_GET['rekomendasi_id'];

// Check if allowed to delete (only if status = Proses)
$qCheck = mysqli_query($conn,"SELECT status, bukti_file FROM audit_tindak_lanjut WHERE id=$id");
$d = mysqli_fetch_assoc($qCheck);
if (!$d) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}
if ($d['status'] != 'Proses') {
    $_SESSION['error'] = "Hapus hanya diizinkan saat status masih Proses.";
    header("Location: index.php?rekomendasi_id=" . $rekomendasi_id);
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
header("Location:index.php?rekomendasi_id=".$rekomendasi_id);

exit;
