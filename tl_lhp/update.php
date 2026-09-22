<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$qRow = mysqli_query($conn, "SELECT * FROM lhp WHERE id=$id");
$r = mysqli_fetch_assoc($qRow);
if (!$r) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$sumber = strtoupper(trim($_POST['sumber'] ?? $r['sumber']));
if (!in_array($sumber, ['BPK', 'BPKP', 'KAP'])) {
    $sumber = $r['sumber'];
}
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];

$nomor_lhp = mysqli_real_escape_string($conn, trim($_POST['nomor_lhp'] ?? ''));
$tahun = (int)($_POST['tahun'] ?? $r['tahun']);
$unit_id = (int)($_POST['unit_id'] ?? 0);
if ($unit_id <= 0) {
    $unit_id = 0;
}
$unit_sql = $unit_id > 0 ? $unit_id : 'NULL';
$judul_temuan = mysqli_real_escape_string($conn, trim($_POST['judul_temuan'] ?? ''));
$jml_rekomendasi = max(0, (int)($_POST['jml_rekomendasi'] ?? 0));
$uraian_rekomendasi = mysqli_real_escape_string($conn, trim($_POST['uraian_rekomendasi'] ?? ''));
$jml_tl = max(0, (int)($_POST['jml_tl'] ?? 0));
$uraian_tl = mysqli_real_escape_string($conn, trim($_POST['uraian_tl'] ?? ''));
$hasil_sesuai = max(0, (int)($_POST['hasil_sesuai'] ?? 0));
$hasil_belum_sesuai = max(0, (int)($_POST['hasil_belum_sesuai'] ?? 0));
$hasil_belum_tl = max(0, (int)($_POST['hasil_belum_tl'] ?? 0));
$hasil_tidak_tl = max(0, (int)($_POST['hasil_tidak_tl'] ?? 0));
$kesimpulan = mysqli_real_escape_string($conn, trim($_POST['kesimpulan'] ?? ''));

$nilai_raw = trim($_POST['nilai'] ?? '');
$nilai = null;
if ($nilai_raw !== '') {
    $nilai_clean = str_replace(['.', ','], '', $nilai_raw);
    if (is_numeric($nilai_clean)) {
        $nilai = (float)$nilai_clean;
    }
}

if ($nomor_lhp === '' || $judul_temuan === '') {
    $_SESSION['error'] = "Nomor LHP dan Judul Temuan wajib diisi.";
    header("Location: edit.php?id=$id");
    exit;
}

$bukti_has_update = false;
if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . "/../uploads/tl_lhp/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $maxSize = 5 * 1024 * 1024;
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($_FILES['bukti_file']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Format bukti harus PDF/JPG/PNG.";
        header("Location: edit.php?id=$id");
        exit;
    }
    if ($_FILES['bukti_file']['size'] > $maxSize) {
        $_SESSION['error'] = "Ukuran file bukti maksimal 5MB.";
        header("Location: edit.php?id=$id");
        exit;
    }
    $nama_file = 'tl_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
    if (move_uploaded_file($_FILES['bukti_file']['tmp_name'], $uploadDir . $nama_file)) {
        if ($r['bukti_file'] && file_exists($uploadDir . $r['bukti_file'])) {
            @unlink($uploadDir . $r['bukti_file']);
        }
        $bukti_has_update = true;
        $bukti_file = $nama_file;
    } else {
        $_SESSION['error'] = "Gagal mengunggah file bukti.";
        header("Location: edit.php?id=$id");
        exit;
    }
}

if ($bukti_has_update) {
    $sql = "UPDATE lhp SET nomor_lhp='$nomor_lhp', sumber='$sumber', tahun=$tahun, unit_id=$unit_sql, judul_temuan='$judul_temuan', jml_rekomendasi=$jml_rekomendasi, uraian_rekomendasi='$uraian_rekomendasi', jml_tl=$jml_tl, uraian_tl='$uraian_tl', bukti_file='$bukti_file', hasil_sesuai=$hasil_sesuai, hasil_belum_sesuai=$hasil_belum_sesuai, hasil_belum_tl=$hasil_belum_tl, hasil_tidak_tl=$hasil_tidak_tl, kesimpulan='$kesimpulan', nilai=" . ($nilai !== null ? $nilai : 'NULL') . " WHERE id=$id";
} else {
    $sql = "UPDATE lhp SET nomor_lhp='$nomor_lhp', sumber='$sumber', tahun=$tahun, unit_id=$unit_sql, judul_temuan='$judul_temuan', jml_rekomendasi=$jml_rekomendasi, uraian_rekomendasi='$uraian_rekomendasi', jml_tl=$jml_tl, uraian_tl='$uraian_tl', hasil_sesuai=$hasil_sesuai, hasil_belum_sesuai=$hasil_belum_sesuai, hasil_belum_tl=$hasil_belum_tl, hasil_tidak_tl=$hasil_tidak_tl, kesimpulan='$kesimpulan', nilai=" . ($nilai !== null ? $nilai : 'NULL') . " WHERE id=$id";
}

if (mysqli_query($conn, $sql)) {
    logActivity($conn, "Mengubah {$judulMap[$sumber]}: $nomor_lhp", 'lhp', $id);
    $_SESSION['success'] = "Data TL LHP $sumber berhasil diperbarui.";
    header("Location: index.php?sumber=$sumber");
} else {
    $_SESSION['error'] = "Gagal menyimpan: " . mysqli_error($conn);
    header("Location: edit.php?id=$id");
}
exit;