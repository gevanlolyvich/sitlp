<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

$id = (int)$_GET['id'];

$passwordBaru = '123456';

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
