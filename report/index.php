<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole([
    'ADMIN',
    'KEPALA_SIA',
    'DIREKSI',
    'KOMISARIS'
]);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h1 class="h4 mb-0">Generate Report Excel</h1>
                <ol class="breadcrumb mb-0 d-none d-md-flex">
                    <li class="breadcrumb-item"><a href="../dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Export Excel</li>
                </ol>
            </div>
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Export Matrik Pemantauan Tindak Lanjut</h3>
                </div>
                <form action="export_excel.php" method="GET">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Awal</label>
                                    <input type="date" name="tanggal_awal" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Tanggal Akhir</label>
                                    <input type="date" name="tanggal_akhir" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-text text-muted">
                            Data ditampilkan berdasarkan tanggal dibuatnya tindak lanjut (created_at) pada
                            rentang tanggal yang dipilih.
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-grid d-sm-inline-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Generate Excel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php
include "../templates/footer.php";
?>