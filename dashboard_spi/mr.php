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

<div class="mb-3">
 <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

<div class="card">
 <div class="card-body text-center py-5">
  <i class="fas fa-tools text-muted mb-3" style="font-size:64px;"></i>
  <h4 class="text-muted">Management Representative (MR)</h4>
  <p class="text-muted">Fitur ini akan tersedia pada rilis berikutnya.</p>
 </div>
</div>

</div>
</div>
</main>

<?php include "../templates/footer.php"; ?>
