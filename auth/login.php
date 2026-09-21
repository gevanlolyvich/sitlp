<?php
session_start();
require_once "../config/app.php";

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        SISIA JAKTOUR - Login
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

        .login-card {
            width: 420px;
            border: 1px solid var(--neutral-300);
            border-radius: var(--radius-card);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            margin: 24px;
        }

        .login-card::before {
            content: "";
            display: block;
            height: 4px;
            background: linear-gradient(90deg,
                    var(--jxb-logo-blue) 0 55%,
                    var(--jxb-logo-red) 55% 80%,
                    var(--jxb-logo-yellow) 80% 100%);
        }

        .login-header {
            background: var(--surface);
            padding: 32px 32px 20px;
            text-align: center;
        }

        .login-header img {
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

        .login-body {
            padding: 8px 32px 20px;
        }

        .btn-login {
            font-weight: 600;
            padding: 11px 16px;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            font-weight: 500;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="card login-card">
        <div class="login-header">
            <img src="/sisia/assets/images/LogoJXB_new.png" alt="JXB Logo">
            <div class="app-title"><span style="color: red;">SI</span>SIA JAKTOUR</div>
            <div class="app-subtitle">
                Sistem Informasi Satuan Internal Audit
            </div>
        </div>
        <div class="login-body">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                    <i class="fas fa-circle-exclamation mt-1"></i>
                    <div>
                        <strong>Gagal masuk.</strong><br>
                        <?php if (($_GET['error'] ?? '') === 'username'): ?>
                            Username tidak ditemukan atau akun tidak aktif.
                        <?php elseif (($_GET['error'] ?? '') === 'password'): ?>
                            Password yang Anda masukkan salah.
                        <?php else: ?>
                            Username atau password salah.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['expired'])): ?>
                <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                    <i class="fas fa-clock mt-1"></i>
                    <div>
                        <strong>Session telah berakhir.</strong><br>
                        Silakan login kembali.
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['nonaktif'])): ?>
                <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                    <i class="fas fa-user-slash mt-1"></i>
                    <div>
                        <strong>Akun tidak aktif.</strong><br>
                        Silakan hubungi administrator.
                    </div>
                </div>
            <?php endif; ?>
            <form action="login_process.php" method="post">
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control"
                        placeholder="Masukkan username" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt"></i>
                    Masuk ke Sistem
                </button>
            </form>
            <div class="footer-text">
                © <?= date('Y') ?>
                PT Jakarta Tourisindo / Jakarta Experience Board
            </div>
        </div>
    </div>
</body>

</html>