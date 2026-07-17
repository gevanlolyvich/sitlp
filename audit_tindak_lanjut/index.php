<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR','AUDITEE','DIREKSI']);

//
//$rekomendasi_id = (int)$_GET['rekomendasi_id'];
//$qRek = mysqli_query($conn,"SELECT r.*, t.nomor_temuan, t.judul_temuan
//	FROM audit_rekomendasi r
//	LEFT JOIN audit_temuan t ON r.temuan_id=t.id
//	WHERE r.id=$rekomendasi_id");
//
//$rekomendasi = mysqli_fetch_assoc($qRek);
//
//if(!$rekomendasi){
//    die("Rekomendasi tidak ditemukan");
//}
//
$rekomendasi = null;
$rekomendasi_id = 0;
if(isset($_GET['rekomendasi_id']))
{
    $rekomendasi_id = (int)$_GET['rekomendasi_id'];
    $qRek = mysqli_query($conn,"
        SELECT
            r.*,
            t.nomor_temuan,
            t.judul_temuan
        FROM audit_rekomendasi r
        LEFT JOIN audit_temuan t
            ON r.temuan_id=t.id
        WHERE r.id=$rekomendasi_id");
    $rekomendasi = mysqli_fetch_assoc($qRek);
    if(!$rekomendasi)
    {
        die(
            "Rekomendasi tidak ditemukan"
        );
    }
}


//$qTL = mysqli_query($conn,"SELECT tl.*, u.nama_unit
//	FROM audit_tindak_lanjut tl
//	LEFT JOIN unit_kerja u ON tl.unit_id=u.id
//	WHERE tl.rekomendasi_id=$rekomendasi_id
//	ORDER BY tl.id");
$where = [];
if($rekomendasi_id > 0)
{
    $where[] = "tl.rekomendasi_id=$rekomendasi_id";
}
if($_SESSION['role'] == 'AUDITEE'){
    $unit_id = (int)$_SESSION['unit_id'];
    //$where[] = "tl.unit_id=$unit_id";
    $where[] = "tl.unit_id = ".(int)$_SESSION['unit_id'];
}
$sqlWhere = '';
if(count($where))
{
    $sqlWhere =
        'WHERE ' .
        implode(
            ' AND ',
            $where
        );
}
$qTL = mysqli_query($conn,"
    SELECT
        tl.*,
        u.nama_unit,
        r.rekomendasi,
        t.judul_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u
        ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r
        ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t
        ON r.temuan_id=t.id
    $sqlWhere
    ORDER BY
        tl.id DESC");

/*
echo "    SELECT
        tl.*,
        u.nama_unit,
        r.rekomendasi,
        t.judul_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u
        ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r
        ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t
        ON r.temuan_id=t.id
    $sqlWhere
    ORDER BY
	tl.id DESC";
exit;
 */


include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<?php if(isset($_GET['upload']) && $_GET['upload']=='success'){ ?>
<script>
document.addEventListener('DOMContentLoaded', function(){

Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: 'Bukti tindak lanjut berhasil diupload',
    timer: 2500,
    showConfirmButton: false
});

});
</script>
<?php } ?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">
<div class="card mt-3">
<div class="card-header d-flex align-items-center">
<h3 class="card-title mb-0">Tindak Lanjut Rekomendasi</h3>
<?php if(isset($_GET['rekomendasi_id']) && $rekomendasi && $_SESSION['role'] != 'AUDITEE'): ?>
<a href="../audit_temuan/detail.php?id=<?= $rekomendasi['temuan_id'] ?>" class="btn btn-secondary btn-sm ms-auto"><i class="fas fa-arrow-left"></i> Kembali</a>
<?php endif; ?>
</div>
<div class="card-body">
<?php if(isset($_GET['rekomendasi_id'])){ ?>
<div class="table-responsive-wrapper"><table class="table table-bordered">
<tr>
<th width="220">Nomor Temuan</th>
<td><?= htmlspecialchars($rekomendasi['nomor_temuan']) ?></td>
</tr>
<tr>
<th>Judul Temuan</th>
<td><?= htmlspecialchars($rekomendasi['judul_temuan']) ?></td>
</tr>
<tr>
<th>Nomor Rekomendasi</th>
<td><?= htmlspecialchars($rekomendasi['nomor_rekomendasi']) ?></td>
</tr>
<tr>
<th>Rekomendasi</th>
<td><?= nl2br(htmlspecialchars($rekomendasi['rekomendasi'])) ?></td>
</tr>
</table></div>
<?php } ?>
</div>
</div>

<div class="card">
<div class="card-header d-flex justify-content-between">
<h3 class="card-title">Daftar Tindak Lanjut</h3>
<?php if(isset($_GET['rekomendasi_id']) && $_SESSION['role'] != 'AUDITEE'){ ?>
<a href="create.php?rekomendasi_id=<?= $rekomendasi_id ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>Tambah Tindak Lanjut</a>
<?php } ?>
</div>

<div class="card-body">

<div class="table-responsive-wrapper"><table id="tblTL" class="table table-bordered table-striped">
 <thead>
  <tr>
   <th>Nomor TL</th>
   <th>Unit</th>
   <th>PIC</th>
   <th>Uraian Tindak Lanjut</th>
   <th>Hasil Tindak Lanjut</th>
   <th>Target</th>
   <th>Status</th>
   <th>Verifikasi</th>
   <th width="180">Aksi</th>
  </tr>
 </thead>
 <tbody>
  <?php while($row = mysqli_fetch_assoc($qTL)){ ?>
  <tr>
   <td><?= htmlspecialchars($row['nomor_tindak_lanjut']) ?></td>
   <td><?= htmlspecialchars($row['nama_unit']) ?></td>
   <td><?= htmlspecialchars($row['pic']) ?></td>
   <td style="white-space: pre-wrap; word-wrap: break-word; max-width: 300px;"><?= htmlspecialchars($row['uraian_tindak_lanjut']) ?></td>
   <td style="white-space: pre-wrap; word-wrap: break-word; max-width: 300px;"><?= htmlspecialchars($row['hasil_tindak_lanjut']) ?></td>
   <td><?= $row['target_selesai'] ?></td>
   <td>
    <?php
   	$status = $row['status'];
	if($status=='OPEN'){
	    echo '<span class="badge bg-danger">OPEN</span>';
	} elseif($status=='PROSES'){
	    echo '<span class="badge bg-warning">PROSES</span>';
	} else {
	    echo '<span class="badge bg-success">SELESAI</span>';
	}
    ?>
   </td>
  <td>
   <?php
   if($row['verifikasi_status']=='DITERIMA'){
    echo '<span class="badge bg-success">DITERIMA</span>';
   }elseif($row['verifikasi_status']=='DITOLAK'){
    echo '<span class="badge bg-danger">DITOLAK</span>';
   }else{
    echo '<span class="badge bg-secondary">BELUM</span>';}
   ?>
  </td>
   <td>
    <a href="../uploads/tindak_lanjut/<?= $row['bukti_file'] ?>" target="_blank" class="btn btn-success btn-sm <?= !$row['bukti_file'] ? 'disabled' : '' ?>">Lihat Bukti</a>
    <?php if(!$row['bukti_file'] && $_SESSION['role'] != 'KEPALA_SPI') { ?>
    <a href="upload_bukti.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Upload Bukti</a>
    <?php } ?>
   <!--
   <a href="verifikasi.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Verifikasi</a>
   -->
   <?php if(
    in_array(
    $_SESSION['role'],
    ['ADMIN','KEPALA_SPI']
   )): ?>
   <a href="verifikasi.php?id=<?= $row['id'] ?>" class="btn btn-success btn-sm <?= (!$row['bukti_file'] || $row['verifikasi_status'] != 'BELUM') ? 'disabled' : '' ?>">Verifikasi</a>
   <?php endif; ?>
   <?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI','AUDITOR'])): ?>
   <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
   <?php if($rekomendasi_id > 0): ?>
   <a href="javascript:void(0)"
   class="btn btn-danger btn-sm" onclick="hapusTL(<?= $row['id'] ?>, <?= $rekomendasi_id ?>)">Hapus</a>
   <?php endif; ?>
   <?php endif; ?>
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

<script>
$(document).ready(function(){
 $('#tblTL').DataTable({
  responsive:true
 });
});
</script>

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
function hapusTL(id, rekomendasi_id)
{
    Swal.fire({
        title: 'Hapus Tindak Lanjut?',
        text: 'Data tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    })
    .then((result) => {
        if (result.isConfirmed) {
            window.location = 'delete.php?id=' + id + '&rekomendasi_id=' + rekomendasi_id;
        }
    });
}
</script>

<?php include "../templates/footer.php"; ?>
