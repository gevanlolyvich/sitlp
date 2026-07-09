<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
hasRole(['ADMIN']);

$id=(int)$_GET['id'];
$q=mysqli_query($conn,"SELECT aktif FROM auditor WHERE id=$id");
$r=mysqli_fetch_assoc($q);
$status=$r['aktif']?0:1;

mysqli_query($conn,"UPDATE auditor SET aktif=$status WHERE id=$id");

header("Location:index.php");
exit;
