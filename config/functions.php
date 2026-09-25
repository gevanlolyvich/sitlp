<?php

function base_url($path="")
{
    return BASE_URL.'/'.$path;
}

function sanitize($conn,$value)
{
    return mysqli_real_escape_string($conn,trim($value));
}

function createLog($conn,$user_id,$aktivitas,$tabel='',$record_id=0){
    $user_id = (int)$user_id;
    $aktivitas = mysqli_real_escape_string($conn,$aktivitas);
    $tabel = mysqli_real_escape_string($conn,$tabel);
    $record_id = (int)$record_id;
    mysqli_query($conn,"INSERT INTO activity_log(user_id,aktivitas,nama_tabel,record_id)
		VALUES($user_id,'$aktivitas','$tabel',$record_id)");
}

function hasRole($roles)
{
    if(!in_array($_SESSION['role'],$roles)){
	header("Location: ../auth/unauthorized.php");
	exit;
    }
}

function normalizeFileArray(array $p)
{
    if (isset($p['name']) && is_array($p['name'])) {
        $out = [];
        foreach ($p['name'] as $k => $v) {
            $sub = [];
            foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $prop) {
                $sub[$prop] = $p[$prop][$k] ?? null;
            }
            $out[$k] = normalizeFileArray($sub);
        }
        return $out;
    }
    if ((int)($p['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    return $p;
}

function uploadBuktiFile($entry, $prefix)
{
    if (!$entry || (int)($entry['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $maxSize = 5 * 1024 * 1024;
    $allow = ['pdf', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
    $size = (int)($entry['size'] ?? 0);
    if ($size < 1 || $size > $maxSize) {
        return false;
    }
    $ext = strtolower(pathinfo($entry['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allow)) {
        return false;
    }
    $dir = dirname(__DIR__) . '/uploads/tl_lhp/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $nama = $prefix . '_' . date('YmdHis') . '_' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($entry['tmp_name'], $dir . $nama)) {
        return false;
    }
    return $nama;
}

function menuActive($folder)
{
    return strpos(
        $_SERVER['REQUEST_URI'],
        '/' . $folder . '/'
    ) !== false
    ? 'active'
    : '';
}

function logActivity($conn,$aktivitas,$tabel_referensi,$referensi_id = null){
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    $user_id = (int)$_SESSION['user_id'];
    $aktivitas = mysqli_real_escape_string($conn,$aktivitas);
    $tabel_referensi = mysqli_real_escape_string($conn,$tabel_referensi);
    $referensi_id =
	$referensi_id
	? (int)$referensi_id
	: "NULL";

    mysqli_query($conn,"INSERT INTO audit_log(user_id,aktivitas,tabel_referensi,referensi_id)
	VALUES('$user_id','$aktivitas','$tabel_referensi',$referensi_id)");
}

function hitung_tanggal_selesai_kerja($mulai, $hari_estimasi, $conn)
{
    $date = new DateTime($mulai);
    $libur = [];
    $q = mysqli_query($conn, "SELECT tanggal FROM hari_libur");
    while ($r = mysqli_fetch_assoc($q)) {
        $libur[] = $r['tanggal'];
    }
    $counted = 0;
    while ($counted < $hari_estimasi) {
        $isKerja = ($date->format('N') < 6) && !in_array($date->format('Y-m-d'), $libur);
        if ($isKerja) {
            $counted++;
            if ($counted >= $hari_estimasi) break;
        }
        $date->modify('+1 day');
    }
    return $date->format('Y-m-d');
}

function is_hari_kerja($tanggal, $conn)
{
    $date = new DateTime($tanggal);
    if ($date->format('N') >= 6) {
        return false;
    }
    $tanggal = mysqli_real_escape_string($conn, $tanggal);
    $q = mysqli_query($conn, "SELECT COUNT(*) total FROM hari_libur WHERE tanggal='$tanggal'");
    $r = mysqli_fetch_assoc($q);
    return (int)$r['total'] == 0;
}

function isAuditLocked($conn, $audit_id)
{
    $audit_id = (int)$audit_id;
    $q = mysqli_query($conn, "SELECT status FROM audit_pemeriksaan WHERE id=$audit_id");
    $r = mysqli_fetch_assoc($q);
    return ($r && $r['status'] == 'Selesai');
}

function blockLockedAudit($conn, $audit_id)
{
    if (isAuditLocked($conn, $audit_id)) {
        $_SESSION['error'] = "Audit sudah ditutup. Data tidak dapat ditambah, diubah, atau dihapus.";
        header("Location: index.php?audit_id=$audit_id");
        exit;
    }
}
