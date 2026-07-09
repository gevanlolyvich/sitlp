<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN']);

$id = (int)$_POST['id'];

$nama_unit = mysqli_real_escape_string($conn,$_POST['nama_unit']);

mysqli_query($conn,"UPDATE unit_kerja SET nama_unit='$nama_unit' WHERE id='$id'");

logActivity(
    $conn,
    "Mengubah Unit Kerja",
    "unit_kerja",
    $id
);

header("Location:index.php?update=success");
exit;
