<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SPI', 'DIREKSI']);

function getCount($conn, $query)
{
    $q = mysqli_query($conn, $query);
    $d = mysqli_fetch_row($q);
    return $d[0];
}

$totalProgram = getCount($conn, "SELECT COUNT(*) FROM audit_program");
$totalPemeriksaan = getCount($conn, "SELECT COUNT(*) FROM audit_pemeriksaan");
$totalTemuan = getCount($conn, "SELECT COUNT(*) FROM audit_temuan");
$totalRekomendasi = getCount($conn, "SELECT COUNT(*) FROM audit_rekomendasi");
$totalTL = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut");
$open = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='OPEN'");
$proses = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='PROSES'");
$selesai = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='SELESAI'");
$belum = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE verifikasi_status='BELUM'");
$diterima = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE verifikasi_status='DITERIMA'");
$ditolak = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE verifikasi_status='DITOLAK'");
$overdue = getCount($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE target_selesai < CURDATE() AND status <> 'SELESAI'");
$progress = 0;

if ($totalTL > 0) {
    $progress = round(
        ($selesai / $totalTL) * 100
    );
}
;


$unitLabel = [];
$unitData = [];
$q = mysqli_query($conn, "SELECT u.nama_unit,COUNT(*) jumlah FROM audit_temuan t JOIN audit_pemeriksaan p ON t.audit_id=p.id JOIN unit_kerja u ON p.unit_id=u.id
	GROUP BY u.nama_unit ORDER BY jumlah DESC LIMIT 5");
while ($r = mysqli_fetch_assoc($q)) {
    $unitLabel[] = $r['nama_unit'];
    $unitData[] = $r['jumlah'];
}

$risikoRendah = getCount($conn, "SELECT COUNT(*) FROM audit_temuan WHERE tingkat_risiko='RENDAH'");
$risikoSedang = getCount($conn, "SELECT COUNT(*) FROM audit_temuan WHERE tingkat_risiko='SEDANG'");
$risikoTinggi = getCount($conn, "SELECT COUNT(*) FROM audit_temuan WHERE tingkat_risiko='TINGGI'");

$qOverdue = mysqli_query($conn, "
	SELECT tl.*, u.nama_unit,

	DATEDIFF(
	CURDATE(),
	tl.target_selesai
	) hari_terlambat

	FROM audit_tindak_lanjut tl

	LEFT JOIN unit_kerja u ON tl.unit_id=u.id

	WHERE
	tl.target_selesai < CURDATE()
	AND tl.status <> 'SELESAI'

	ORDER BY tl.target_selesai
	LIMIT 10");


include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">

            <!--
<h3 class="mb-3">
Dashboard Monitoring SPI
</h3>
-->

            <div class="alert alert-info"><i class="fas fa-calendar"></i>Data Monitoring Audit Tahun <?= date('Y') ?>
            </div>

            <div class="row">

                <?php

                $cards = [
                    ['Program Audit', $totalProgram, 'primary', 'fa-calendar'],
                    ['Pemeriksaan', $totalPemeriksaan, 'success', 'fa-clipboard-check'],
                    ['Temuan', $totalTemuan, 'warning', 'fa-search'],
                    ['Rekomendasi', $totalRekomendasi, 'info', 'fa-lightbulb'],
                    ['Tindak Lanjut', $totalTL, 'secondary', 'fa-tasks'],

                    ['OPEN', $open, 'danger', 'fa-folder-open'],
                    ['PROSES', $proses, 'warning', 'fa-spinner'],
                    ['SELESAI', $selesai, 'success', 'fa-check-circle'],

                    ['BELUM VERIF', $belum, 'secondary', 'fa-clock'],
                    ['DITERIMA', $diterima, 'success', 'fa-thumbs-up'],
                    ['DITOLAK', $ditolak, 'danger', 'fa-times-circle'],

                    ['OVERDUE', $overdue, 'cream', 'fa-exclamation-triangle']

                ];

                foreach ($cards as $card) {
                    ?>

                    <div class="col-md-3 mb-3">
                        <div class="small-box bg-<?= $card[2] ?>">
                            <div class="inner">
                                <h3><?= $card[1] ?></h3>
                                <p><?= $card[0] ?></p>
                            </div>
                            <div class="icon" style="padding-left: 5px; padding-bottom: 5px;">
                                <i class="fas <?= $card[3] ?>"></i>
                            </div>
                        </div>
                    </div>

                <?php } ?>
            </div>

            <!-- anank  -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Status Tindak Lanjut</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartStatus"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Verifikasi SPI</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartVerifikasi"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Temuan per Unit Kerja</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartUnit"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end -->


            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Progress Penyelesaian Tindak Lanjut</h5>
                        </div>
                        <div class="card-body">
                            <div class="progress" style="height:30px;">
                                <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                    style="width: <?= $progress ?>%;"> <?= $progress ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Risiko Temuan Audit</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="chartRisiko"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>
                                Tindak Lanjut Overdue
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-wrapper">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Unit Kerja</th>
                                            <th>PIC</th>
                                            <th>Target</th>
                                            <th>Hari Terlambat</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if (mysqli_num_rows($qOverdue) > 0) {
                                            while ($row = mysqli_fetch_assoc($qOverdue)) {
                                                ?>
                                                <tr>
                                                    <td><?= $row['nama_unit'] ?></td>
                                                    <td><?= $row['pic'] ?></td>
                                                    <td><?= $row['target_selesai'] ?></td>
                                                    <td><span class="badge bg-danger"><?= $row['hari_terlambat'] ?>hari</span>
                                                    </td>
                                                </tr>
                                            <?php }
                                        } else { ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada tindak lanjut overdue</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>

                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>
<?php include "../templates/footer.php"; ?>
<script>
    new Chart(
        document.getElementById('chartStatus'), {
        type: 'pie',
        data: {
            labels: ['OPEN', 'PROSES', 'SELESAI'],
            datasets: [{ data: [<?= $open ?>, <?= $proses ?>, <?= $selesai ?>] }]
        }
    });

    new Chart(
        document.getElementById('chartVerifikasi'), {
        type: 'pie',
        data: {
            labels: ['BELUM', 'DITERIMA', 'DITOLAK'],
            datasets: [{ data: [<?= $belum ?>, <?= $diterima ?>, <?= $ditolak ?>] }]
        }
    });

    new Chart(
        document.getElementById('chartUnit'), {
        type: 'bar',
        data: {
            labels:
                <?= json_encode($unitLabel) ?>,
            datasets: [{
                label: 'Jumlah Temuan',
                data:
                    <?= json_encode($unitData) ?>
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    new Chart(
        document.getElementById('chartRisiko'), {
        type: 'pie',
        data: {
            labels: ['RENDAH', 'SEDANG', 'TINGGI'],
            datasets: [{ data: [<?= $risikoRendah ?>, <?= $risikoSedang ?>, <?= $risikoTinggi ?>] }]
        }
    });

</script>