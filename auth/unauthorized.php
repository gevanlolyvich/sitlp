<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISIA JAKTOUR - Akses Ditolak</title>
    <link rel="icon" href="/sisia/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/sisia/assets/css/jxb-design-system.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--canvas);
            font-family: "Segoe UI", Arial, sans-serif;
        }

        .denied-card {
            max-width: 460px;
            width: 100%;
            margin: 24px;
            text-align: center;
        }

        .denied-code {
            font-size: 64px;
            font-weight: 700;
            color: var(--jxb-blue-700);
            line-height: 1;
        }

        .denied-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--jxb-yellow-100);
            color: var(--jxb-yellow-600);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
    </style>
</head>

<body>
    <div class="card denied-card">
        <div class="card-body p-4 p-sm-5">
            <span class="denied-icon mb-3"><i class="fas fa-lock"></i></span>
            <div class="denied-code">403</div>
            <h1 class="h5 fw-bold mt-2 mb-1">Akses Ditolak</h1>
            <p class="text-muted mb-4">
                Anda tidak memiliki akses ke halaman ini. Hubungi administrator bila
                Anda merasa ini sebuah kekeliruan.
            </p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="/sisia/dashboard/" class="btn btn-primary"><i class="fas fa-house"></i> Ke Dashboard</a>
                <a href="/sisia/auth/login.php" class="btn btn-outline-secondary">Login ulang</a>
            </div>
        </div>
    </div>
</body>

</html>