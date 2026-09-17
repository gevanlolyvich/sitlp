<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$audit_id = (int)$_GET['audit_id'];

$qAudit = mysqli_query($conn,"SELECT nomor_audit, judul_audit, ketua_auditor_id, status FROM audit_pemeriksaan WHERE id=$audit_id");
$audit = mysqli_fetch_assoc($qAudit);
$locked = ($audit['status'] == 'Selesai');
$ketuaId = (int)$audit['ketua_auditor_id'];
$qAuditor = mysqli_query($conn,"SELECT * FROM auditor WHERE aktif=1 AND id != $ketuaId AND id NOT IN (SELECT auditor_id FROM audit_tim WHERE audit_id=$audit_id) ORDER BY nama_auditor");

$qTim = mysqli_query($conn,"SELECT t.*, a.nama_auditor FROM audit_tim t
	LEFT JOIN auditor a ON t.auditor_id=a.id
	WHERE t.audit_id=$audit_id
	ORDER BY t.id");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="jxb-page-header">
    <div>
     <h1 class="jxb-page-title"><i class="fas fa-users me-2 text-primary"></i>Tim Audit</h1>
     <div class="jxb-page-subtitle"><?= htmlspecialchars($audit['nomor_audit']) ?> &mdash; <?= htmlspecialchars($audit['judul_audit']) ?></div>
    </div>
    <div class="jxb-page-actions">
     <a href="../audit_pemeriksaan/detail.php?id=<?= $audit_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
   </div>
   <div class="card">
    <?php if(!$locked): ?>
    <form action="store.php" method="post">
     <input type="hidden" name="audit_id" value="<?= $audit_id ?>">
     <div class="card-body">
      <div class="row">
       <div class="col-md-5">
        <label class="form-label">Auditor <span class="jxb-required">*</span></label>
        <select name="auditor_id" class="form-select" required>
	 <option value="">Pilih Auditor</option>
         <?php
          while($a=mysqli_fetch_assoc($qAuditor)){
         ?>
         <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_auditor']) ?></option>
         <?php } ?>
        </select>
       </div>
       <div class="col-md-4">
       <label class="form-label">Peran</label>
        <select name="peran" class="form-select">
	<option value="Anggota" selected>Anggota</option>
	<option value="Ketua Tim">Ketua Tim</option>
       </select>
       </div>
      <div class="col-md-3">
       <label class="form-label">&nbsp;</label>
       <button type="submit" class="btn btn-primary w-100">Tambah Tim</button>
      </div>
     </div>
    </div>
   </form>
   <?php else: ?>
   <div class="alert alert-secondary mb-0"><i class="fas fa-lock"></i> Audit sudah ditutup. Tim audit tidak dapat diubah.</div>
   <?php endif; ?>
  </div>
  <div class="card">
   <div class="card-header">
    <h6 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Daftar Anggota Tim</h6>
   </div>
   <div class="card-body">
    <div class="table-responsive-wrapper"><table class="table table-bordered">
     <thead>
      <tr>
	<th>No</th>
	<th>Nama Auditor</th>
	<th>Peran</th>
	<th width="100">Aksi</th>
      </tr>
     </thead>
     <tbody>
      <?php
       if(mysqli_num_rows($qTim) === 0){
      ?>
       <tr>
        <td colspan="4" class="text-center py-4">
         <div class="jxb-empty">
          <i class="fas fa-users"></i>
          <div class="jxb-empty-title mt-1">Belum ada anggota tim</div>
          <div>Tambahkan auditor untuk membentuk tim pemeriksaan.</div>
         </div>
        </td>
       </tr>
      <?php
       } else {
       $no=1;
       while($t=mysqli_fetch_assoc($qTim)){
       ?>
        <tr>
	 <td><?= $no++ ?></td>
	 <td><?= htmlspecialchars($t['nama_auditor']) ?></td>
 	 <td><?php
             $mapPeran = ['KETUA'=>'Ketua Tim','Ketua Tim'=>'Ketua Tim','ANGGOTA'=>'Anggota','Anggota'=>'Anggota','PENGENDALI'=>'Pengendali'];
             $peranTampil = isset($mapPeran[$t['peran']]) ? $mapPeran[$t['peran']] : $t['peran'];
             echo htmlspecialchars($peranTampil);
         ?></td>
	 <td>
	  <?php if(!$locked): ?>
	  <a href="javascript:void(0)" class="btn btn-danger btn-sm" title="Hapus" aria-label="Hapus" onclick="hapusAnggota(<?= $t['id'] ?>, <?= $audit_id ?>)"><i class="fas fa-trash"></i> Hapus</a>
	  <?php endif; ?>
	 </td>
	</tr>
       <?php } } ?>
     </tbody>
    </table>
    </div>
   </div>
  </div>
  </div>
 </div>
</main>

<?php if (isset($_SESSION['success'])) : ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= addslashes($_SESSION['success']) ?>',
    timer: 2500,
    showConfirmButton: false
});
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= addslashes($_SESSION['error']) ?>'
});
</script>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<script>
function hapusAnggota(id, audit_id)
{
    Swal.fire({
        title: 'Hapus Anggota Tim?',
        text: 'Data tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    })
    .then((result) => {
        if (result.isConfirmed) {
            window.location = 'delete.php?id=' + id + '&audit_id=' + audit_id;
        }
    });
}
</script>

<?php
include "../templates/footer.php";
?>
