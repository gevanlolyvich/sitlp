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
    header("Location: edit.php?id=$id");
    exit;
}

$rekomendasi = $_POST['rekomendasi'] ?? [];
$jmlRek = count((array)$rekomendasi);
if ($jmlRek < 1) {
    $_SESSION['error'] = "Minimal 1 rekomendasi wajib diisi.";
    header("Location: edit.php?id=$id");
    exit;
}

$fileTree = (isset($_FILES['rekomendasi']) && is_array($_FILES['rekomendasi']))
    ? normalizeFileArray($_FILES['rekomendasi'])
    : [];

$oldFiles = [];
$qFiles = mysqli_query($conn, "SELECT b.file FROM lhp_tl_bukti b JOIN lhp_tl t ON b.tl_id=t.id JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=$id");
while ($f = mysqli_fetch_assoc($qFiles)) {
    $oldFiles[] = $f['file'];
}

$fail = function ($msg) use ($conn, $id) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $msg;
    header("Location: edit.php?id=$id");
    exit;
};

mysqli_begin_transaction($conn);

$nilai_sql = ($nilai !== null) ? $nilai : 'NULL';
$sql = "UPDATE lhp SET nomor_lhp='$nomor_lhp', sumber='$sumber', tahun=$tahun, judul_temuan='$judul_temuan', kesimpulan='$kesimpulan', nilai=$nilai_sql WHERE id=$id";
if (!mysqli_query($conn, $sql)) {
    $fail("Gagal menyimpan data LHP: " . mysqli_error($conn));
}

mysqli_query($conn, "DELETE FROM lhp_unit WHERE lhp_id=$id");
if (!mysqli_query($conn, "DELETE FROM lhp_rekomendasi WHERE lhp_id=$id")) {
    $fail("Gagal menghapus rekomendasi lama: " . mysqli_error($conn));
}

foreach ($unit_ids as $uid) {
    mysqli_query($conn, "INSERT INTO lhp_unit (lhp_id, unit_id) VALUES ($id, $uid)");
}

$hapusBukti = $_POST['rekomendasi'] ?? [];
foreach (array_slice($rekomendasi, 0, 20) as $i => $rek) {
    $uraianRek = mysqli_real_escape_string($conn, trim($rek['uraian'] ?? ''));
    if ($uraianRek === '') {
        $fail("Uraian rekomendasi ke-" . ($i + 1) . " wajib diisi.");
    }
    $no = $i + 1;
    if (!mysqli_query($conn, "INSERT INTO lhp_rekomendasi (lhp_id, no, uraian) VALUES ($id, $no, '$uraianRek')")) {
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

        // Pertahankan bukti lama (kecuali yang ditandai hapus)
        $existing = isset($hapusBukti[$i]['tl'][$j]['existing_bukti']) ? (array)$hapusBukti[$i]['tl'][$j]['existing_bukti'] : [];
        $hapusList = isset($hapusBukti[$i]['tl'][$j]['hapus_bukti']) ? (array)$hapusBukti[$i]['tl'][$j]['hapus_bukti'] : [];
        foreach ($existing as $fRaw) {
            $f = urldecode($fRaw);
            if (in_array($f, $hapusList, true)) {
                continue;
            }
            mysqli_query($conn, "INSERT INTO lhp_tl_bukti (tl_id, file) VALUES ($tlId, '" . mysqli_real_escape_string($conn, $f) . "')");
        }

        // Upload bukti baru
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

// Bersihkan file bukti lama yang tidak dipakai lagi
$reuse = [];
$qReuse = mysqli_query($conn, "SELECT b.file FROM lhp_tl_bukti b JOIN lhp_tl t ON b.tl_id=t.id JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=$id");
while ($f = mysqli_fetch_assoc($qReuse)) {
    $reuse[] = $f['file'];
}
foreach ($oldFiles as $f) {
    if (!in_array($f, $reuse, true)) {
        $path = dirname(__DIR__) . '/uploads/tl_lhp/' . $f;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}

logActivity($conn, "Mengubah {$judulMap[$sumber]}: $nomor_lhp", 'lhp', $id);
$_SESSION['success'] = "Data TL LHP $sumber berhasil diperbarui.";
header("Location: index.php?sumber=$sumber");
exit;