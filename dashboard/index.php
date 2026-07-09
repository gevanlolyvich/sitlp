<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$unitFilter = '';
$unitJoin = '';
if($_SESSION['role'] == 'AUDITEE' && !empty($_SESSION['unit_id'])){
    $unitId = (int)$_SESSION['unit_id'];
    $unitFilter = " WHERE unit_id=$unitId";
    $unitJoin = " JOIN audit_pemeriksaan a ON t.audit_id=a.id AND a.unit_id=$unitId";
}

$totalPAT = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml FROM audit_program $unitFilter"
    )
)['jml'];

$auditBerjalan = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_pemeriksaan
         WHERE status='BERJALAN' $unitFilter"
    )
)['jml'];

$auditSelesai = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_pemeriksaan
         WHERE status='SELESAI' $unitFilter"
    )
)['jml'];

$totalTemuan = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_temuan t $unitJoin"
    )
)['jml'];

$totalRekomendasi = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_rekomendasi r
         JOIN audit_temuan t ON r.temuan_id=t.id $unitJoin"
    )
)['jml'];

$tlOpen = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_tindak_lanjut
         WHERE status='OPEN' $unitFilter"
    )
)['jml'];

$tlProses = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_tindak_lanjut
         WHERE status='PROSES' $unitFilter"
    )
)['jml'];

$tlSelesai = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) jml
         FROM audit_tindak_lanjut
         WHERE status='SELESAI' $unitFilter"
    )
)['jml'];

$qUnit = mysqli_query(
    $conn,
    "
    SELECT
        uk.nama_unit,
        COUNT(*) jml
    FROM audit_temuan t
    JOIN audit_pemeriksaan a
        ON t.audit_id=a.id
    JOIN unit_kerja uk
        ON a.unit_id=uk.id
    " . ($_SESSION['role'] == 'AUDITEE' && !empty($_SESSION['unit_id'])
        ? "WHERE a.unit_id=" . (int)$_SESSION['unit_id']
        : "") . "
    GROUP BY uk.id
    "
);

$unit = [];
$jumlah = [];

while($r=mysqli_fetch_assoc($qUnit))
{
    $unit[] = $r['nama_unit'];
    $jumlah[] = $r['jml'];
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="row mb-3 mt-3">
                <div class="col-sm-6">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">Home</li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
            <div class="row">
             <div class="col-lg-3 col-6">
              <div class="small-box bg-primary">
               <div class="inner">
                <h3><?= $totalPAT ?></h3>
                <p>Total PAT</p>
               </div>
               <div class="icon">
                <i class="fas fa-calendar"></i>
               </div>
              </div>
             </div>
             <div class="col-lg-3 col-6">
              <div class="small-box bg-info">
               <div class="inner">
                <h3><?= $auditBerjalan ?></h3>
                <p>Audit Berjalan</p>
               </div>
               <div class="icon">
                <i class="fas fa-search"></i>
               </div>
              </div>
             </div>
             <div class="col-lg-3 col-6">
              <div class="small-box bg-success">
               <div class="inner">
                <h3><?= $auditSelesai ?></h3>
                <p>Audit Selesai</p>
               </div>
               <div class="icon">
                <i class="fas fa-check-circle"></i>
               </div>
              </div>
             </div>
             <div class="col-lg-3 col-6">
              <div class="small-box bg-danger">
               <div class="inner">
                <h3><?= $totalTemuan ?></h3>
                <p>Total Temuan</p>
               </div>
               <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
               </div>
              </div>
             </div>
            </div>

            <div class="row">
             <div class="col-md-6">
              <div class="card">
               <div class="card-header">
                <h3 class="card-title">Status Tindak Lanjut</h3>
               </div>
               <div class="card-body">
                <canvas id="chartTL"></canvas>
               </div>
              </div>
             </div>
             <div class="col-md-6">
              <div class="card">
               <div class="card-header">
                <h3 class="card-title">Temuan per Unit Kerja</h3>
               </div>
               <div class="card-body">
                <canvas id="chartTemuan"></canvas>
               </div>
              </div>
             </div>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function(){
new Chart(
document.getElementById('chartTL'),
{
    type:'doughnut',
    data:{
        labels:[
            'OPEN',
            'PROSES',
            'SELESAI'
        ],
        datasets:[{
            data:[
                <?= $tlOpen ?>,
                <?= $tlProses ?>,
                <?= $tlSelesai ?>
            ],
            backgroundColor:[
                '#0d6efd',
                '#ffc107',
                '#198754'
            ]
        }]
    }
});

new Chart(
document.getElementById('chartTemuan'),
{
    type:'bar',
    data:{
        labels:
            <?= json_encode($unit) ?>,

        datasets:[{
            label:'Jumlah Temuan',
            data:
                <?= json_encode($jumlah) ?>
        }]
    }
});

});
</script>

<?php
include "../templates/footer.php";
?>
