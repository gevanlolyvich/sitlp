<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT t.*, u.nama FROM audit_temuan t LEFT JOIN users u ON t.created_by=u.id WHERE t.id=$id");
$temuan = mysqli_fetch_assoc($q);
if(!$temuan)
{
    die("Temuan tidak ditemukan");
}

$locked = isAuditLocked($conn, $temuan['audit_id']);

$qRekomendasi = mysqli_query($conn,"SELECT * FROM audit_rekomendasi WHERE temuan_id=$id ORDER BY id");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header d-flex align-items-center">
     <h3 class="card-title mb-0"><?= htmlspecialchars($temuan['nomor_temuan']) ?></h3>
     <a href="index.php?audit_id=<?= $temuan['audit_id'] ?>" class="btn btn-secondary btn-sm ms-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
    <div class="table-responsive-wrapper"><table class="table table-bordered">
     <tr>
      <th width="220">Judul Temuan</th>
       <td><?= htmlspecialchars($temuan['judul_temuan']) ?></td>
     </tr>
     <tr>
      <th>Tingkat Risiko</th>
       <td>
        <?php
         if($temuan['tingkat_risiko']=='Tinggi'){
    	  echo '<span class="badge bg-danger">Tinggi</span>';
         }elseif($temuan['tingkat_risiko']=='Sedang'){
          echo '<span class="badge bg-warning">Sedang</span>';
         } else {
          echo '<span class="badge bg-success">Rendah</span>';
         }
        ?>
       </td>
      </tr>
      <tr>
       <th>Status</th>
        <td><?= htmlspecialchars($temuan['status']) ?></td>
      </tr>
      <tr>
       <th>Dibuat Oleh</th>
        <td><?= htmlspecialchars($temuan['nama']) ?></td>
      </tr>
      <tr>
       <th>Tanggal</th>
        <td><?= $temuan['created_at'] ?></td>
      </tr>
     </table></div>
     <hr>
     <h5>Kondisi</h5>
     <div class="alert alert-light">
      <?= $temuan['kondisi'] ?>
     </div>
     <h5>Kriteria</h5>
     <div class="alert alert-light">
      <?= nl2br(htmlspecialchars($temuan['kriteria'])) ?>
     </div>
     <h5>Sebab</h5>
     <div class="alert alert-light">
      <?= nl2br(htmlspecialchars($temuan['sebab'])) ?>
     </div>
     <h5>Akibat</h5>
     <div class="alert alert-light">
      <?= nl2br(htmlspecialchars($temuan['akibat'])) ?>
     </div>
    </div>
   </div>
   <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
     <h3 class="card-title">Rekomendasi</h3>
     <?php if(!$locked): ?>
     <a href="../audit_rekomendasi/create.php?temuan_id=<?= $temuan['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>Tambah Rekomendasi</a>
     <?php endif; ?>
    </div>
    <div class="card-body">
     <?php
      $rekomendasiList = [];
      while($r = mysqli_fetch_assoc($qRekomendasi)){ $rekomendasiList[] = $r; }
      $jmlRekomendasi = count($rekomendasiList);
     ?>
     <?php if($jmlRekomendasi > 0): ?>
     <div class="table-responsive-wrapper"><table class="table table-bordered">
      <thead>
       <tr>
        <th width="40">No</th>
        <th>Nomor Temuan</th>
        <th>Rekomendasi</th>
        <th width="200">Aksi</th>
       </tr>
      </thead>
      <tbody>
       <tr>
        <td>1</td>
        <td><?= htmlspecialchars($temuan['nomor_temuan']) ?></td>
        <td>
         <a href="#rekomendasi-collapse" data-bs-toggle="collapse" data-bs-target="#rekomendasi-collapse" role="button" aria-expanded="false" aria-controls="rekomendasi-collapse" class="collapse-trigger text-decoration-none text-reset">
          <i class="fas fa-chevron-circle-down me-1"></i> <?= $jmlRekomendasi ?> Rekomendasi
         </a>
        </td>
        <td>
         <a href="../audit_tindak_lanjut/index.php?temuan_id=<?= $temuan['id'] ?>" class="btn btn-success btn-sm"><i class="fas fa-list"></i> Tindak Lanjut</a>
         <?php if(!$locked): ?>
         <a href="../audit_rekomendasi/edit.php?temuan_id=<?= $temuan['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
         <?php endif; ?>
        </td>
       </tr>
       <tr>
        <td colspan="4" class="p-0">
         <div class="collapse" id="rekomendasi-collapse">
          <div class="p-2 bg-light border-top">
           <table class="table table-sm table-bordered mb-0">
            <thead>
             <tr>
              <th width="40">No</th>
              <th>Rekomendasi</th>
             </tr>
            </thead>
            <tbody>
             <?php $no = 1; foreach($rekomendasiList as $r): ?>
             <tr>
              <td><?= $no++ ?></td>
              <td style="white-space: pre-wrap;"><?= htmlspecialchars($r['rekomendasi']) ?></td>
             </tr>
             <?php endforeach; ?>
            </tbody>
           </table>
          </div>
         </div>
        </td>
       </tr>
      </tbody>
     </table>
     </div>
     <?php else: ?>
     <p class="text-muted mb-0">Belum ada rekomendasi untuk temuan ini.</p>
     <?php endif; ?>
    </div>
   </div>
  </div>
 </div>
</main>

<style>
.collapse-trigger i.fa-chevron-circle-down { display:inline-block; transition: transform .2s; }
.collapse-trigger.collapsed i.fa-chevron-circle-down { transform: rotate(-90deg); }
</style>

<?php if (isset($_SESSION['success'])) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= addslashes($_SESSION['success']) ?>',
        timer: 2500,
        showConfirmButton: false
    });
});
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '<?= addslashes($_SESSION['error']) ?>'
    });
});
</script>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php
include "../templates/footer.php";
?>
