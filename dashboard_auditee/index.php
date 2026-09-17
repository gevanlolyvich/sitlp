<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];

$totalTL = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id"))[0];

$proses              = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Proses'"))[0];
$sesuai              = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Sesuai'"))[0];
$belumSesuai         = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Belum Sesuai'"))[0];
$belumDitindakLanjut = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Belum Ditindak Lanjut'"))[0];
$tidakDapat          = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE unit_id=$unit_id AND status='Tidak Dapat Ditindak Lanjut'"))[0];

$statusCounts = [
    'Proses'                    => $proses,
    'Sesuai'                    => $sesuai,
    'Belum Sesuai'              => $belumSesuai,
    'Belum Ditindak Lanjut'     => $belumDitindakLanjut,
    'Tidak Dapat Ditindak Lanjut' => $tidakDapat,
];

$statusConf = [
    'Proses'                    => ['icon' => 'fa-hourglass-half', 'color' => 'linear-gradient(135deg,#4facfe,#00f2fe)'],
    'Sesuai'                    => ['icon' => 'fa-check-circle',   'color' => 'linear-gradient(135deg,#43e97b,#38f9d7)'],
    'Belum Sesuai'              => ['icon' => 'fa-times-circle',   'color' => 'linear-gradient(135deg,#f83600,#f9d423)'],
    'Belum Ditindak Lanjut'     => ['icon' => 'fa-minus-circle',   'color' => 'linear-gradient(135deg,#667eea,#764ba2)'],
    'Tidak Dapat Ditindak Lanjut' => ['icon' => 'fa-ban',          'color' => 'linear-gradient(135deg,#868f96,#596164)'],
];

function kpiCard($href, $bg)
{
    if(!$href)
    {
        return ['open' => '<div class="card kpi-card shadow" style="background:' . $bg . ';">', 'close' => '</div>'];
    }
    return ['open' => '<a href="' . $href . '" class="card kpi-card shadow" style="background:' . $bg . ';">', 'close' => '</a>'];
}

$kpiTL     = kpiCard('../auditee_temuan/', 'linear-gradient(135deg,#4facfe,#00f2fe)');
$kpiProses = kpiCard(null, 'linear-gradient(135deg,#667eea,#764ba2)');
$kpiSesuai = kpiCard(null, 'linear-gradient(135deg,#43e97b,#38f9d7)');
$kpiBelum  = kpiCard(null, 'linear-gradient(135deg,#f83600,#f9d423)');
$kpiBlm    = kpiCard(null, 'linear-gradient(135deg,#2af598,#009efd)');
$kpiTdk    = kpiCard(null, 'linear-gradient(135deg,#868f96,#596164)');

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<style>
.kpi-card{
    display:block;
    border-radius:14px;
    color:#fff;
    text-decoration:none;
    transition:transform .2s ease, box-shadow .2s ease;
    position:relative;
    overflow:hidden;
    border:none;
}
.kpi-card:hover{ transform:translateY(-4px); box-shadow:0 12px 24px rgba(0,0,0,.15)!important; color:#fff; }
.kpi-card .kpi-icon{
    width:52px;height:52px;display:inline-flex;align-items:center;justify-content:center;
    border-radius:14px;background:rgba(255,255,255,.25);color:#fff;font-size:22px;
}
.status-card{ height:100%; border:none; cursor:pointer; transition:transform .2s ease, box-shadow .2s ease; }
.status-card:hover{ transform:translateY(-3px); box-shadow:0 10px 20px rgba(0,0,0,.12)!important; }
.status-card.active{ box-shadow:0 0 0 2px rgba(0,0,0,.15), 0 10px 20px rgba(0,0,0,.15)!important; }
.status-card .fa-chevron-down{ transition:transform .2s; }
.status-card.active .fa-chevron-down{ transform:rotate(180deg); }
.status-icon{
    width:46px;height:46px;display:inline-flex;align-items:center;justify-content:center;
    border-radius:12px;color:#fff;font-size:20px;flex-shrink:0;
}
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Dashboard Auditee</h4>
        <small class="text-muted">Monitoring tindak lanjut unit kerja Anda</small>
    </div>
</div>

<!-- Kartu KPI -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiTL['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-tasks"></i></span>
                <div>
                    <h2 class="fw-bold mb-0"><?= $totalTL ?></h2>
                    <small>Tindak Lanjut Saya</small>
                </div>
            </div>
        <?= $kpiTL['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiProses['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-hourglass-half"></i></span>
                <div>
                    <h2 class="fw-bold mb-0"><?= $proses ?></h2>
                    <small>Proses</small>
                </div>
            </div>
        <?= $kpiProses['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiSesuai['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                <div>
                    <h2 class="fw-bold mb-0"><?= $sesuai ?></h2>
                    <small>Sesuai</small>
                </div>
            </div>
        <?= $kpiSesuai['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiBelum['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-times-circle"></i></span>
                <div>
                    <h2 class="fw-bold mb-0"><?= $belumSesuai ?></h2>
                    <small>Belum Sesuai</small>
                </div>
            </div>
        <?= $kpiBelum['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiBlm['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-minus-circle"></i></span>
                <div>
                    <h2 class="fw-bold mb-0"><?= $belumDitindakLanjut ?></h2>
                    <small>Belum Ditindak Lanjut</small>
                </div>
            </div>
        <?= $kpiBlm['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiTdk['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-ban"></i></span>
                <div>
                    <h2 class="fw-bold mb-0"><?= $tidakDapat ?></h2>
                    <small>Tidak Dapat Ditindak Lanjut</small>
                </div>
            </div>
        <?= $kpiTdk['close'] ?>
    </div>
</div>

<!-- Status Tindak Lanjut -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h6 class="card-title mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Status Tindak Lanjut</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach($statusCounts as $sts => $cnt):
                $icon = $statusConf[$sts]['icon'];
                $color = $statusConf[$sts]['color'];
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <a href="javascript:void(0)" class="card shadow-sm text-decoration-none text-dark status-card"
                    data-status="<?= htmlspecialchars($sts, ENT_QUOTES) ?>" onclick="toggleStatus(this)">
                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="status-icon" style="background:<?= $color ?>;"><i class="fas <?= $icon ?>"></i></span>
                            <div>
                                <div class="fw-bold fs-4 lh-1 mb-1"><?= $cnt ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($sts) ?></div>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-muted small mt-3"><i class="fas fa-info-circle me-1"></i>Klik status untuk menampilkan daftar tindak lanjut di bawah.</div>
        <div id="statusResult" class="mt-3 d-none">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0" id="statusBody"></div>
            </div>
        </div>
    </div>
</div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function(){
    window.toggleStatus = function(card){
        var status = card.getAttribute('data-status');
        var container = document.getElementById('statusResult');
        var body = document.getElementById('statusBody');
        var cards = document.querySelectorAll('.status-card');

        if(card.classList.contains('active')){
            card.classList.remove('active');
            container.classList.add('d-none');
            return;
        }
        cards.forEach(function(c){ c.classList.remove('active'); });
        card.classList.add('active');

        body.innerHTML = '<div class="p-4 text-center text-muted small"><i class="fas fa-spinner fa-spin me-1"></i>Memuat data...</div>';
        container.classList.remove('d-none');
        container.scrollIntoView({behavior:'smooth', block:'nearest'});

        fetch('../dashboard_sia/sia_status_ajax.php?status=' + encodeURIComponent(status))
            .then(function(r){ if(!r.ok){ throw new Error('HTTP ' + r.status); } return r.text(); })
            .then(function(html){ body.innerHTML = html; })
            .catch(function(){
                body.innerHTML = '<div class="p-4 text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Gagal memuat data tindak lanjut.</div>';
            });
    };
});
</script>

<?php include "../templates/footer.php"; ?>