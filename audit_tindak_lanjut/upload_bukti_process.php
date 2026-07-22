<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','AUDITOR','AUDITEE']);

$id = (int)$_POST['id'];
$rekomendasi_id = (int)$_POST['rekomendasi_id'];
$uraian_tindak_lanjut = mysqli_real_escape_string($conn, $_POST['hasil_tindak_lanjut']);

// Check if locked (Sesuai)
$qCheck = mysqli_query($conn, "SELECT status FROM audit_tindak_lanjut WHERE id=$id");
$current = mysqli_fetch_assoc($qCheck);
if ($current['status'] == 'Sesuai') {
    $_SESSION['error'] = "Status sudah Sesuai (final). Tidak dapat upload bukti.";
    header("Location: ../auditee_temuan/index.php");
    exit;
}

$allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
$ext = strtolower(pathinfo($_FILES['bukti']['name'],PATHINFO_EXTENSION));

if(!in_array($ext,$allowed))
{
    die("Format file tidak diperbolehkan");
}

$nama_file = date('YmdHis') . '_' . uniqid() . '.' . $ext;

if(
    move_uploaded_file(
        $_FILES['bukti']['tmp_name'],
        '../uploads/tindak_lanjut/' . $nama_file
    )
)
{
    $original_name = mysqli_real_escape_string($conn, $_FILES['bukti']['name']);
    $user_id = $_SESSION['user_id'];

    // Insert log entry for upload (DO NOT change status)
    mysqli_query($conn,"INSERT INTO audit_tindak_lanjut_log (tindak_lanjut_id, aksi, file_bukti, file_bukti_original, hasil_tindak_lanjut, keterangan, dibuat_oleh, dibuat_pada) VALUES ('$id', 'upload_bukti', '$nama_file', '$original_name', '$uraian_tindak_lanjut', 'Upload bukti tindak lanjut', '$user_id', NOW())");

    // Update bukti_file on main table for quick access (latest file only)
    mysqli_query($conn,"UPDATE audit_tindak_lanjut SET bukti_file='$nama_file', hasil_tindak_lanjut='$uraian_tindak_lanjut' WHERE id=$id");

    logActivity(
        $conn,
        "Upload bukti Tindak Lanjut",
        "audit_tindak_lanjut",
        $id
    );
}
else
{
    $_SESSION['error'] = "Upload file gagal.";
    header("Location: detail.php?id=" . $id);
    exit;
}

$_SESSION['success'] = "Bukti tindak lanjut berhasil diupload.";
header("Location: detail.php?id=" . $id);
exit;
