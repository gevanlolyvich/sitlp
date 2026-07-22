<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";

$username = mysqli_real_escape_string($conn, trim($_POST['username']));
$password = $_POST['password'];

$sql = mysqli_query($conn, " SELECT * FROM users WHERE username='$username' AND aktif=1 LIMIT 1 ");

if (mysqli_num_rows($sql) == 0) {
    header("Location: login.php?error=username");
    exit;
}

$user = mysqli_fetch_assoc($sql);
if (!password_verify($password, $user['password'])) {
    header("Location: login.php?error=password");
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['role'] = $user['role'];
$_SESSION['unit_id'] = $user['unit_id'];
$_SESSION['last_activity'] = time();

mysqli_query($conn, " UPDATE users SET last_login=NOW() WHERE id='" . $user['id'] . "' ");

$role = $user['role'];
if ($role == 'AUDITEE') {
    header("Location: ../dashboard_auditee/");
} else {
    header("Location: ../dashboard/");
}
exit; ?>