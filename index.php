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
    <link rel="stylesheet" href="/sisia/assets/css/jxb-design-system.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--canvas);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .landing-card {
            width: 100%;
            max-width: 720px;
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            margin: 24px;
        }

        .landing-card::before {
            content: "";
            display: block;
            height: 4px;
            background: linear-gradient(90deg,
                    var(--jxb-logo-blue) 0 55%,
                    var(--jxb-logo-red) 55% 80%,
                    var(--jxb-logo-yellow) 80% 100%);
        }

        .landing-header {
            background: var(--surface);
            padding: 32px 32px 8px;
            text-align: center;
        }

        .landing-header img {
            max-height: 72px;
            margin-bottom: 16px;
        }

        .app-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--jxb-blue-700);
        }

        .app-subtitle {
            font-size: 13px;
            color: var(--neutral-500);
            margin-top: 4px;
        }
    </style>
</head>

<body>
    <div class="card landing-card">
        <div class="landing-header">
            <img src="/sisia/assets/images/LogoJXB_new.png" alt="JXB Logo">
            <div class="app-title"><span style="color: red;">SI</span>SIA JAKTOUR</div>
            <div class="app-subtitle">
                Sistem Informasi Satuan Internal Audit
            </div>
        </div>
        <div class="card-body px-4 pb-4 pt-2">
            <p class="text-center text-muted fw-semibold mb-4">
                Pilih menu untuk masuk ke sistem
            </p>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <a href="auth/login.php" class="jxb-choice-card h-100">
                        <div class="d-flex align-items-center gap-3">
                            <span class="jxb-choice-icon"><i class="fas fa-clipboard-list"></i></span>
                            <div>
                                <div class="jxb-choice-title">SIA</div>
                                <div class="jxb-choice-desc">Satuan Internal Audit</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto jxb-choice-arrow"></i>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <a href="mr/" class="jxb-choice-card h-100">
                        <div class="d-flex align-items-center gap-3">
                            <span class="jxb-choice-icon"><i class="fas fa-building"></i></span>
                            <div>
                                <div class="jxb-choice-title">MR</div>
                                <div class="jxb-choice-desc">Managemen Risiko</div>
                            </div>
                            <i class="fas fa-chevron-right ms-auto jxb-choice-arrow"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="text-center mt-4" style="color: blue; font-weight: 500;">
                © <?= date('Y') ?> PT Jakarta Tourisindo / Jakarta Experience Board
            </div>
        </div>
    </div>
</body>

</html>