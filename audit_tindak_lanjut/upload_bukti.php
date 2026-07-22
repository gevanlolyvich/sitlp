<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'AUDITOR', 'AUDITEE']);

$id = (int) $_GET['id'];

header("Location: detail.php?id=" . $id);
exit;
