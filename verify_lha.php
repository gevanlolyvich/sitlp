<?php

require_once "config/app.php";
require_once "config/database.php";

$token = isset($_GET['token']) ? trim($_GET['token']) : '';

if(empty($token)){
    die("Token verifikasi tidak ditemukan.");
}

$token = mysqli_real_escape_string($conn, $token);

$q = mysqli_query(
    $conn,
    "
    SELECT nomor_audit,
           judul_audit,
           tanggal_mulai,
           tanggal_selesai
    FROM audit_pemeriksaan
    WHERE verification_token='$token'
    "
);

$data = mysqli_fetch_assoc($q);

if(!$data)
{
    die("Dokumen tidak valid.");
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verifikasi Dokumen LHA - <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0">Verifikasi Dokumen LHA</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <th width="200">Nomor Audit</th>
                    <td><?= htmlspecialchars($data['nomor_audit']) ?></td>
                </tr>
                <tr>
                    <th>Judul Audit</th>
                    <td><?= htmlspecialchars($data['judul_audit']) ?></td>
                </tr>
                <tr>
                    <th>Periode Audit</th>
                    <td><?= htmlspecialchars($data['tanggal_mulai']) ?> s/d <?= htmlspecialchars($data['tanggal_selesai']) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><span class="badge bg-success" style="font-size:16px;">VALID</span></td>
                </tr>
            </table>
            <p class="text-muted mt-3">Dokumen ini telah diverifikasi dan ditandatangani secara elektronik oleh Satuan Internal Audit PT Jakarta Tourisindo.</p>
        </div>
    </div>
</div>
</body>
</html>
