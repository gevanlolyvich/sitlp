<?php

session_start();

require_once "config/app.php";

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'AUDITEE' ? 'dashboard_auditee/' : 'dashboard/'));
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        SISIA JAKTOUR - Beranda
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

        .landing-card {
            width: 100%;
            max-width: 760px;
            border: none;
            border-radius: 20px;
            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .landing-header {
            background: white;
            padding: 30px;
            text-align: center;
        }

        .landing-header img {
            max-height: 80px;
            margin-bottom: 15px;
        }

        .app-title {
            font-size: 28px;
            font-weight: bold;
            color: #003366;
        }

        .app-subtitle {
            font-size: 14px;
            color: #666;
        }

        .card-body {
            padding: 35px;
        }

        .choice-card {
            border: none;
            border-radius: 16px;
            transition:
                transform .2s;
            cursor: pointer;
        }

        .choice-card:hover {
            transform: scale(1.03);
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="card landing-card">
        <div class="landing-header">
            <img src="/sisia/assets/images/LogoJXB_new.png" alt="JXB Logo">
            <div class="app-title">SISIA JAKTOUR</div>
            <div class="app-subtitle">
                Sistem Informasi Satuan Internal Audit Jakarta Tourisindo
            </div>
            <small class="text-muted">PT Jakarta Tourisindo</small>
        </div>
        <div class="card-body">
            <p class="text-center text-muted fw-semibold mb-4">
                Pilih menu untuk masuk ke sistem
            </p>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <a href="auth/login.php" class="text-decoration-none">
                        <div class="card choice-card bg-primary text-white text-center py-5 shadow">
                            <div class="card-body">
                                <i class="fas fa-clipboard-list mb-3" style="font-size:48px;"></i>
                                <h2 class="mb-1"><strong>SIA</strong></h2>
                                <small>Satuan Internal Audit</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <a href="mr/" class="text-decoration-none">
                        <div class="card choice-card bg-secondary text-white text-center py-5 shadow">
                            <div class="card-body">
                                <i class="fas fa-building mb-3" style="font-size:48px;"></i>
                                <h2 class="mb-1"><strong>MR</strong></h2>
                                <small>Management Resiko</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="footer-text">
                © <?= date('Y') ?>
                PT Jakarta Tourisindo &mdash; by MBG
                <br>
                Satuan Internal Audit
            </div>
        </div>
    </div>
</body>

</html>