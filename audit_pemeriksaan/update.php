<?php

session_start();

require_once "../config/app.php"; 
require_once "../config/database.php"; 
require_once "../config/functions.php"; 
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$id = (int)$_POST['id'];

$nomor_surat_tugas = mysqli_real_escape_string( $conn, $_POST['nomor_surat_tugas'] );
$tanggal_surat_tugas = $_POST['tanggal_surat_tugas'];
$judul_audit = mysqli_real_escape_string( $conn, $_POST['judul_audit'] );
$jenis_audit = mysqli_real_escape_string( $conn, $_POST['jenis_audit'] );
$tanggal_mulai = $_POST['tanggal_mulai'];
$estimasi_hari = (int)$_POST['estimasi_hari'];

if (!is_hari_kerja($tanggal_mulai, $conn)) {
    $_SESSION['error'] = "Tanggal mulai audit tidak boleh jatuh pada akhir pekan atau hari libur.";
    header("Location: edit.php?id=$id");
    exit;
}

$tanggal_selesai = hitung_tanggal_selesai_kerja($tanggal_mulai, $estimasi_hari, $conn);
$ruang_lingkup = mysqli_real_escape_string( $conn, $_POST['ruang_lingkup'] );
$keterangan = mysqli_real_escape_string( $conn, $_POST['keterangan'] );

$sql = " UPDATE audit_pemeriksaan SET nomor_surat_tugas='$nomor_surat_tugas',
	tanggal_surat_tugas='$tanggal_surat_tugas', judul_audit='$judul_audit', jenis_audit='$jenis_audit',
	tanggal_mulai='$tanggal_mulai', estimasi_hari='$estimasi_hari', tanggal_selesai='$tanggal_selesai',
	ruang_lingkup='$ruang_lingkup', keterangan='$keterangan' 
	WHERE id='$id' ";
if(mysqli_query($conn,$sql)) { if(function_exists('logActivity')) { logActivity( $conn, 'Mengubah data audit','audit_pemeriksaan', $id ); }
	header("Location: index.php");
} else { 
	die( mysqli_error($conn) ); 
}
