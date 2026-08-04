<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int)$_POST['id'];

$q = mysqli_query($conn, "SELECT * FROM audit_program WHERE id=$id");
$program = mysqli_fetch_assoc($q);
if(!$program){ $_SESSION['error'] = "Program tidak ditemukan"; header("Location: index.php"); exit; }

if($program['status'] != 'SELESAI'){ $_SESSION['error'] = "Program belum selesai"; header("Location: index.php"); exit; }

if(isset($_FILES['lha_file']) && $_FILES['lha_file']['error'] == 0){
    $allowed = ['pdf'];
    $ext = strtolower(pathinfo($_FILES['lha_file']['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, $allowed)){ $_SESSION['error'] = "Hanya file PDF yang diizinkan"; header("Location: upload_lha.php?id=$id"); exit; }

    $dir = __DIR__.'/../uploads/program_lha/';
    if(!is_dir($dir)) mkdir($dir, 0775, true);

    $filename = 'LHA_'.$program['kode_program'].'_'.time().'.'.$ext;
    $filepath = $dir.$filename;

    if(move_uploaded_file($_FILES['lha_file']['tmp_name'], $filepath)){
        mysqli_query($conn, "UPDATE audit_program SET lha_file='$filename' WHERE id=$id");

        $logId = mysqli_insert_id($conn);
        logActivity($conn, "Upload LHA Program Audit", "audit_program", $id);

        $_SESSION['success'] = "LHA berhasil diupload";
    } else {
        $_SESSION['error'] = "Gagal upload file";
    }
} else {
    $_SESSION['error'] = "Tidak ada file yang diupload";
}

header("Location: index.php");
exit;
