<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = (int)$_POST['id'];
$passwordBaru = $_POST['password_baru'] ?? '';
$passwordUlang = $_POST['password_ulang'] ?? '';

if ($passwordBaru !== $passwordUlang) {
    header("Location: index.php?msg=reset_failed");
    exit;
}

if (strlen($passwordBaru) < 6) {
    header("Location: index.php?msg=reset_failed");
    exit;
}

$hash = password_hash(
    $passwordBaru,
    PASSWORD_DEFAULT
);

mysqli_query(
    $conn,
    "
    UPDATE users
    SET password='$hash'
    WHERE id='$id'
    "
);

header(
    "Location: index.php?msg=reset_success"
);

exit;
