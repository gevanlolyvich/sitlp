<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA']);

header('Content-Type: application/json');

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
if ($tahun < 2000 || $tahun > 2100) {
    http_response_code(400);
    exit(json_encode(['error' => 'Tahun tidak valid.']));
}

$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM audit_program WHERE tahun=$tahun");
$urut = (int)mysqli_fetch_assoc($q)['total'] + 1;

echo json_encode([
    'kode' => 'PKPT-' . $tahun . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT),
]);
exit;