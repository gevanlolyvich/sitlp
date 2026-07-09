<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$audit_id   = (int)$_POST['audit_id'];
$auditor_id = (int)$_POST['auditor_id'];
$peran      = $_POST['peran'];

mysqli_query($conn,"INSERT INTO audit_tim (audit_id,auditor_id,peran)
	VALUES('$audit_id','$auditor_id','$peran')");

header("Location:index.php?audit_id=".$audit_id);
exit;
