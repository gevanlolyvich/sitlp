<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR', 'DIREKSI', 'KOMISARIS']);

$isDireksi = in_array($_SESSION['role'], ['DIREKSI', 'KOMISARIS']);

function kpiCard($isDireksi, $href, $bg, $border)
{
    $style = 'background:' . $bg . ';border:2px solid ' . $border . ';';
    if($isDireksi || !$href)
    {
        return ['open' => '<div class="card kpi-card shadow" style="' . $style . '">', 'close' => '</div>'];
    }
    return ['open' => '<a href="' . $href . '" class="card kpi-card shadow" style="' . $style . '">', 'close' => '</a>'];
}

$totalProgram     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_program"))[0];
$totalPemeriksaan = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_pemeriksaan"))[0];
$totalTemuan      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_temuan"))[0];
$totalRekomendasi = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_rekomendasi"))[0];
$totalTL          = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut"))[0];

$proses             = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Proses'"))[0];
$sesuai             = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Sesuai'"))[0];
$belumSesuai        = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Belum Sesuai'"))[0];
$belumDitindakLanjut= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Belum Ditindak Lanjut'"))[0];
$tidakDapat         = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut WHERE status='Tidak Dapat Ditindak Lanjut'"))[0];

$statusCounts = [
    'Proses'                    => $proses,
    'Sesuai'                    => $sesuai,
    'Belum Sesuai'              => $belumSesuai,
    'Belum Ditindak Lanjut'     => $belumDitindakLanjut,
    'Tidak Dapat Ditindak Lanjut' => $tidakDapat,
];

$statusConf = [
    'Proses'                    => ['icon' => 'fa-hourglass-half', 'soft' => '#E8F2FC', 'strong' => '#063F7A'],
    'Sesuai'                    => ['icon' => 'fa-check-circle',   'soft' => '#E8F7EE', 'strong' => '#18794E'],
    'Belum Sesuai'              => ['icon' => 'fa-times-circle',   'soft' => '#FDECEC', 'strong' => '#C53030'],
    'Belum Ditindak Lanjut'     => ['icon' => 'fa-minus-circle',   'soft' => '#FFF6D8', 'strong' => '#A66A00'],
    'Tidak Dapat Ditindak Lanjut' => ['icon' => 'fa-ban',          'soft' => '#F1F4F8', 'strong' => '#46526A'],
];

$statusCss = [
    'Proses'                      => 'is-proses',
    'Sesuai'                      => 'is-sesuai',
    'Belum Sesuai'                => 'is-belum-sesuai',
    'Belum Ditindak Lanjut'       => 'is-belum-ditindak-lanjut',
    'Tidak Dapat Ditindak Lanjut' => 'is-tidak-dapat',
];

$today      = date('Y-m-d');
$qPerlu     = mysqli_query($conn, "SELECT tl.id, tl.status, tl.target_selesai, tl.pic, tl.uraian_tindak_lanjut,
    u.nama_unit, r.prioritas, r.nomor_rekomendasi, t.judul_temuan, t.nomor_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    WHERE tl.status IN ('Proses','Belum Ditindak Lanjut')
    ORDER BY (tl.target_selesai IS NULL), tl.target_selesai ASC, tl.id DESC
    LIMIT 8");
$perluTindakan = [];
while ($row = mysqli_fetch_assoc($qPerlu)) {
    $perluTindakan[] = $row;
}

function prioritasBadge($p)
{
    $map = [
        'Tinggi'  => 'is-danger',
        'Sedang'  => 'is-warn',
        'Rendah'  => 'is-neutral',
    ];
    return $map[$p] ?? '';
}

function statusBadgeClass($s)
{
    $map = [
        'Proses'                      => 'is-info',
        'Sesuai'                      => 'is-success',
        'Belum Sesuai'                => 'is-danger',
        'Belum Ditindak Lanjut'       => 'is-warn',
        'Tidak Dapat Ditindak Lanjut' => 'is-neutral',
    ];
    return $map[$s] ?? 'is-neutral';
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">

<div class="jxb-page-header">
    <div>
        <h1 class="jxb-page-title"><i class="fas fa-chart-line me-2 text-primary"></i>Dashboard Monitoring SIA</h1>
        <div class="jxb-page-subtitle">Ringkasan audit dan tindak lanjut satuan internal audit</div>
    </div>
</div>

<!-- Kartu KPI -->
<?php $kpiProgram   = kpiCard($isDireksi, '../audit_program/', '#ffffff', '#075AA8');
      $kpiPemeriksaan = kpiCard($isDireksi, '../audit_pemeriksaan/', '#ffffff', '#0891B2');
      $kpiTemuan    = kpiCard($isDireksi, null, '#ffffff', '#C53030');
      $kpiRekomendasi = kpiCard($isDireksi, null, '#ffffff', '#A66A00');
      $kpiTL        = kpiCard($isDireksi, '../audit_tindak_lanjut/', '#ffffff', '#18794E'); ?>
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiProgram['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-calendar-alt"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $totalProgram ?></h2>
                    <div class="kpi-label">Program Audit</div>
                </div>
            </div>
        <?= $kpiProgram['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiPemeriksaan['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-clipboard-check"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $totalPemeriksaan ?></h2>
                    <div class="kpi-label">Pemeriksaan Audit</div>
                </div>
            </div>
        <?= $kpiPemeriksaan['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiTemuan['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-search"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $totalTemuan ?></h2>
                    <div class="kpi-label">Temuan Audit</div>
                </div>
            </div>
        <?= $kpiTemuan['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiRekomendasi['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-lightbulb"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $totalRekomendasi ?></h2>
                    <div class="kpi-label">Rekomendasi</div>
                </div>
            </div>
        <?= $kpiRekomendasi['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <?= $kpiTL['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-tasks"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $totalTL ?></h2>
                    <div class="kpi-label">Tindak Lanjut</div>
                </div>
            </div>
        <?= $kpiTL['close'] ?>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Status Tindak Lanjut -->
    <div class="col-12">
    <div class="card shadow-sm">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-tasks me-2 text-primary"></i>Status Tindak Lanjut</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach($statusCounts as $sts => $cnt):
                    $soft = $statusConf[$sts]['soft'];
                    $strong = $statusConf[$sts]['strong'];
                    $icon = $statusConf[$sts]['icon'];
                ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg">
                    <a href="javascript:void(0)" class="card shadow-sm text-decoration-none status-card <?= $statusCss[$sts] ?? '' ?>"
                        data-status="<?= htmlspecialchars($sts, ENT_QUOTES) ?>" onclick="toggleStatus(this)">
                        <div class="card-body d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center gap-3">
                                <span class="status-icon" style="background:<?= $soft ?>;color:<?= $strong ?>;"><i class="fas <?= $icon ?>"></i></span>
                                <div>
                                    <div class="status-count mb-1"><?= $cnt ?></div>
                                    <div class="status-name"><?= htmlspecialchars($sts) ?></div>
                                </div>
                            </div>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-muted small mt-3"><i class="fas fa-info-circle me-1"></i>Klik status untuk menampilkan daftar tindak lanjut di bawah.</div>
            <div id="statusResult" class="mt-3 d-none">
                <div class="card shadow-sm border-0 overflow-hidden">
                    <div class="card-body p-0" id="statusBody"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Baris kedua: Perlu tindakan + Distribusi status -->
<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-exclamation-circle me-2 text-primary"></i>Perlu Tindakan</span>
                <?php if(count($perluTindakan) > 0): ?>
                <a href="../audit_tindak_lanjut/" class="btn btn-sm btn-link float-end p-0">Lihat semua</a>
                <?php endif; ?>
            </div>
            <div class="card-body py-2">
                <?php if(count($perluTindakan) === 0): ?>
                <div class="jxb-empty">
                    <i class="fas fa-check-circle"></i>
                    <div class="jxb-empty-title mt-1">Tidak ada tindak lanjut yang menunggu</div>
                    <div>Seluruh rekomendasi telah ditindaklanjuti.</div>
                </div>
                <?php else: foreach($perluTindakan as $pt):
                    $overdue = ($pt['target_selesai'] && $pt['target_selesai'] < $today);
                    $pb = prioritasBadge($pt['prioritas']);
                    $judul = $pt['judul_temuan'] ?: ('Rekomendasi no. ' . $pt['nomor_rekomendasi']);
                ?>
                <div class="jxb-list-item">
                    <div class="min-w-0">
                        <a href="../audit_tindak_lanjut/detail.php?id=<?= (int)$pt['id'] ?>" class="fw-semibold text-decoration-none text-reset"><?= htmlspecialchars(mb_substr($judul, 0, 90)) ?></a>
                        <div class="text-muted small mt-1">
                            <?= htmlspecialchars($pt['nama_unit'] ?? '-') ?>
                            <?php if(!empty($pt['pic'])): ?> &middot; PIC: <?= htmlspecialchars($pt['pic']) ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="d-flex align-items-center justify-content-end gap-2 mb-1">
                            <?php if($pb !== ''): ?>
                            <span class="jxb-status-badge <?= $pb ?>"><?= htmlspecialchars($pt['prioritas']) ?></span>
                            <?php endif; ?>
                            <span class="jxb-status-badge <?= statusBadgeClass($pt['status']) ?>"><?= htmlspecialchars($pt['status']) ?></span>
                        </div>
                        <div class="small <?= $overdue ? 'fw-semibold' : 'text-muted' ?>">
                            <?php if(!empty($pt['target_selesai'])):
                                $due = date('d M Y', strtotime($pt['target_selesai']));
                                if($overdue): ?>
                                <i class="fas fa-exclamation-triangle me-1 text-danger"></i><span class="text-danger"><?= $due ?></span>
                                <?php else: ?>
                                Target: <?= $due ?>
                                <?php endif;
                            else: ?>
                            Target: belum ditetapkan
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Distribusi Status</span>
            </div>
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-5">
                        <canvas id="distribusiChart" height="180" aria-label="Grafik distribusi status tindak lanjut" role="img"></canvas>
                    </div>
                    <div class="col-md-7">
                        <div class="jxb-legend">
                            <?php
                            $palette = ['#075AA8', '#18794E', '#C53030', '#F4B400', '#6E7B91'];
                            $labels  = array_keys($statusCounts);
                            $charts  = array_values($statusCounts);
                            foreach($labels as $i => $label):
                                $color = $palette[$i % count($palette)];
                            ?>
                            <div class="jxb-legend-item" data-status="<?= htmlspecialchars($label, ENT_QUOTES) ?>" onclick="openStatusList('<?= htmlspecialchars($label, ENT_QUOTES) ?>')">
                                <span class="jxb-legend-label"><span class="jxb-legend-dot" style="background:<?= $color ?>;"></span><?= htmlspecialchars($label) ?></span>
                                <span class="jxb-legend-value"><?= (int)$charts[$i] ?></span>
                            </div>
                            <?php endforeach; ?>
                            <div class="jxb-legend-item border-top pt-2">
                                <span class="jxb-legend-label fw-semibold">Total</span>
                                <span class="jxb-legend-value"><?= (int)$totalTL ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function(){
    window.openStatusList = function(status){
        var container = document.getElementById('statusResult');
        var body = document.getElementById('statusBody');
        var cards = document.querySelectorAll('.status-card');
        var card = null;
        cards.forEach(function(c){ if(c.getAttribute('data-status') === status){ card = c; } });

        if(card && card.classList.contains('active')){
            card.classList.remove('active');
            container.classList.add('d-none');
            return;
        }
        cards.forEach(function(c){ c.classList.remove('active'); });
        if(card){ card.classList.add('active'); }

        body.innerHTML = '<div class="p-4 text-center text-muted small"><i class="fas fa-spinner fa-spin me-1"></i>Memuat data...</div>';
        container.classList.remove('d-none');
        container.scrollIntoView({behavior:'smooth', block:'nearest'});

        fetch('sia_status_ajax.php?status=' + encodeURIComponent(status))
            .then(function(r){ if(!r.ok){ throw new Error('HTTP ' + r.status); } return r.text(); })
            .then(function(html){ body.innerHTML = html; })
            .catch(function(){
                body.innerHTML = '<div class="p-4 text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Gagal memuat data tindak lanjut.</div>';
            });
    };
    window.toggleStatus = function(card){
        openStatusList(card.getAttribute('data-status'));
    };

    var labels = <?= json_encode($labels) ?>;
    var data   = <?= json_encode(array_map('intval', $charts)) ?>;
    var colors = <?= json_encode($palette) ?>;
    var ctx = document.getElementById('distribusiChart');
    if(ctx && typeof Chart !== 'undefined'){
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                onClick: function(evt, elements){
                    if(elements && elements.length > 0){
                        openStatusList(labels[elements[0].index]);
                    }
                }
            }
        });
    }
});
</script>

<?php include "../templates/footer.php"; ?>