<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI']);

$id = (int)$_POST['id'];

$kode_program = mysqli_real_escape_string($conn,$_POST['kode_program']);
$tahun = (int)$_POST['tahun'];
$judul_program = mysqli_real_escape_string($conn,$_POST['judul_program']);
$unit_id = (int)$_POST['unit_id'];
$penanggung_jawab_id = !empty($_POST['penanggung_jawab_id']) ? (int)$_POST['penanggung_jawab_id'] : "NULL";
$jenis_audit = mysqli_real_escape_string($conn,$_POST['jenis_audit']);
$triwulan = mysqli_real_escape_string($conn,$_POST['triwulan']);
$bulan_rencana = (int)$_POST['bulan_rencana'];
$estimasi_hari = (int)$_POST['estimasi_hari'];
$level_risiko = mysqli_real_escape_string($conn,$_POST['level_risiko']);
$prioritas = mysqli_real_escape_string($conn,$_POST['prioritas']);
$keterangan = mysqli_real_escape_string($conn,$_POST['keterangan']);

$sql = "UPDATE audit_program SET
	tahun='$tahun',
	judul_program='$judul_program',
	unit_id='$unit_id',
	penanggung_jawab_id=$penanggung_jawab_id,
	jenis_audit='$jenis_audit',
	triwulan='$triwulan',
	bulan_rencana='$bulan_rencana',
	estimasi_hari='$estimasi_hari',
	level_risiko='$level_risiko',
	prioritas='$prioritas',
	keterangan='$keterangan'
	WHERE id='$id'";

$result = mysqli_query($conn,$sql);

$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Mengubah Program Audit",
    "audit_program",
    $id
);

if(!$result){die(mysqli_error($conn));}
header("Location:index.php");
exit;
