<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'DIREKSI', 'KOMISARIS']);

$totalProgram     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_program"))[0];
$totalPemeriksaan = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_pemeriksaan"))[0];
$totalTemuan      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_temuan"))[0];
$totalRekomendasi = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_rekomendasi"))[0];
$totalTL          = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut"))[0];

$proses             = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Proses'"))[0];
$sesuai             = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Sesuai'"))[0];
$belumSesuai        = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Belum Sesuai'"))[0];
$belumDitindakLanjut= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Belum Ditindak Lanjut'"))[0];
$tidakDapat         = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Tidak Dapat Ditindak Lanjut'"))[0];

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

<div class="jxb-page-header">
 <div>
  <h1 class="jxb-page-title"><i class="fas fa-clipboard-list me-2 text-primary"></i>Dashboard Monitoring SIA</h1>
  <div class="jxb-page-subtitle">Ringkasan program, pemeriksaan, temuan, dan tindak lanjut</div>
 </div>
 <div class="jxb-page-actions">
  <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
 </div>
</div>

<div class="row g-3 mb-3">
 <div class="col-6 col-lg">
  <div class="jxb-kpi-card p-3 h-100">
   <div class="d-flex align-items-center gap-2 mb-2">
    <span class="kpi-icon"><i class="fas fa-calendar-alt"></i></span>
    <span class="kpi-label">Program Audit</span>
   </div>
   <div class="kpi-value"><?= $totalProgram ?></div>
  </div>
 </div>
 <div class="col-6 col-lg">
  <div class="jxb-kpi-card p-3 h-100">
   <div class="d-flex align-items-center gap-2 mb-2">
    <span class="kpi-icon"><i class="fas fa-clipboard-check"></i></span>
    <span class="kpi-label">Pemeriksaan</span>
   </div>
   <div class="kpi-value"><?= $totalPemeriksaan ?></div>
  </div>
 </div>
 <div class="col-6 col-lg">
  <div class="jxb-kpi-card p-3 h-100">
   <div class="d-flex align-items-center gap-2 mb-2">
    <span class="kpi-icon"><i class="fas fa-search"></i></span>
    <span class="kpi-label">Temuan</span>
   </div>
   <div class="kpi-value"><?= $totalTemuan ?></div>
  </div>
 </div>
 <div class="col-6 col-lg">
  <div class="jxb-kpi-card p-3 h-100">
   <div class="d-flex align-items-center gap-2 mb-2">
    <span class="kpi-icon"><i class="fas fa-lightbulb"></i></span>
    <span class="kpi-label">Rekomendasi</span>
   </div>
   <div class="kpi-value"><?= $totalRekomendasi ?></div>
  </div>
 </div>
 <div class="col-6 col-lg">
  <div class="jxb-kpi-card p-3 h-100">
   <div class="d-flex align-items-center gap-2 mb-2">
    <span class="kpi-icon"><i class="fas fa-tasks"></i></span>
    <span class="kpi-label">Tindak Lanjut</span>
   </div>
   <div class="kpi-value"><?= $totalTL ?></div>
  </div>
 </div>
</div>

<div class="card">
 <div class="card-header">
  <h6 class="card-title mb-0"><i class="fas fa-list-check me-2 text-primary"></i>Status Tindak Lanjut</h6>
 </div>
 <div class="card-body">
  <table class="table table-borderless mb-0">
   <tbody>
    <tr>
     <td><span class="jxb-status-badge is-info">Proses</span></td>
     <td class="text-end fw-bold fs-5"><?= $proses ?></td>
    </tr>
    <tr>
     <td><span class="jxb-status-badge is-success">Sesuai</span></td>
     <td class="text-end fw-bold fs-5"><?= $sesuai ?></td>
    </tr>
    <tr>
     <td><span class="jxb-status-badge is-danger">Belum Sesuai</span></td>
     <td class="text-end fw-bold fs-5"><?= $belumSesuai ?></td>
    </tr>
    <tr>
     <td><span class="jxb-status-badge is-warn">Belum Ditindak Lanjut</span></td>
     <td class="text-end fw-bold fs-5"><?= $belumDitindakLanjut ?></td>
    </tr>
    <tr>
     <td><span class="jxb-status-badge is-neutral">Tidak Dapat Ditindak Lanjut</span></td>
     <td class="text-end fw-bold fs-5"><?= $tidakDapat ?></td>
    </tr>
   </tbody>
  </table>
 </div>
</div>

</div>
</div>
</main>

<?php include "../templates/footer.php"; ?>
