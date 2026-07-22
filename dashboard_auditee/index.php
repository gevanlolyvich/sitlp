<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];

$totalTL = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id"))[0];

$proses              = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Proses'"))[0];
$sesuai              = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Sesuai'"))[0];
$belumSesuai         = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Belum Sesuai'"))[0];
$belumDitindakLanjut = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Belum Ditindak Lanjut'"))[0];
$tidakDapat          = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Tidak Dapat Ditindak Lanjut'"))[0];

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">

<div class="row">

 <!-- Card 1: Total Tindak Lanjut -->
 <div class="col-md-4 mb-4">
  <div class="card shadow-sm border-primary" style="border-left:5px solid #007bff;">
   <div class="card-body text-center py-4">
    <h6 class="text-muted mb-2">Total Tindak Lanjut</h6>
    <h1 class="mb-0 font-weight-bold text-primary"><?= $totalTL ?></h1>
   </div>
  </div>
 </div>

 <!-- Card 2: Status Tindak Lanjut -->
 <div class="col-md-8 mb-4">
  <div class="card shadow-sm">
   <div class="card-header bg-white">
    <h6 class="card-title mb-0">Status Tindak Lanjut</h6>
   </div>
   <div class="card-body">
    <table class="table table-borderless mb-0">
     <tr>
      <td style="width:40%;padding:10px 0;"><span class="badge bg-warning" style="font-size:14px;padding:8px 16px;">Proses</span></td>
      <td style="width:60%;padding:10px 0;text-align:right;font-weight:700;font-size:22px;"><?= $proses ?></td>
     </tr>
     <tr>
      <td style="padding:10px 0;"><span class="badge bg-success" style="font-size:14px;padding:8px 16px;">Sesuai</span></td>
      <td style="padding:10px 0;text-align:right;font-weight:700;font-size:22px;"><?= $sesuai ?></td>
     </tr>
     <tr>
      <td style="padding:10px 0;"><span class="badge bg-danger" style="font-size:14px;padding:8px 16px;">Belum Sesuai</span></td>
      <td style="padding:10px 0;text-align:right;font-weight:700;font-size:22px;"><?= $belumSesuai ?></td>
     </tr>
     <tr>
      <td style="padding:10px 0;"><span class="badge bg-secondary text-white" style="font-size:14px;padding:8px 16px;">Belum Ditindak Lanjut</span></td>
      <td style="padding:10px 0;text-align:right;font-weight:700;font-size:22px;"><?= $belumDitindakLanjut ?></td>
     </tr>
     <tr>
      <td style="padding:10px 0;"><span class="badge bg-dark text-white" style="font-size:14px;padding:8px 16px;">Tidak Dapat Ditindak Lanjut</span></td>
      <td style="padding:10px 0;text-align:right;font-weight:700;font-size:22px;"><?= $tidakDapat ?></td>
     </tr>
    </table>
   </div>
  </div>
 </div>

</div>
</div>
</div>
</main>

<?php include "../templates/footer.php"; ?>
