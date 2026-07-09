<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$audit_id = (int)$_GET['audit_id'];

$qAudit = mysqli_query($conn,"SELECT id, nomor_audit, judul_audit, tahun_audit
	FROM audit_pemeriksaan
	WHERE id=$audit_id");

$audit = mysqli_fetch_assoc($qAudit);

if(!$audit){die("Audit tidak ditemukan");}

/*
Generate Nomor Temuan
Format:
TM-2026-001-001
*/

$qCount = mysqli_query($conn,"SELECT COUNT(*) total FROM audit_temuan WHERE audit_id=$audit_id");
$d = mysqli_fetch_assoc($qCount);
$urut = $d['total'] + 1;
$bagianAudit = explode('-',$audit['nomor_audit']);

/*
AUD-2026-001
      ↓
001
*/

$nomorAudit = end($bagianAudit);
$nomor_temuan ='TM-' . $audit['tahun_audit'] . '-' . $nomorAudit . '-' . str_pad($urut,3,'0',STR_PAD_LEFT);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header">
     <h3 class="card-title">Tambah Temuan Audit</h3>
    </div>
    <form action="store.php" method="post">
     <input type="hidden" name="audit_id" value="<?= $audit_id ?>">
     <div class="card-body">
      <div class="row">
       <div class="col-md-6">
	<label>Nomor Temuan</label>
	<input type="text" name="nomor_temuan" value="<?= $nomor_temuan ?>" class="form-control" readonly>
       </div>
       <div class="col-md-6">
        <label>Status</label>
        <select name="status" class="form-select">
	<option value="OPEN">OPEN</option>
	<option value="PROSES">PROSES</option>
	<option value="CLOSED">CLOSED</option>
	</select>
       </div>
      </div>
      <br>
      <label>Judul Temuan</label>
      <input type="text" name="judul_temuan" class="form-control" required>
      <br>
      <label>Kondisi</label>
      <textarea name="kondisi" class="form-control" rows="4" required></textarea>
      <br>
      <label>Kriteria</label>
      <textarea name="kriteria" class="form-control" rows="4" required></textarea>
      <br>
      <label>Sebab</label>
      <textarea name="sebab" class="form-control" rows="4" required></textarea>
      <br>
      <label>Akibat</label>
      <textarea name="akibat" class="form-control" rows="4" required></textarea>
      <br>
      <div class="row">
       <div class="col-md-4">
        <label>Tingkat Risiko</label>
        <select name="tingkat_risiko" class="form-select">
	<option value="RENDAH">RENDAH</option>
	<option value="SEDANG" selected>SEDANG</option>
	<option value="TINGGI">TINGGI</option>
	</select>
      </div>
     </div>
    </div>
    <div class="card-footer">
     <button type="submit" class="btn btn-primary"> Simpan Temuan</button>
     <a href="index.php?audit_id=<?= $audit_id ?>" class="btn btn-secondary">Kembali</a>
    </div>
   </form>
  </div>
 </div>
</div>
</main>

<?php
include "../templates/footer.php";
?>
