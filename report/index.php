<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole([
    'ADMIN',
    'KEPALA_SPI',
    'DIREKSI'
]);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
		 <h1>Generate Report Excel</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
			 <a href="../dashboard">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Export Excel</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Filter Report</h3>
                </div>
                <form action="export_excel.php" method="GET">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Awal</label>
                                    <input type="date" name="tanggal_awal" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Tanggal Akhir</label>
                                    <input type="date" name="tanggal_akhir" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Jenis Report</label>
                                    <select name="jenis" class="form-control">
                                        <option value="all">Semua Report</option>
                                        <option value="audit">Pemeriksaan Audit</option>
                                        <option value="temuan">Temuan Audit</option>
                                        <option value="rekomendasi">Rekomendasi Audit</option>
					<option value="tindak_lanjut">Tindak Lanjut</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-success"><i class="fas fa-file-excel"></i>Generate Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<?php
include "../templates/footer.php";
?>

