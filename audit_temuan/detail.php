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
         if($temuan['tingkat_risiko']=='TINGGI'){
    	  echo '<span class="badge bg-danger">TINGGI</span>';
         }elseif($temuan['tingkat_risiko']=='SEDANG'){
          echo '<span class="badge bg-warning">SEDANG</span>';
         } else {
          echo '<span class="badge bg-success">RENDAH</span>';
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
      <?= nl2br(htmlspecialchars($temuan['kondisi'])) ?>
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
    <div class="card-header d-flex justify-content-between">
     <h3 class="card-title">Rekomendasi</h3>
     <a href="../audit_rekomendasi/create.php?temuan_id=<?= $temuan['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>Tambah Rekomendasi</a>
    </div>
    <div class="card-body">
     <div class="table-responsive-wrapper"><table class="table table-bordered">
      <thead>
       <tr>
	<th>No</th>
	<th>Nomor</th>
	<th>Rekomendasi</th>
	<th>Prioritas</th>
	<th width="180">Aksi</th>
       </tr>
      </thead>
      <tbody>
       <?php
        $no = 1;
        while($r=mysqli_fetch_assoc($qRekomendasi)){
       ?>
       <tr>
        <td><?= $no++ ?></td>
	<td><?= htmlspecialchars($r['nomor_rekomendasi']) ?></td>
	<td><?= htmlspecialchars($r['rekomendasi']) ?></td>
	<td><?= htmlspecialchars($r['prioritas']) ?></td>
	<td><a href="../audit_tindak_lanjut/index.php?rekomendasi_id=<?= $r['id'] ?>" class="btn btn-success btn-sm">Tindak Lanjut</a>
	    <a href="../audit_rekomendasi/edit.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
        </td>
       </tr>
       <?php } ?>
      </tbody>
     </table>
     </div>
    </div>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
