
<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI']);

$kode_program = mysqli_real_escape_string(
    $conn,
    $_POST['kode_program']
);

$tahun = (int)$_POST['tahun'];

$unit_id = (int)$_POST['unit_id'];

$penanggung_jawab_id =
!empty($_POST['penanggung_jawab_id'])
? (int)$_POST['penanggung_jawab_id']
: "NULL";

$jenis_audit = mysqli_real_escape_string(
    $conn,
    $_POST['jenis_audit']
);

$triwulan = mysqli_real_escape_string(
    $conn,
    $_POST['triwulan']
);

$bulan_rencana = (int)$_POST['bulan_rencana'];

$estimasi_hari = (int)$_POST['estimasi_hari'];

$level_risiko = mysqli_real_escape_string(
    $conn,
    $_POST['level_risiko']
);

$prioritas = mysqli_real_escape_string(
    $conn,
    $_POST['prioritas']
);

$judul_program = mysqli_real_escape_string(
    $conn,
    $_POST['judul_program']
);

$keterangan = mysqli_real_escape_string(
    $conn,
    $_POST['keterangan']
);

$created_by = $_SESSION['user_id'];

$sql = "
INSERT INTO audit_program
(
kode_program,
tahun,
judul_program,
unit_id,
penanggung_jawab_id,
jenis_audit,
triwulan,
bulan_rencana,
estimasi_hari,
level_risiko,
prioritas,
keterangan,
created_by
)
VALUES
(
'$kode_program',
'$tahun',
'$judul_program',
'$unit_id',
$penanggung_jawab_id,
'$jenis_audit',
'$triwulan',
'$bulan_rencana',
'$estimasi_hari',
'$level_risiko',
'$prioritas',
'$keterangan',
'$created_by'
)
";

mysqli_query($conn,$sql);

$id = mysqli_insert_id($conn);
logActivity(
    $conn,
    "Membuat Program Audit",
    "audit_program",
    $id
);

header("Location:index.php");
exit;
