<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];

$unitRow = mysqli_fetch_row(mysqli_query($conn, "SELECT nama_unit FROM unit_kerja WHERE id=$unit_id"));
$namaUnit = $unitRow ? $unitRow[0] : null;

$filterTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$tahunSql = '';
if ($filterTahun > 0) { $tahunSql = " AND p.tahun_audit=$filterTahun"; }

$tlJoin = " FROM audit_tindak_lanjut tl
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id
    WHERE tl.unit_id=$unit_id";

$totalTL = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) $tlJoin$tahunSql"))[0];

$proses              = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) $tlJoin AND tl.status='Proses'$tahunSql"))[0];
$sesuai              = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) $tlJoin AND tl.status='Sesuai'$tahunSql"))[0];
$belumSesuai         = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) $tlJoin AND tl.status='Belum Sesuai'$tahunSql"))[0];
$belumDitindakLanjut = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) $tlJoin AND tl.status='Belum Ditindak Lanjut'$tahunSql"))[0];
$tidakDapat          = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) $tlJoin AND tl.status='Tidak Dapat Ditindak Lanjut'$tahunSql"))[0];

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

$qTahunOptions = mysqli_query($conn, "
    SELECT DISTINCT p.tahun_audit AS th
    FROM audit_tindak_lanjut tl
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id
    WHERE tl.unit_id=$unit_id
    ORDER BY p.tahun_audit DESC");

function kpiCard($href, $bg)
{
    if(!$href)
    {
        return ['open' => '<div class="card kpi-card shadow" style="background:' . $bg . ';">', 'close' => '</div>'];
    }
    return ['open' => '<a href="' . $href . '" class="card kpi-card shadow" style="background:' . $bg . ';">', 'close' => '</a>'];
}

$kpiTL     = kpiCard('../auditee_temuan/', '#ffffff');
$kpiProses = kpiCard(null, '#ffffff');
$kpiSesuai = kpiCard(null, '#ffffff');
$kpiBelum  = kpiCard(null, '#ffffff');
$kpiBlm    = kpiCard(null, '#ffffff');
$kpiTdk    = kpiCard(null, '#ffffff');

$today      = date('Y-m-d');
$qPerlu     = mysqli_query($conn, "SELECT tl.id, tl.status, tl.target_selesai, tl.pic, tl.uraian_tindak_lanjut,
    r.prioritas, r.nomor_rekomendasi, t.judul_temuan, t.nomor_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id
    WHERE tl.unit_id=$unit_id AND tl.status IN ('Proses','Belum Ditindak Lanjut') $tahunSql
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
        <h1 class="jxb-page-title"><i class="fas fa-chart-pie me-2 text-primary"></i>Dashboard Auditee</h1>
        <div class="jxb-page-subtitle">Monitoring tindak lanjut <?= htmlspecialchars($namaUnit ?? 'unit kerja Anda') ?><?= $filterTahun > 0 ? ' &mdash; TA ' . $filterTahun : '' ?></div>
    </div>
</div>

<!-- Filter Tahun -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small mb-1 fw-semibold"><i class="fas fa-calendar-alt me-1"></i>Tahun</label>
                <select name="tahun" class="form-select">
                    <option value="">Semua Tahun</option>
                    <?php while ($t = mysqli_fetch_assoc($qTahunOptions)): ?>
                        <option value="<?= $t['th'] ?>" <?= $filterTahun === (int)$t['th'] ? 'selected' : '' ?>><?= $t['th'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Terapkan Filter</button>
                <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-undo me-1"></i>Reset</a>
            </div>
        </form>
        <?php if ($filterTahun > 0): ?>
        <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Menampilkan data tindak lanjut untuk TA <?= $filterTahun ?> pada unit <?= htmlspecialchars($namaUnit ?? 'Anda') ?>.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Kartu KPI -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-4">
        <?= $kpiTL['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-tasks"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $totalTL ?></h2>
                    <div class="kpi-label">Tindak Lanjut Saya</div>
                </div>
            </div>
        <?= $kpiTL['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <?= $kpiProses['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-hourglass-half"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $proses ?></h2>
                    <div class="kpi-label">Proses</div>
                </div>
            </div>
        <?= $kpiProses['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <?= $kpiSesuai['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $sesuai ?></h2>
                    <div class="kpi-label">Sesuai</div>
                </div>
            </div>
        <?= $kpiSesuai['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <?= $kpiBelum['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-times-circle"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $belumSesuai ?></h2>
                    <div class="kpi-label">Belum Sesuai</div>
                </div>
            </div>
        <?= $kpiBelum['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <?= $kpiBlm['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-minus-circle"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $belumDitindakLanjut ?></h2>
                    <div class="kpi-label">Belum Ditindak Lanjut</div>
                </div>
            </div>
        <?= $kpiBlm['close'] ?>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <?= $kpiTdk['open'] ?>
            <div class="card-body d-flex align-items-center gap-3">
                <span class="kpi-icon"><i class="fas fa-ban"></i></span>
                <div>
                    <h2 class="kpi-value mb-1"><?= $tidakDapat ?></h2>
                    <div class="kpi-label">Tidak Dapat Ditindak Lanjut</div>
                </div>
            </div>
        <?= $kpiTdk['close'] ?>
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
                    <a href="javascript:void(0)" class="card shadow-sm text-decoration-none status-card"
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
                <a href="../auditee_temuan/" class="btn btn-sm btn-link float-end p-0">Lihat semua</a>
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
                            <?php if(!empty($pt['pic'])): ?>PIC: <?= htmlspecialchars($pt['pic']) ?><?php endif; ?>
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
                            <div class="jxb-legend-item">
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

        var tahun = <?= $filterTahun ?>;
        var qs = 'status=' + encodeURIComponent(status);
        if(tahun > 0){ qs += '&tahun=' + tahun; }

        fetch('../dashboard_sia/sia_status_ajax.php?' + qs)
            .then(function(r){ if(!r.ok){ throw new Error('HTTP ' + r.status); } return r.text(); })
            .then(function(html){ body.innerHTML = html; })
            .catch(function(){
                body.innerHTML = '<div class="p-4 text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Gagal memuat data tindak lanjut.</div>';
            });
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
                }
            }
        });
    }
});
</script>

<?php include "../templates/footer.php"; ?>