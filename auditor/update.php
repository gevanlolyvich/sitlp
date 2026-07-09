<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$id       = (int)$_POST['id'];
$nip      = trim($_POST['nip']);
$nama     = trim($_POST['nama']);
$jabatan  = trim($_POST['jabatan']);
$email    = trim($_POST['email']);
$telepon  = trim($_POST['telepon']);

$aktif = isset($_POST['aktif'])?1:0;

mysqli_query($conn,"UPDATE auditor SET
	nip='$nip', nama_auditor='$nama', jabatan='$jabatan', email='$email', telepon='$telepon', aktif='$aktif'
	WHERE id='$id'");

header("Location:index.php");

exit;
