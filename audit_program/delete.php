<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN','KEPALA_SIA']);

$id=(int)$_GET['id'];

mysqli_query($conn,"DELETE FROM audit_program WHERE id=$id");

$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Menghapus Audit Program",
    "audit_tindak_lanjut",
    $id
);

header("Location:index.php");
exit;
