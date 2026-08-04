<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN','KEPALA_SIA']);

$nip      = mysqli_real_escape_string($conn, trim($_POST['nip']));
$nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
$jabatan  = mysqli_real_escape_string($conn, trim($_POST['jabatan']));
$email    = mysqli_real_escape_string($conn, trim($_POST['email']));
$telepon  = mysqli_real_escape_string($conn, trim($_POST['telepon']));

mysqli_query($conn,"INSERT INTO auditor
	(nip,nama_auditor,jabatan,email,telepon)
	VALUES
	('$nip','$nama','$jabatan','$email','$telepon')");

header("Location:index.php");
exit;
