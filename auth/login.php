<?php
session_start();
require_once "../config/app.php";

if(isset($_SESSION['user_id']))
{
    header("Location: ../dashboard/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>
        SITLP - Login
    </title>
    <link
        rel="icon"
        href="/sitlp/assets/images/favicon.ico"
    >
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background:
                linear-gradient(135deg,#003366,#005baa);
            font-family: Arial, sans-serif;
        }
        .login-card {
            width: 420px;
            border: none;
            border-radius: 20px;
            box-shadow:
                0 15px 40px rgba(0,0,0,0.25);
            overflow: hidden;
        }
        .login-header {
            background: white;
            padding: 30px;
            text-align: center;
        }
        .login-header img {
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
        .input-group-text {
            background: #f8f9fa;
        }
        .btn-login {
            background: #005baa;
            border: none;
            font-weight: 600;
            padding: 12px;
        }
        .btn-login:hover {
            background: #003f7d;
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
<div class="card login-card">
    <div class="login-header">
        <img
            src="/sitlp/assets/images/LogoJXB_new.png"
            alt="JXB Logo"
        >
        <div class="app-title">SITLP</div>
        <div class="app-subtitle">
            Sistem Informasi Tindak Lanjut Pengawasan
        </div>
        <small class="text-muted">PT Jakarta Tourisindo</small>
    </div>
    <div class="card-body">
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                Username atau password salah.
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['expired'])): ?>
            <div class="alert alert-warning">
                Session telah berakhir.
                Silakan login kembali.
            </div>
        <?php endif; ?>
        <form action="login_process.php" method="post">
            <div class="input-group mb-3">
                <span class="input-group-text">
                    <i class="fa fa-user"></i>
                </span>
                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Username"
                    required
                    autofocus
                >
            </div>
            <div class="input-group mb-4">
                <span class="input-group-text">
                    <i class="fa fa-lock"></i>
                </span>
                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Password"
                    required
                >
            </div>
            <button type="submit" class="btn btn-primary btn-login w-100">
                <i class="fa fa-sign-in-alt"></i>
                Masuk ke Sistem
            </button>
        </form>
        <div class="footer-text">
            © <?= date('Y') ?>
            PT Jakarta Tourisindo
            <br>
            Satuan Internal Audit
        </div>
    </div>
</div>
</body>
</html>
