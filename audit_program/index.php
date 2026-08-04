<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";


$where = "";
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
if ($keyword != '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where = "
        WHERE
            p.kode_program LIKE '%$kw%'
            OR
            p.judul_program LIKE '%$kw%'
    ";
}

$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($limit * ($page - 1));

$countResult = mysqli_query(
    $conn,
    "SELECT COUNT(*) total
    FROM audit_program p
    LEFT JOIN unit_kerja u ON p.unit_id = u.id
    LEFT JOIN auditor a ON p.penanggung_jawab_id = a.id
    $where"
);
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRecords / $limit);

$sql = mysqli_query(
    $conn,
    "
    SELECT
        p.*,
        u.nama_unit,
        a.nama_auditor AS penanggung_jawab
    FROM audit_program p
    LEFT JOIN unit_kerja u
        ON p.unit_id = u.id
    LEFT JOIN auditor a
        ON p.penanggung_jawab_id = a.id
    $where
    ORDER BY p.tahun DESC,
             p.kode_program
    LIMIT $limit OFFSET $offset
    "
);

?>

<main class="app-main">

<div class="app-content">

<div class="container-fluid">

<div class="row mt-3 mb-3">

<div class="col-md-6">

<h3>Program Kerja Pengawasan Tahunan (PKPT)</h3>

</div>

<div class="col-md-6 text-end">

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SIA'])): ?>
<a
href="create.php"
class="btn btn-primary">

<i class="fas fa-plus"></i>

Tambah Program

</a>
<?php endif; ?>

</div>

</div>

<div class="card">

<div class="card-body">

<!-- -->
<div class="row mb-3">
    <div class="col-md-6">
        <form method="GET" class="search-form">
            <div class="input-group">
		<input type="text" name="keyword" class="form-control" placeholder="Cari berdasarkan Kode atau Judul..." 
                 value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary"> <i class="fas fa-search"></i>Cari</button>
                    <?php if(isset($_GET['keyword']) && $_GET['keyword'] != ''): ?>
                    <a href="index.php" class="btn btn-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- -->

<div class="table-responsive-wrapper">
<table
id="tblPKPT"
class="table table-bordered table-striped">

<thead>

<tr>

<th>Kode</th>
<th>Judul</th>
<th>Tahun</th>
<th>TW</th>
<th>Unit Kerja</th>
<th>PIC</th>
<th>Jenis Audit</th>
<th>Risiko</th>
<th>Status</th>
<th>Aksi</th>

</tr>

</thead>

<tbody>

<?php while($r = mysqli_fetch_assoc($sql)){ ?>

<tr>

<td><?= htmlspecialchars($r['kode_program']) ?></td>
<td><?= htmlspecialchars($r['judul_program']) ?></td>
<td><?= htmlspecialchars($r['tahun']) ?></td>
<td><?= htmlspecialchars($r['triwulan']) ?></td>
<td><?= htmlspecialchars($r['nama_unit']) ?></td>
<td><?= htmlspecialchars($r['penanggung_jawab']) ?></td>
<td><?= htmlspecialchars($r['jenis_audit']) ?></td>

<td>

<?php

if($r['level_risiko']=='TINGGI')
{
    echo '<span class="badge bg-danger">TINGGI</span>';
}
elseif($r['level_risiko']=='SEDANG')
{
    echo '<span class="badge bg-warning">SEDANG</span>';
}
else
{
    echo '<span class="badge bg-success">RENDAH</span>';
}

?>

</td>

<td>

<?php

if($r['status']=='RENCANA')
{
    echo '<span class="badge bg-secondary">RENCANA</span>';
}
elseif($r['status']=='BERJALAN')
{
    echo '<span class="badge bg-primary">BERJALAN</span>';
}
else
{
    echo '<span class="badge bg-success">SELESAI</span>';
}

?>

</td>


<td>
<?php if($r['status']=='RENCANA'): ?>
<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SIA'])): ?>
<a href="edit.php?id=<?= $r['id'] ?>"
class="btn btn-warning btn-sm">
Edit
</a>
<button
onclick="hapusProgram(<?= $r['id'] ?>)"
class="btn btn-danger btn-sm">
Hapus
</button>
<?php endif; ?>
<a href="../audit_pemeriksaan/create.php?program_id=<?= $r['id'] ?>"
class="btn btn-success btn-sm btn-blink-border">
Buat Audit
</a>
<?php elseif($r['status']=='SELESAI' && !empty($r['lha_file'])): ?>
<a href="../uploads/program_lha/<?= $r['lha_file'] ?>"
target="_blank" class="btn btn-success btn-sm">
<i class="fas fa-file-pdf"></i> Lihat LHA
</a>
<?php elseif($r['status']=='SELESAI'): ?>
<a href="upload_lha.php?id=<?= $r['id'] ?>"
class="btn btn-info btn-sm">
<i class="fas fa-upload"></i> Upload LHA
</a>
<?php else: ?>
<span class="text-muted small">-</span>
<?php endif; ?>
</td>


</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>


<div class="mt-3 d-flex justify-content-between align-items-center">
<small class="text-muted">Total: <strong><?= $totalRecords ?></strong></small>
<nav>
<ul class="pagination pagination-sm mb-0">
<li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $page - 1 ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>">Sebelumnya</a>
</li>
<?php for ($i = 1; $i <= $totalPages; $i++): ?>
<li class="page-item <?= $i == $page ? 'active' : '' ?>">
<a class="page-link" href="?page=<?= $i ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>"><?= $i ?></a>
</li>
<?php endfor; ?>
<li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
<a class="page-link" href="?page=<?= $page + 1 ?><?= $keyword ? '&keyword=' . urlencode($keyword) : '' ?>">Selanjutnya</a>
</li>
</ul>
</nav>
</div>


</div>

</div>

</div>

</main>

<script>

function hapusProgram(id)
{
    Swal.fire({
        title:'Hapus Program Audit?',
        text:'Data tidak dapat dikembalikan',
        icon:'warning',
        showCancelButton:true,
        confirmButtonText:'Ya, Hapus',
        cancelButtonText:'Batal'
    })
    .then((result)=>{

        if(result.isConfirmed)
        {
            window.location=
            'delete.php?id='+id;
        }

    });
}

</script>

<?php
include "../templates/footer.php";
?>
