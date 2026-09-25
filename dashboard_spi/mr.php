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
  <h1 class="jxb-page-title"><i class="fas fa-building me-2 text-primary"></i>Management Representative (MR)</h1>
  <div class="jxb-page-subtitle">Pemantauan Management Representative</div>
 </div>
 <div class="jxb-page-actions">
  <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
 </div>
</div>

<div class="card">
 <div class="card-body py-5">
  <div class="jxb-empty">
   <i class="fas fa-tools"></i>
   <div class="jxb-empty-title mt-1">Belum tersedia</div>
   <div>Fitur Management Representative akan tersedia pada rilis berikutnya.</div>
  </div>
 </div>
</div>

</div>
</div>
</main>

<?php include "../templates/footer.php"; ?>
