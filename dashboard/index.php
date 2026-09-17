<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-house me-2 text-primary"></i>Dashboard</h1>
                    <div class="jxb-page-subtitle">Sistem Informasi Satuan Internal Audit</div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="mb-3">
                                <span class="jxb-avatar jxb-avatar-lg"><i class="fas fa-user"></i></span>
                            </div>
                            <h2 class="h4 fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h2>
                            <p class="text-muted mb-0">Sistem Informasi Satuan Internal Audit</p>
                            <p class="text-muted">PT Jakarta Tourisindo / Jakarta Experience Board</p>
                            <hr class="w-25 mx-auto">
                            <p class="text-muted small mb-0">Gunakan menu di samping untuk memulai aktivitas Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include "../templates/footer.php";
?>
