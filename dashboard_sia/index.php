<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR', 'DIREKSI', 'KOMISARIS', 'KOMITE_AUDIT']);

$isDireksi = in_array($_SESSION['role'], ['DIREKSI', 'KOMISARIS', 'KOMITE_AUDIT']);

function kpiCard($isDireksi, $href, $bg, $border)
{
    $style = 'background:' . $bg . ';border:2px solid ' . $border . ';';
    if($isDireksi || !$href)
    {
        return ['open' => '<div class="card kpi-card shadow" style="' . $style . '">', 'close' => '</div>'];
    }
    return ['open' => '<a href="' . $href . '" class="card kpi-card shadow" style="' . $style . '">', 'close' => '</a>'];
}

$filterUnit   = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$filterTahun  = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$wProgram = " WHERE 1=1";
if ($filterUnit > 0)  { $wProgram .= " AND unit_id=$filterUnit"; }
if ($filterTahun > 0) { $wProgram .= " AND tahun=$filterTahun"; }

$wPemeriksaan = " WHERE 1=1";
if ($filterUnit > 0)  { $wPemeriksaan .= " AND unit_id=$filterUnit"; }
if ($filterTahun > 0) { $wPemeriksaan .= " AND tahun_audit=$filterTahun"; }

$wTemuan = " WHERE 1=1";
if ($filterUnit > 0)  { $wTemuan .= " AND p.unit_id=$filterUnit"; }
if ($filterTahun > 0) { $wTemuan .= " AND p.tahun_audit=$filterTahun"; }

$wRekomendasi = " WHERE 1=1";
if ($filterUnit > 0)  { $wRekomendasi .= " AND p.unit_id=$filterUnit"; }
if ($filterTahun > 0) { $wRekomendasi .= " AND p.tahun_audit=$filterTahun"; }

$wTL = " WHERE 1=1";
if ($filterUnit > 0)  { $wTL .= " AND tl.unit_id=$filterUnit"; }
if ($filterTahun > 0) { $wTL .= " AND p.tahun_audit=$filterTahun"; }

$totalProgram     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_program $wProgram"))[0];
$totalPemeriksaan = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_pemeriksaan $wPemeriksaan"))[0];
$totalTemuan      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_temuan t JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTemuan"))[0];
$totalRekomendasi = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_rekomendasi r JOIN audit_temuan t ON r.temuan_id=t.id JOIN audit_pemeriksaan p ON t.audit_id=p.id $wRekomendasi"))[0];
$totalTL          = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id LEFT JOIN audit_temuan t ON r.temuan_id=t.id LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTL"))[0];

$proses             = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id LEFT JOIN audit_temuan t ON r.temuan_id=t.id LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTL AND tl.status='Proses'"))[0];
$sesuai             = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id LEFT JOIN audit_temuan t ON r.temuan_id=t.id LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTL AND tl.status='Sesuai'"))[0];
$belumSesuai        = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id LEFT JOIN audit_temuan t ON r.temuan_id=t.id LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTL AND tl.status='Belum Sesuai'"))[0];
$belumDitindakLanjut= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id LEFT JOIN audit_temuan t ON r.temuan_id=t.id LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTL AND tl.status='Belum Ditindak Lanjut'"))[0];
$tidakDapat         = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM audit_tindak_lanjut tl LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id LEFT JOIN audit_temuan t ON r.temuan_id=t.id LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id $wTL AND tl.status='Tidak Dapat Ditindak Lanjut'"))[0];

$qUnitOptions   = mysqli_query($conn, "SELECT id, nama_unit FROM unit_kerja WHERE aktif=1 ORDER BY nama_unit");
$qTahunOptions  = mysqli_query($conn, "SELECT DISTINCT tahun AS th FROM audit_program WHERE tahun IS NOT NULL UNION SELECT DISTINCT tahun_audit FROM audit_pemeriksaan WHERE tahun_audit IS NOT NULL UNION SELECT DISTINCT tahun FROM lhp WHERE tahun IS NOT NULL ORDER BY th DESC");

$lhpSumber = ['KAP', 'BPK', 'BPKP'];
$lhpSumberBadge = ['BPK' => 'is-info', 'BPKP' => 'is-warn', 'KAP' => 'is-neutral'];
$lhpSumberLabel = ['KAP' => 'Kantor Akuntan Publik', 'BPK' => 'Badan Pemeriksa Keuangan', 'BPKP' => 'Badan Pengawasan Keuangan dan Pembangunan'];
$lhpSumberCard = [
    'KAP'  => ['icon' => 'fa-landmark',   'soft' => '#F1F4F8', 'strong' => '#46526A'],
    'BPK'  => ['icon' => 'fa-shield-alt', 'soft' => '#E8F2FC', 'strong' => '#063F7A'],
    'BPKP' => ['icon' => 'fa-user-tie',   'soft' => '#FFF6D8', 'strong' => '#A66A00'],
];
$totalLhp = [];
$totalTlLhp = [];
$lhpStatusList = ['Proses', 'Sesuai', 'Belum Sesuai', 'Belum Ditindak Lanjut', 'Tidak Dapat Ditindak Lanjut'];
$lhpStatusCounts = [
    'KAP'  => ['Proses' => 0, 'Sesuai' => 0, 'Belum Sesuai' => 0, 'Belum Ditindak Lanjut' => 0, 'Tidak Dapat Ditindak Lanjut' => 0],
    'BPK'  => ['Proses' => 0, 'Sesuai' => 0, 'Belum Sesuai' => 0, 'Belum Ditindak Lanjut' => 0, 'Tidak Dapat Ditindak Lanjut' => 0],
    'BPKP' => ['Proses' => 0, 'Sesuai' => 0, 'Belum Sesuai' => 0, 'Belum Ditindak Lanjut' => 0, 'Tidak Dapat Ditindak Lanjut' => 0],
];
$wUnitTahun = '';
if ($filterUnit > 0)  { $wUnitTahun .= " AND EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$filterUnit)"; }
if ($filterTahun > 0) { $wUnitTahun .= " AND l.tahun=$filterTahun"; }
$qLhpCnt = mysqli_query($conn, "SELECT l.sumber AS src, t.status AS st, COUNT(*) AS jml
    FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id JOIN lhp l ON r.lhp_id=l.id
    WHERE 1=1 $wUnitTahun GROUP BY l.sumber, t.status");
while ($lc = mysqli_fetch_assoc($qLhpCnt)) {
    if (isset($lhpStatusCounts[$lc['src']][$lc['st']])) {
        $lhpStatusCounts[$lc['src']][$lc['st']] = (int)$lc['jml'];
    }
}
foreach ($lhpSumber as $src) {
    $lhpWhere = " WHERE l.sumber='$src'$wUnitTahun";
    $totalLhp[$src] = (int)mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM lhp l $lhpWhere"))[0];
    $totalTlLhp[$src] = array_sum($lhpStatusCounts[$src]);
}

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
    LEFT JOIN audit_pemeriksaan p ON t.audit_id=p.id
    WHERE tl.status IN ('Proses','Belum Ditindak Lanjut')
    " . ($filterUnit > 0 ? " AND tl.unit_id=$filterUnit" : '') . "
    " . ($filterTahun > 0 ? " AND p.tahun_audit=$filterTahun" : '') . "
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
        <h1 class="jxb-page-title"><i class="fas fa-chart-line me-2 text-primary"></i>Dashboard Monitoring</h1>
        <div class="jxb-page-subtitle">Ringkasan audit dan tindak lanjut satuan internal audit</div>
    </div>
</div>

<!-- Filter Unit + Tahun -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-4 col-lg-3">
                <label class="form-label small mb-1 fw-semibold"><i class="fas fa-building me-1"></i>Unit Kerja</label>
                <select name="unit" class="form-select">
                    <option value="">Semua Unit</option>
                    <?php while ($u = mysqli_fetch_assoc($qUnitOptions)): ?>
                        <option value="<?= $u['id'] ?>" <?= $filterUnit === (int)$u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nama_unit']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
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
        <?php if ($filterUnit > 0 || $filterTahun > 0): ?>
        <div class="small text-muted mt-2"><i class="fas fa-info-circle me-1"></i>Menampilkan data untuk
            <?= $filterUnit > 0 ? 'unit terpilih' : 'semua unit'; ?> &mdash;
            <?= $filterTahun > 0 ? 'TA ' . $filterTahun : 'semua tahun'; ?>
        </div>
        <?php endif; ?>
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
                <?php if(count($perluTindakan) > 0 && !$isDireksi): ?>
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

<!-- Data Tindak Lanjut LHP -->
<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-tasks me-2 text-primary"></i>Data Tindak Lanjut LHP <?= $filterTahun > 0 ? 'TA ' . $filterTahun : '' ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($lhpSumber as $src):
                        $sb = $lhpSumberBadge[$src];
                        $sc = $lhpSumberCard[$src];
                    ?>
                    <div class="col-12 col-md-4">
                        <a href="javascript:void(0)" class="card shadow-sm text-decoration-none status-card lhp-card is-sumber-<?= strtolower($src) ?>"
                            data-sumber="<?= $src ?>" onclick="toggleLhp(this)">
                            <div class="card-body d-flex align-items-center gap-3">
                                <span class="status-icon" style="background:<?= $sc['soft'] ?>;color:<?= $sc['strong'] ?>;"><i class="fas <?= $sc['icon'] ?>"></i></span>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="status-count mb-0"><?= $totalTlLhp[$src] ?></span>
                                        <span class="jxb-status-badge <?= $sb ?>"><?= $src ?></span>
                                    </div>
                                    <div class="status-name mt-1"><?= $lhpSumberLabel[$src] ?></div>
                                    <div class="small text-muted mt-1"><?= $totalLhp[$src] ?> LHP &middot; tindak lanjut</div>
                                </div>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-muted small mt-3"><i class="fas fa-info-circle me-1"></i>Klik sumber, lalu pilih status untuk menampilkan daftar tindak lanjut di bawah.</div>
                <div id="lhpResult" class="mt-3 d-none">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row g-3" id="lhpStatusRow">
                                <?php foreach ($lhpStatusList as $st):
                                    $stc = $statusConf[$st];
                                ?>
                                <div class="col-6 col-md">
                                    <a href="javascript:void(0)" class="card shadow-sm text-decoration-none status-card lhp-status-card <?= $statusCss[$st] ?>"
                                        data-lhp-status="<?= $st ?>" onclick="selectLhpStatus('<?= $st ?>')">
                                        <div class="card-body d-flex align-items-center gap-2 py-3">
                                            <span class="status-icon" style="background:<?= $stc['soft'] ?>;color:<?= $stc['strong'] ?>;"><i class="fas <?= $stc['icon'] ?>"></i></span>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="status-count mb-0" id="lhpStatusCnt_<?= str_replace(' ', '_', $st) ?>">0</div>
                                                <div class="status-name"><?= $st ?></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-body p-0 border-top" id="lhpBody"></div>
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
        var cards = document.querySelectorAll('.status-card[data-status]');
        var card = null;
        cards.forEach(function(c){ if(c.getAttribute('data-status') === status){ card = c; } });

        if(card && card.classList.contains('active')){
            card.classList.remove('active');
            container.classList.add('d-none');
            return;
        }
        cards.forEach(function(c){ c.classList.remove('active'); });
        if(card){ card.classList.add('active'); }

        var filterUnit  = <?= $filterUnit ?>;
        var filterTahun = <?= $filterTahun ?>;
        var qs = 'status=' + encodeURIComponent(status);
        if(filterUnit > 0){ qs += '&unit=' + filterUnit; }
        if(filterTahun > 0){ qs += '&tahun=' + filterTahun; }

        body.innerHTML = '<div class="p-4 text-center text-muted small"><i class="fas fa-spinner fa-spin me-1"></i>Memuat data...</div>';
        container.classList.remove('d-none');
        container.scrollIntoView({behavior:'smooth', block:'nearest'});

        fetch('sia_status_ajax.php?' + qs)
            .then(function(r){ if(!r.ok){ throw new Error('HTTP ' + r.status); } return r.text(); })
            .then(function(html){ body.innerHTML = html; })
            .catch(function(){
                body.innerHTML = '<div class="p-4 text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Gagal memuat data tindak lanjut.</div>';
            });
    };
    window.toggleStatus = function(card){
        openStatusList(card.getAttribute('data-status'));
    };

    window.LHP_STATUS_COUNTS = <?= json_encode($lhpStatusCounts) ?>;
    window.CURRENT_LHP_SRC = '';

    window.openLhpList = function(sumber){
        var container = document.getElementById('lhpResult');
        var body = document.getElementById('lhpBody');
        var cards = document.querySelectorAll('.lhp-card');
        var card = null;
        cards.forEach(function(c){ if(c.getAttribute('data-sumber') === sumber){ card = c; } });

        if(card && card.classList.contains('active')){
            card.classList.remove('active');
            container.classList.add('d-none');
            body.innerHTML = '';
            return;
        }
        cards.forEach(function(c){ c.classList.remove('active'); });
        if(card){ card.classList.add('active'); }
        window.CURRENT_LHP_SRC = sumber;

        container.classList.remove('d-none');
        container.scrollIntoView({behavior:'smooth', block:'nearest'});

        updateLhpStatusCounts(sumber);
        clearLhpStatusSelection();
        body.innerHTML = '<div class="p-4 text-center text-muted small"><i class="fas fa-info-circle me-1"></i>Pilih status di atas untuk menampilkan daftar tindak lanjut.</div>';
    };
    window.updateLhpStatusCounts = function(sumber){
        var counts = (window.LHP_STATUS_COUNTS && window.LHP_STATUS_COUNTS[sumber]) || {};
        document.querySelectorAll('.lhp-status-card').forEach(function(c){
            var st = c.getAttribute('data-lhp-status');
            var el = c.querySelector('.status-count');
            if(el){ el.textContent = counts[st] || 0; }
        });
    };
    window.clearLhpStatusSelection = function(){
        document.querySelectorAll('.lhp-status-card').forEach(function(c){ c.classList.remove('active'); });
    };
    window.toggleLhp = function(card){
        openLhpList(card.getAttribute('data-sumber'));
    };
    window.selectLhpStatus = function(status){
        clearLhpStatusSelection();
        var cards = document.querySelectorAll('.lhp-status-card');
        cards.forEach(function(c){ if(c.getAttribute('data-lhp-status') === status){ c.classList.add('active'); } });
        loadLhp(window.CURRENT_LHP_SRC, status, 1);
    };
    window.loadLhp = function(sumber, status, page){
        var container = document.getElementById('lhpResult');
        var body = document.getElementById('lhpBody');
        var filterUnit  = <?= $filterUnit ?>;
        var filterTahun = <?= $filterTahun ?>;
        var qs = 'sumber=' + encodeURIComponent(sumber) + '&status=' + encodeURIComponent(status) + '&page=' + (Math.floor(page) || 1);
        if(filterUnit > 0){ qs += '&unit=' + filterUnit; }
        if(filterTahun > 0){ qs += '&tahun=' + filterTahun; }

        body.innerHTML = '<div class="p-4 text-center text-muted small"><i class="fas fa-spinner fa-spin me-1"></i>Memuat data...</div>';
        container.classList.remove('d-none');

        fetch('lhp_ajax.php?' + qs)
            .then(function(r){ if(!r.ok){ throw new Error('HTTP ' + r.status); } return r.text(); })
            .then(function(html){ body.innerHTML = html; })
            .catch(function(){
                body.innerHTML = '<div class="p-4 text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Gagal memuat data tindak lanjut.</div>';
            });
        return false;
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