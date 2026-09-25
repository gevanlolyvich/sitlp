<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q = mysqli_query($conn, "SELECT l.*, u.nama AS nama_user FROM lhp l LEFT JOIN users u ON l.created_by=u.id WHERE l.id=$id");
$r = mysqli_fetch_assoc($q);
if (!$r) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$sumber = $r['sumber'];
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];
$submenuMap = ['BPK' => 'TL LHP BPK', 'BPKP' => 'TL LHP BPKP', 'KAP' => 'TL LHP KAP'];

$units = [];
$qU = mysqli_query($conn, "SELECT uk.id, uk.nama_unit FROM lhp_unit lu JOIN unit_kerja uk ON lu.unit_id=uk.id WHERE lu.lhp_id=$id ORDER BY uk.nama_unit");
while ($u = mysqli_fetch_assoc($qU)) {
    $units[] = $u['nama_unit'];
}

$rekomendasi = [];
$qRek = mysqli_query($conn, "SELECT * FROM lhp_rekomendasi WHERE lhp_id=$id ORDER BY no");
while ($rek = mysqli_fetch_assoc($qRek)) {
    $rekId = (int)$rek['id'];
    $tls = [];
    $qTl = mysqli_query($conn, "SELECT * FROM lhp_tl WHERE rekomendasi_id=$rekId ORDER BY no");
    while ($tl = mysqli_fetch_assoc($qTl)) {
        $tlId = (int)$tl['id'];
        $bukti = [];
        $qB = mysqli_query($conn, "SELECT file FROM lhp_tl_bukti WHERE tl_id=$tlId ORDER BY id");
        while ($b = mysqli_fetch_assoc($qB)) {
            $bukti[] = $b['file'];
        }
        $tls[] = ['no' => $tl['no'], 'uraian' => $tl['uraian'], 'status' => $tl['status'], 'bukti' => $bukti];
    }
    $rekomendasi[] = ['no' => $rek['no'], 'uraian' => $rek['uraian'], 'tl' => $tls];
}

$totalTl = 0;
$countStatus = ['Proses' => 0, 'Sesuai' => 0, 'Belum Sesuai' => 0, 'Belum Ditindak Lanjut' => 0, 'Tidak Dapat Ditindak Lanjut' => 0];
foreach ($rekomendasi as $rek) {
    $totalTl += count($rek['tl']);
    foreach ($rek['tl'] as $tl) {
        if (isset($countStatus[$tl['status']])) {
            $countStatus[$tl['status']]++;
        }
    }
}

$statusBadge = [
    'Proses' => 'is-info',
    'Sesuai' => 'is-success',
    'Belum Sesuai' => 'is-danger',
    'Belum Ditindak Lanjut' => 'is-warn',
    'Tidak Dapat Ditindak Lanjut' => 'is-neutral',
];

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-eye me-2 text-primary"></i>Detail <?= $submenuMap[$sumber] ?></h1>
                    <div class="jxb-page-subtitle">Rincian data pemantauan tindak lanjut <?= $judulMap[$sumber] ?></div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php?sumber=<?= $sumber ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-outline-warning"><i class="fas fa-edit"></i> Edit</a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <th width="200">Sumber / Tahun</th>
                                    <td><?= htmlspecialchars($sumber) ?> &middot; TA <?= (int)$r['tahun'] ?></td>
                                    <th width="200">Unit / Entitas yang Diperiksa</th>
                                    <td>
                                        <?php if ($units): ?>
                                            <?php foreach ($units as $nu): ?>
                                                <span class="jxb-status-badge is-neutral"><?= htmlspecialchars($nu) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nomor LHP</th>
                                    <td colspan="3"><?= htmlspecialchars($r['nomor_lhp']) ?></td>
                                </tr>
                                <tr>
                                    <th>Dibuat oleh</th>
                                    <td colspan="3"><?= htmlspecialchars($r['nama_user'] ?? '-') ?></td>
                                </tr>
                                <tr>
                                    <th>Judul Temuan</th>
                                    <td colspan="3"><?= htmlspecialchars($r['judul_temuan']) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="jxb-form-section mt-4"><i class="fas fa-list-check me-1"></i>Rekomendasi & Tindak Lanjut
                        <small class="text-muted">(<?= count($rekomendasi) ?> rekomendasi, <?= $totalTl ?> tindak lanjut)</small>
                    </h6>

                    <?php if (!$rekomendasi): ?>
                        <div class="jxb-empty"><i class="fas fa-list"></i><div class="jxb-empty-title mt-1">Belum ada rekomendasi</div></div>
                    <?php endif; ?>

                    <?php foreach ($rekomendasi as $ri => $rek): ?>
                        <div class="card border mb-3">
                            <div class="card-header py-2 bg-light">
                                <strong>Rekomendasi <?= (int)$rek['no'] ?></strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-3" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($rek['uraian'])) ?></p>
                                <?php if (!$rek['tl']): ?>
                                    <div class="text-muted small">Belum ada tindak lanjut.</div>
                                <?php else: ?>
                                    <div class="ps-2 border-start border-3">
                                        <?php foreach ($rek['tl'] as $tl): ?>
                                            <div class="mb-2">
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    <strong class="text-primary">TL <?= (int)$tl['no'] ?></strong>
                                                    <span class="jxb-status-badge <?= $statusBadge[$tl['status']] ?? 'is-neutral' ?>"><?= htmlspecialchars($tl['status']) ?></span>
                                                </div>
                                                <div class="mt-1" style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($tl['uraian'])) ?></div>
                                                <?php if ($tl['bukti']): ?>
                                                    <div class="mt-1">
                                                        <small class="text-muted d-block">Bukti:</small>
                                                        <?php foreach ($tl['bukti'] as $bf): ?>
                                                            <a href="<?= htmlspecialchars('/sisia/uploads/tl_lhp/' . $bf) ?>" target="_blank" class="btn btn-outline-success btn-sm mb-1"><i class="fas fa-paperclip"></i> <?= htmlspecialchars($bf) ?></a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <h6 class="jxb-form-section mt-4"><i class="fas fa-chart-pie me-1"></i>Hasil Pemantauan Tindak Lanjut</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md"><div class="border rounded p-3 text-center"><div class="text-muted small">Proses</div><strong><?= $countStatus['Proses'] ?></strong></div></div>
                        <div class="col-6 col-md"><div class="border rounded p-3 text-center"><div class="text-muted small">Sesuai</div><strong><?= $countStatus['Sesuai'] ?></strong></div></div>
                        <div class="col-6 col-md"><div class="border rounded p-3 text-center"><div class="text-muted small">Belum Sesuai</div><strong><?= $countStatus['Belum Sesuai'] ?></strong></div></div>
                        <div class="col-6 col-md"><div class="border rounded p-3 text-center"><div class="text-muted small">Belum Ditindak Lanjut</div><strong><?= $countStatus['Belum Ditindak Lanjut'] ?></strong></div></div>
                        <div class="col-6 col-md"><div class="border rounded p-3 text-center"><div class="text-muted small">Tidak Dapat Ditindak Lanjut</div><strong><?= $countStatus['Tidak Dapat Ditindak Lanjut'] ?></strong></div></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kesimpulan</label>
                        <div style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($r['kesimpulan'])) ?></div>
                    </div>
                    <div>
                        <label class="form-label">Nilai Penyerahan Aset / Penyetoran Uang ke Kas Negara/Daerah</label>
                        <div><?= $r['nilai'] !== null && $r['nilai'] !== '' ? 'Rp ' . number_format((float)$r['nilai'], 0, ',', '.') : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "../templates/footer.php"; ?>