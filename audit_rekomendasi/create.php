<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

$temuan_id = (int)$_GET['temuan_id'];
$qTemuan = mysqli_query($conn,"SELECT id,nomor_temuan,judul_temuan FROM audit_temuan WHERE id=$temuan_id");
$temuan = mysqli_fetch_assoc($qTemuan);
if(!$temuan)
{
    die("Temuan tidak ditemukan");
}

/*
Generate nomor rekomendasi
*/

$qCount = mysqli_query($conn,"SELECT COUNT(*) total FROM audit_rekomendasi WHERE temuan_id=$temuan_id");
$d = mysqli_fetch_assoc($qCount);
$urut = $d['total'] + 1;

/*
TM-2026-001-001
↓
RK-2026-001-001-001
*/

$nomor_rekomendasi = 'RK-' . substr($temuan['nomor_temuan'],3). '-' .str_pad($urut,3,'0',STR_PAD_LEFT);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Tambah Rekomendasi</h3>
    </div>
    <form action="store.php" method="post">
     <input type="hidden" name="temuan_id" value="<?= $temuan_id ?>">
     <div class="card-body">
      <div class="mb-3">
       <label>Nomor Temuan</label>
       <input type="text" class="form-control" value="<?= htmlspecialchars($temuan['nomor_temuan']) ?>" readonly>
      </div>
      <div class="mb-3">
       <label>Nomor Rekomendasi</label>
       <input type="text" name="nomor_rekomendasi" value="<?= $nomor_rekomendasi ?>" class="form-control" readonly>
      </div>
      <div class="mb-3">
       <label>Rekomendasi</label>
       <textarea name="rekomendasi" class="form-control" rows="5" required></textarea>
      </div>
      <div class="mb-3">
       <label>Prioritas</label>
       <select name="prioritas" class="form-select">
	<option value="RENDAH">RENDAH</option>
        <option value="SEDANG" selected>SEDANG</option>
        <option value="TINGGI">TINGGI</option>
       </select>
      </div>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="../audit_temuan/detail.php?id=<?= $temuan_id ?>" class="btn btn-secondary">Kembali</a>
     </div>
    </form>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
