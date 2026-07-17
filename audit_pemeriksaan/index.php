<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI','AUDITOR']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";


$where = "";
if (
    isset($_GET['keyword']) &&
    trim($_GET['keyword']) != ''
) {

    $keyword = mysqli_real_escape_string(
        $conn,
        trim($_GET['keyword'])
    );
    $where = "
        WHERE
            ap.nomor_audit LIKE '%$keyword%'
            OR
            ap.judul_audit LIKE '%$keyword%'
    ";
}


$q = mysqli_query($conn,"SELECT ap.*, uk.nama_unit, au.nama_auditor
	FROM audit_pemeriksaan ap
	LEFT JOIN unit_kerja uk ON ap.unit_id = uk.id
	LEFT JOIN auditor au ON ap.ketua_auditor_id = au.id
        $where
	ORDER BY ap.id DESC");

?>

<main class="app-main">
 <div class="app-content">
  <div class="container-fluid">
   <div class="card mt-3">
    <div class="card-header d-flex justify-content-between">
     <h3 class="card-title">Pemeriksaan Audit</h3>
     <a href="../audit_program/index.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i>Dari Program Audit</a>
    </div>
    <div class="card-body">


<!-- -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="search-form">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Cari berdasarkan Nomor atau Judul..." value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i>Cari</button>
                    <?php if(isset($_GET['keyword']) && $_GET['keyword'] != ''): ?>
                    <a href="index.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- -->


     <div class="table-responsive-wrapper"><table id="tblAudit" class="table table-bordered table-striped">
      <thead>
       <tr>
	<th>No Audit</th>
	<th>Judul Audit</th>
	<th>Unit Kerja</th>
	<th>Ketua Auditor</th>
	<th>Status</th>
	<th>Aksi</th>
       </tr>
      </thead>
      <tbody>
       <?php while($row=mysqli_fetch_assoc($q)){ ?>
       <tr>
        <td><?= htmlspecialchars($row['nomor_audit']) ?></td>
	<td><?= htmlspecialchars($row['judul_audit']) ?></td>
	<td><?= htmlspecialchars($row['nama_unit']) ?></td>
	<td><?= htmlspecialchars($row['nama_auditor']) ?></td>
        <td><?php
		$status = $row['status'];
		if($status=='DRAFT'){
		    echo '<span class="badge bg-secondary">DRAFT</span>';
		} elseif($status=='BERJALAN'){
		    echo '<span class="badge bg-warning">BERJALAN</span>';
		} else{
		    echo '<span class="badge bg-success">SELESAI</span>';
		}
	?></td>
	<td>
	 <a href="detail.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">Detail</a>
	 <?php if($row['status'] != 'SELESAI'): ?>
	 <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
	 <?php endif; ?>
	 <a href="lha_pdf.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" target="_blank"><i class="fas fa-file-pdf"></i>LHA</a>

	 <?php if(!empty($row['lha_file'])): ?>
	 <a href="../uploads/lha/<?= $row['lha_file'] ?>" target="_blank" class="btn btn-success btn-sm">
<i class="fa fa-file-pdf"></i>Lihat LHA</a>
         <?php endif; ?>

	</td>
       </tr>
      <?php } ?>
      </tbody>
     </table>
     </div>
    </div>

    <div class="mb-2">
    <small class="text-muted">
        Total Data:
        <strong>
            <?= mysqli_num_rows($q) ?>
        </strong>
    </small>
    </div>


   </div>
  </div>
 </div>
</main>

<script>
 $(document).ready(function(){
  $('#tblAudit').DataTable({
   responsive:true,
   pageLength:10
  });
 });
</script>

<?php
include "../templates/footer.php";
?>
