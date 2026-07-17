<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];
$audit_id = (int)$_GET['audit_id'];

mysqli_query($conn, "DELETE FROM audit_tim WHERE id=$id");

logActivity(
    $conn,
    "Menghapus audit tim",
    "audit_tim",
    $id
);

$_SESSION['success'] = "Anggota tim berhasil dihapus.";
header("Location: index.php?audit_id=" . $audit_id);
exit;
