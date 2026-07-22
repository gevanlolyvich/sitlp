<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT r.*, t.nomor_temuan, t.judul_temuan FROM audit_rekomendasi r LEFT JOIN audit_temuan t ON r.temuan_id=t.id WHERE r.id=$id");
$rekomendasi = mysqli_fetch_assoc($q);

if(!$rekomendasi){
    die("Rekomendasi tidak ditemukan");
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
     <h3 class="card-title">Edit Rekomendasi</h3>
    </div>
    <form action="update.php" method="post">
     <input type="hidden" name="id" value="<?= $rekomendasi['id'] ?>">
     <input type="hidden" name="temuan_id" value="<?= $rekomendasi['temuan_id'] ?>">
     <div class="card-body">
      <div class="mb-3">
       <label>Nomor Temuan</label>
       <input type="text" class="form-control" value="<?= htmlspecialchars($rekomendasi['nomor_temuan']) ?>" readonly>
      </div>
       <div class="mb-3">
        <label>Nomor Rekomendasi</label>
        <input type="text" name="nomor_rekomendasi" value="<?= htmlspecialchars($rekomendasi['nomor_rekomendasi']) ?>" class="form-control" required>
       </div>
      <div class="mb-3">
       <label>Rekomendasi</label>
       <textarea name="rekomendasi" class="form-control" rows="5" required><?= htmlspecialchars($rekomendasi['rekomendasi']) ?></textarea>
      </div>
      <div class="mb-3">
       <label>Prioritas</label>
       <select name="prioritas" class="form-select">
        <option value="RENDAH" <?= $rekomendasi['prioritas']=='RENDAH'?'selected':'' ?>>RENDAH</option>
        <option value="SEDANG" <?= $rekomendasi['prioritas']=='SEDANG'?'selected':'' ?>>SEDANG</option>
        <option value="TINGGI" <?= $rekomendasi['prioritas']=='TINGGI'?'selected':'' ?>>TINGGI</option>
       </select>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="../audit_temuan/detail.php?id=<?= $rekomendasi['temuan_id'] ?>" class="btn btn-secondary">Kembali</a>
     </div>
    </form>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
