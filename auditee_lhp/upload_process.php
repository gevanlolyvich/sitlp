<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id < 1) {
    $_SESSION['error'] = "Data tindak lanjut tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$q = mysqli_query($conn, "SELECT t.id AS tl_id, t.status, l.id AS lhp_id
    FROM lhp_tl t
    JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id
    JOIN lhp l ON r.lhp_id=l.id
    WHERE t.id=$id
      AND EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$unit_id)");
$tl = mysqli_fetch_assoc($q);

if (!$tl) {
    $_SESSION['error'] = "Tindak lanjut tidak ditemukan atau bukan unit Anda.";
    header("Location: index.php");
    exit;
}

if (in_array($tl['status'], ['Sesuai', 'Tidak Dapat Ditindak Lanjut'])) {
    $_SESSION['error'] = "Status sudah final (" . $tl['status'] . "). Tidak dapat upload bukti.";
    header("Location: detail.php?id=$id");
    exit;
}

$files = normalizeFileArray($_FILES['bukti']);
$files = is_array($files) ? array_slice(array_values(array_filter($files)), 0, 5) : [];

$uploaded = 0;
$failed = [];

foreach ($files as $entry) {
    $nama = uploadBuktiFile($entry, 'bukti_' . $id);
    if ($nama === false) {
        $failed[] = $entry['name'] ?? 'file';
        continue;
    }
    if ($nama !== null) {
        mysqli_query($conn, "INSERT INTO lhp_tl_bukti (tl_id, file) VALUES ($id, '" . mysqli_real_escape_string($conn, $nama) . "')");
        $uploaded++;
    }
}

if ($uploaded > 0) {
    logActivity($conn, "Upload bukti TL LHP", 'lhp', (int)$tl['lhp_id']);
    $_SESSION['success'] = "$uploaded file bukti berhasil diupload.";
    $_SESSION['upload_success'] = "Upload bukti berhasil: $uploaded file.";
} else {
    $_SESSION['error'] = "Tidak ada file yang berhasil diupload. Pastikan format PDF/JPG/PNG dan ukuran maksimal 5MB.";
}

if ($failed) {
    $_SESSION['error'] = (isset($_SESSION['error']) ? trim($_SESSION['error'], '.') . '. ' : '') . "File gagal: " . implode(', ', $failed) . ".";
}

header("Location: detail.php?id=$id");
exit;