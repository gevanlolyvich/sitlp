<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'DIREKSI', 'KOMISARIS']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

<div class="row justify-content-center" style="margin-top:60px;">
 <div class="col-md-6 col-lg-5 mb-4">
  <a href="sia.php" class="text-decoration-none">
   <div class="card bg-primary text-white text-center py-5 shadow-lg" style="border-radius:16px;cursor:pointer;transition:transform .2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
    <div class="card-body">
     <i class="fas fa-clipboard-list mb-3" style="font-size:48px;"></i>
     <h2 class="mb-1"><strong>SIA</strong></h2>
     <small>Satuan Internal Audit</small>
    </div>
   </div>
  </a>
 </div>
 <div class="col-md-6 col-lg-5 mb-4">
  <a href="mr.php" class="text-decoration-none">
   <div class="card bg-secondary text-white text-center py-5 shadow-lg" style="border-radius:16px;cursor:pointer;transition:transform .2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
    <div class="card-body">
     <i class="fas fa-building mb-3" style="font-size:48px;"></i>
     <h2 class="mb-1"><strong>MR</strong></h2>
     <small>Management Representative</small>
    </div>
   </div>
  </a>
 </div>
</div>

</div>
</div>
</main>

<?php include "../templates/footer.php"; ?>
