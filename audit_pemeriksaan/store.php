<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$program_id          = (int)$_POST['program_id'];
$nomor_audit         = mysqli_real_escape_string($conn,$_POST['nomor_audit']);
$nomor_surat_tugas   = mysqli_real_escape_string($conn,$_POST['nomor_surat_tugas']);
$tanggal_surat_tugas = $_POST['tanggal_surat_tugas'];

$judul_audit         = mysqli_real_escape_string(
    $conn,
    $_POST['judul_audit']
);

$unit_id             = (int)$_POST['unit_id'];
$ketua_auditor_id    = (int)$_POST['ketua_auditor_id'];

$tanggal_mulai       = $_POST['tanggal_mulai'];
$estimasi_hari       = (int)$_POST['estimasi_hari'];

if (!is_hari_kerja($tanggal_mulai, $conn)) {
    $_SESSION['error'] = "Tanggal mulai audit tidak boleh jatuh pada akhir pekan atau hari libur.";
    header("Location: create.php?program_id=$program_id");
    exit;
}

$tanggal_selesai     = hitung_tanggal_selesai_kerja($tanggal_mulai, $estimasi_hari, $conn);

$tahun_audit         = $_POST['tahun_audit'];

$raw_jenis           = $_POST['jenis_audit'];
$mapJenis = [
    'OPERASIONAL'=>'Operasional','Operasional'=>'Operasional',
    'KEUANGAN'=>'Keuangan','Keuangan'=>'Keuangan',
    'KEPATUHAN'=>'Kepatuhan','Kepatuhan'=>'Kepatuhan',
    'VERIFIKASI'=>'Verifikasi','INVESTIGASI'=>'Investigasi','KHUSUS'=>'Khusus'
];
$jenis_audit = isset($mapJenis[$raw_jenis]) ? $mapJenis[$raw_jenis] : $raw_jenis;
$jenis_audit = mysqli_real_escape_string($conn, $jenis_audit);

$ruang_lingkup       = mysqli_real_escape_string(
    $conn,
    $_POST['ruang_lingkup']
);

$created_by          = $_SESSION['user_id'];

$token = bin2hex(
    random_bytes(32)
);

$sql = "
INSERT INTO audit_pemeriksaan
(
    program_id,
    nomor_audit,
    nomor_surat_tugas,
    tanggal_surat_tugas,
    judul_audit,
    unit_id,
    ketua_auditor_id,
    tanggal_mulai,
    estimasi_hari,
    tanggal_selesai,
    tahun_audit,
    status,
    keterangan,
    created_by,
    jenis_audit,
    ruang_lingkup,
    verification_token
)
VALUES
(
    '$program_id',
    '$nomor_audit',
    '$nomor_surat_tugas',
    '$tanggal_surat_tugas',
    '$judul_audit',
    '$unit_id',
    '$ketua_auditor_id',
    '$tanggal_mulai',
    '$estimasi_hari',
    '$tanggal_selesai',
    '$tahun_audit',
    'DRAFT',
    '',
    '$created_by',
    '$jenis_audit',
    '$ruang_lingkup',
    '$token'
)
";

$q = mysqli_query($conn,$sql);

if(!$q)
{
    die("Gagal menyimpan data pemeriksaan audit. Silakan coba lagi.");
}

//RENCANA --> BERJALAN 
mysqli_query($conn,"
    UPDATE audit_program
    SET status='BERJALAN'
    WHERE id='$program_id'");


$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Membuat Pemeriksaan Audit",
    "audit_pemeriksaan",
    $id
);

header("Location:index.php");
exit;
