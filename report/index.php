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
    'KOMISARIS',
    'KOMITE_AUDIT'
]);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-file-excel me-2 text-primary"></i>Export Excel</h1>
                    <div class="jxb-page-subtitle">Matrik pemantauan tindak lanjut berdasarkan rentang tanggal</div>
                </div>
            </div>
            <div class="card">
                <form action="export_excel.php" method="GET">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Awal <span class="jxb-required">*</span></label>
                                    <input type="date" name="tanggal_awal" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Akhir <span class="jxb-required">*</span></label>
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
                        <div class="jxb-form-actions">
                            <button type="submit" class="btn btn-primary">
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