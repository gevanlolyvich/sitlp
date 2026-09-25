<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'DIREKSI', 'KOMISARIS', 'KOMITE_AUDIT']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

<div class="jxb-page-header">
 <div>
  <h1 class="jxb-page-title"><i class="fas fa-chart-line me-2 text-primary"></i>Dashboard Monitoring SPI</h1>
  <div class="jxb-page-subtitle">Pilih unit pemantauan yang ingin dibuka</div>
 </div>
</div>

<div class="row g-3">
 <div class="col-12 col-md-6">
  <a href="sia.php" class="jxb-choice-card">
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
  <a href="mr.php" class="jxb-choice-card">
   <div class="d-flex align-items-center gap-3">
    <span class="jxb-choice-icon"><i class="fas fa-building"></i></span>
    <div>
     <div class="jxb-choice-title">MR</div>
     <div class="jxb-choice-desc">Management Representative</div>
    </div>
    <i class="fas fa-chevron-right ms-auto jxb-choice-arrow"></i>
   </div>
  </a>
 </div>
</div>

</div>
</div>
</main>

<?php include "../templates/footer.php"; ?>
