<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
$id = (int)$_GET['id'];
$audit_id = (int)$_GET['audit_id'];

$q = mysqli_query($conn,"SELECT file_path FROM audit_lampiran WHERE id=$id");
$file = mysqli_fetch_assoc($q);
if($file){@unlink('../'.$file['file_path']);
}

mysqli_query($conn,"DELETE FROM audit_lampiran WHERE id=$id");

$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Menghapus Audit Lampiran",
    "audit_tindak_lanjut",
    $id
);

header("Location:index.php?audit_id=".$audit_id);

exit;
