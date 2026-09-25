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

$sumber = strtoupper(trim($_POST['sumber'] ?? 'BPK'));
if (!in_array($sumber, ['BPK', 'BPKP', 'KAP'])) {
    $sumber = 'BPK';
}
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];

$nomor_lhp = mysqli_real_escape_string($conn, trim($_POST['nomor_lhp'] ?? ''));
$tahun = (int)($_POST['tahun'] ?? date('Y'));
$judul_temuan = mysqli_real_escape_string($conn, trim($_POST['judul_temuan'] ?? ''));
$kesimpulan = mysqli_real_escape_string($conn, trim($_POST['kesimpulan'] ?? ''));

$unit_ids = [];
foreach (($_POST['unit_id'] ?? []) as $uid) {
    $uid = (int)$uid;
    if ($uid > 0) {
        $unit_ids[$uid] = $uid;
    }
}

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
    header("Location: create.php?sumber=$sumber");
    exit;
}

$rekomendasi = $_POST['rekomendasi'] ?? [];
$jmlRek = count((array)$rekomendasi);
if ($jmlRek < 1) {
    $_SESSION['error'] = "Minimal 1 rekomendasi wajib diisi.";
    header("Location: create.php?sumber=$sumber");
    exit;
}

$fileTree = (isset($_FILES['rekomendasi']) && is_array($_FILES['rekomendasi']))
    ? normalizeFileArray($_FILES['rekomendasi'])
    : [];

$fail = function ($msg) use ($conn, $sumber) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $msg;
    header("Location: create.php?sumber=$sumber");
    exit;
};

mysqli_begin_transaction($conn);

$created_by = (int)$_SESSION['user_id'];
$nilai_sql = ($nilai !== null) ? $nilai : 'NULL';
$sql = "INSERT INTO lhp (nomor_lhp, sumber, tahun, judul_temuan, kesimpulan, nilai, created_by)
        VALUES ('$nomor_lhp', '$sumber', $tahun, '$judul_temuan', '$kesimpulan', $nilai_sql, $created_by)";
if (!mysqli_query($conn, $sql)) {
    $fail("Gagal menyimpan data LHP: " . mysqli_error($conn));
}
$lhpId = mysqli_insert_id($conn);

foreach ($unit_ids as $uid) {
    mysqli_query($conn, "INSERT INTO lhp_unit (lhp_id, unit_id) VALUES ($lhpId, $uid)");
}

foreach (array_slice($rekomendasi, 0, 20) as $i => $rek) {
    $uraianRek = mysqli_real_escape_string($conn, trim($rek['uraian'] ?? ''));
    if ($uraianRek === '') {
        $fail("Uraian rekomendasi ke-" . ($i + 1) . " wajib diisi.");
    }
    $no = $i + 1;
    if (!mysqli_query($conn, "INSERT INTO lhp_rekomendasi (lhp_id, no, uraian) VALUES ($lhpId, $no, '$uraianRek')")) {
        $fail("Gagal menyimpan rekomendasi: " . mysqli_error($conn));
    }
    $rekId = mysqli_insert_id($conn);

    $tls = (array)($rek['tl'] ?? []);
    foreach (array_slice($tls, 0, 10) as $j => $tl) {
        $uraianTl = mysqli_real_escape_string($conn, trim($tl['uraian'] ?? ''));
        $status = mysqli_real_escape_string($conn, trim($tl['status'] ?? ''));
        if (!in_array($status, ['Proses', 'Sesuai', 'Belum Sesuai', 'Belum Ditindak Lanjut', 'Tidak Dapat Ditindak Lanjut'])) {
            $status = '';
        }
        if ($uraianTl === '' || $status === '') {
            $fail("Uraian dan status tindak lanjut ke-" . ($j + 1) . " pada rekomendasi " . ($i + 1) . " wajib diisi.");
        }
        if (!mysqli_query($conn, "INSERT INTO lhp_tl (rekomendasi_id, no, uraian, status) VALUES ($rekId, " . ($j + 1) . ", '$uraianTl', '$status')")) {
            $fail("Gagal menyimpan tindak lanjut: " . mysqli_error($conn));
        }
        $tlId = mysqli_insert_id($conn);

        $bukti = [];
        if (isset($fileTree[$i]['tl'][$j]['bukti']) && is_array($fileTree[$i]['tl'][$j]['bukti'])) {
            $bukti = array_filter($fileTree[$i]['tl'][$j]['bukti']);
        }
        $count = 0;
        foreach ($bukti as $entry) {
            if ($count >= 5) {
                break;
            }
            $nama = uploadBuktiFile($entry, 'bukti_' . $tlId);
            if ($nama === false) {
                $fail("File bukti tindak lanjut " . ($j + 1) . " tidak valid (format PDF/JPG/JPEG/PNG/XLS/XLSX, maks 5MB).");
            }
            if ($nama !== null) {
                mysqli_query($conn, "INSERT INTO lhp_tl_bukti (tl_id, file) VALUES ($tlId, '$nama')");
                $count++;
            }
        }
    }
}

mysqli_commit($conn);

logActivity($conn, "Menambah {$judulMap[$sumber]}: $nomor_lhp", 'lhp', $lhpId);
$_SESSION['success'] = "Data TL LHP $sumber berhasil disimpan.";
header("Location: index.php?sumber=$sumber");
exit;