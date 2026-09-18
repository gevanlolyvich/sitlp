<?php

session_start();

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        SISIA JAKTOUR - MR
    </title>
    <link rel="icon" href="/sisia/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background:
                linear-gradient(135deg, #003366, #005baa);
            font-family: Arial, sans-serif;
        }

        .mr-card {
            width: 100%;
            max-width: 520px;
            border: none;
            border-radius: 20px;
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .mr-card .card-body {
            padding: 45px 35px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="card mr-card">
        <div class="card-body">
            <img src="/sisia/assets/images/LogoJXB_new.png" alt="JXB Logo" style="max-height:70px;margin-bottom:15px;">
            <div>
                <i class="fas fa-tools text-muted mb-3" style="font-size:64px;"></i>
            </div>
            <h4 class="text-muted">Managemen Risiko (MR)</h4>
            <p class="text-muted">Fitur ini akan tersedia pada rilis berikutnya.</p>
            <a href="../" class="btn btn-primary mt-2">
                <i class="fas fa-arrow-left"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
</body>

</html>