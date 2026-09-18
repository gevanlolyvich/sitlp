<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_SESSION['last_activity'])){
    if(time() - $_SESSION['last_activity'] > SESSION_TIMEOUT){
      session_destroy();
      header("Location: ../auth/login.php?expired=1");
      exit;
    }
}

$_SESSION['last_activity']=time();

require_once dirname(__DIR__) . "/config/database.php";

$current_user_id = (int) $_SESSION['user_id'];
$sql = mysqli_query($conn, "SELECT id, nama, email, unit_id, role, foto, aktif FROM users WHERE id = $current_user_id LIMIT 1");

if (!$sql || mysqli_num_rows($sql) == 0) {
    session_destroy();
    header("Location: ../auth/login.php?expired=1");
    exit;
}

$current_user = mysqli_fetch_assoc($sql);
if ((int) $current_user['aktif'] !== 1) {
    session_destroy();
    header("Location: ../auth/login.php?nonaktif=1");
    exit;
}

$_SESSION['nama'] = $current_user['nama'];
$_SESSION['role'] = $current_user['role'];
$_SESSION['unit_id'] = $current_user['unit_id'];
$_SESSION['foto'] = $current_user['foto'];
$_SESSION['email'] = $current_user['email'];
