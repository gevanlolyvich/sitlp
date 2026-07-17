<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT * FROM audit_temuan WHERE id=$id");
$temuan = mysqli_fetch_assoc($q);

if(!$temuan){
    die("Temuan tidak ditemukan");
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Edit Temuan Audit</h3>
    </div>
    <form action="update.php" method="post">
     <input type="hidden" name="id" value="<?= $temuan['id'] ?>">
     <input type="hidden" name="audit_id" value="<?= $temuan['audit_id'] ?>">
     <div class="card-body">
      <div class="row">
       <div class="col-md-6">
        <label>Nomor Temuan</label>
        <input type="text" name="nomor_temuan" value="<?= htmlspecialchars($temuan['nomor_temuan']) ?>" class="form-control" readonly>
       </div>
       <div class="col-md-6">
        <label>Status</label>
        <select name="status" class="form-select">
         <option value="OPEN" <?= $temuan['status']=='OPEN'?'selected':'' ?>>OPEN</option>
         <option value="PROSES" <?= $temuan['status']=='PROSES'?'selected':'' ?>>PROSES</option>
         <option value="CLOSED" <?= $temuan['status']=='CLOSED'?'selected':'' ?>>CLOSED</option>
        </select>
       </div>
      </div>
      <br>
      <label>Judul Temuan</label>
      <input type="text" name="judul_temuan" value="<?= htmlspecialchars($temuan['judul_temuan']) ?>" class="form-control" required>
      <br>
      <label>Kondisi</label>
      <textarea name="kondisi" class="form-control" rows="4" required><?= htmlspecialchars($temuan['kondisi']) ?></textarea>
      <br>
      <label>Kriteria</label>
      <textarea name="kriteria" class="form-control" rows="4" required><?= htmlspecialchars($temuan['kriteria']) ?></textarea>
      <br>
      <label>Sebab</label>
      <textarea name="sebab" class="form-control" rows="4" required><?= htmlspecialchars($temuan['sebab']) ?></textarea>
      <br>
      <label>Akibat</label>
      <textarea name="akibat" class="form-control" rows="4" required><?= htmlspecialchars($temuan['akibat']) ?></textarea>
      <br>
      <div class="row">
       <div class="col-md-4">
        <label>Tingkat Risiko</label>
        <select name="tingkat_risiko" class="form-select">
         <option value="RENDAH" <?= $temuan['tingkat_risiko']=='RENDAH'?'selected':'' ?>>RENDAH</option>
         <option value="SEDANG" <?= $temuan['tingkat_risiko']=='SEDANG'?'selected':'' ?>>SEDANG</option>
         <option value="TINGGI" <?= $temuan['tingkat_risiko']=='TINGGI'?'selected':'' ?>>TINGGI</option>
        </select>
       </div>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="index.php?audit_id=<?= $temuan['audit_id'] ?>" class="btn btn-secondary">Kembali</a>
     </div>
    </form>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
