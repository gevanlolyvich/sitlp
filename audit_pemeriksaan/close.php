<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];

/* check apakah sudah ada temuan */
$qTemuan = mysqli_query($conn, "SELECT COUNT(*) jml FROM audit_temuan WHERE audit_id='$id'");
$temuan = mysqli_fetch_assoc($qTemuan);
if($temuan['jml']==0){
    die("Audit belum memiliki temuan.");
}

/* check apakah semua tindak lanjut sudah Sesuai */
$qTL = mysqli_query($conn, "
    SELECT COUNT(*) jml
    FROM audit_tindak_lanjut tl
    JOIN audit_rekomendasi r ON tl.rekomendasi_id = r.id
    JOIN audit_temuan t ON r.temuan_id = t.id
    WHERE t.audit_id = '$id' AND tl.status != 'Sesuai'
");
$tlBelum = mysqli_fetch_assoc($qTL);
if($tlBelum['jml'] > 0){
    $_SESSION['error'] = "Tidak dapat menutup audit. Masih ada " . $tlBelum['jml'] . " tindak lanjut yang statusnya belum Sesuai.";
    header("Location: detail.php?id=" . $id);
    exit;
}

$qAudit = mysqli_query(
    $conn,
    "
    SELECT
        id,
        nomor_audit,
        program_id
    FROM audit_pemeriksaan
    WHERE id='$id'
    "
);

$audit =
mysqli_fetch_assoc(
    $qAudit
);

if(!$audit)
{
    die(
      "Audit tidak ditemukan"
    );
}

/*
|--------------------------------------------------------------------------
| UPDATE AUDIT
|--------------------------------------------------------------------------
*/

mysqli_query(
    $conn,
    "
    UPDATE audit_pemeriksaan
    SET status='SELESAI'
    WHERE id='$id'
    "
);

/*
|--------------------------------------------------------------------------
| UPDATE PROGRAM AUDIT
|--------------------------------------------------------------------------
*/

if(
    !empty(
        $audit['program_id']
    )
)
{
    mysqli_query(
        $conn,
        "
        UPDATE audit_program
        SET status='SELESAI'
        WHERE id='".$audit['program_id']."'
        "
    );
}

/*
|--------------------------------------------------------------------------
| ACTIVITY LOG
|--------------------------------------------------------------------------
*/

if(
    function_exists(
        'logActivity'
    )
)
{
    logActivity(
        $conn,
        'Menutup Audit '.$audit['nomor_audit'],
        'audit_pemeriksaan',
        $id
    );
}

header(
    "Location: detail.php?id=".$id
);

exit;
