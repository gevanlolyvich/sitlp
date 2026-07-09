<?php

session_start();

require_once "../config/app.php"; 
require_once "../config/database.php"; 
require_once "../config/functions.php"; 
require_once "../auth/check.php";

$id = (int)$_POST['id'];

$nomor_surat_tugas = mysqli_real_escape_string( $conn, $_POST['nomor_surat_tugas'] );
$tanggal_surat_tugas = $_POST['tanggal_surat_tugas'];
$judul_audit = mysqli_real_escape_string( $conn, $_POST['judul_audit'] );
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$ruang_lingkup = mysqli_real_escape_string( $conn, $_POST['ruang_lingkup'] );
$keterangan = mysqli_real_escape_string( $conn, $_POST['keterangan'] );

$sql = " UPDATE audit_pemeriksaan SET nomor_surat_tugas='$nomor_surat_tugas',
	tanggal_surat_tugas='$tanggal_surat_tugas', judul_audit='$judul_audit', tanggal_mulai='$tanggal_mulai',
	tanggal_selesai='$tanggal_selesai', ruang_lingkup='$ruang_lingkup', keterangan='$keterangan' 
	WHERE id='$id' ";
if(mysqli_query($conn,$sql)) { if(function_exists('logActivity')) { logActivity( $conn, 'Mengubah data audit','audit_pemeriksaan', $id ); }
	header("Location: detail.php?id=".$id);
} else { 
	die( mysqli_error($conn) ); 
}
