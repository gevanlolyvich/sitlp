<?php
// $host = "172.16.0.18";
// $user = "sitlp";
// $pass = "DeptIT2022;";
// $db = "sitlp";

$host = "localhost";
$user = "root";
$pass = "";
$db = "sitlp";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal");
}
