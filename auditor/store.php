<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$nip      = trim($_POST['nip']);
$nama     = trim($_POST['nama']);
$jabatan  = trim($_POST['jabatan']);
$email    = trim($_POST['email']);
$telepon  = trim($_POST['telepon']);

mysqli_query($conn,"INSERT INTO auditor
	(nip,nama_auditor,jabatan,email,telepon)
	VALUES
	('$nip','$nama','$jabatan','$email','$telepon')");

header("Location:index.php");
exit;
