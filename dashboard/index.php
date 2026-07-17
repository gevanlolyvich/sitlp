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
            <?php if($_SESSION['role'] == 'AUDITOR'): ?>
            <div class="row mb-3 mt-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Home</li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
            <?php else: ?>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <span class="d-inline-block bg-success bg-opacity-10 rounded-circle p-4">
                                    <i class="fas fa-hand-wave text-success" style="font-size: 48px;"></i>
                                </span>
                            </div>
                            <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h3>
                            <p class="text-muted mb-0">Sistem Informasi Tindak Lanjut Pengawasan</p>
                            <p class="text-muted">PT Jakarta Tourisindo</p>
                            <hr class="w-25 mx-auto">
                            <p class="text-muted small mb-0">Gunakan menu di samping untuk memulai aktivitas Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
include "../templates/footer.php";
?>
