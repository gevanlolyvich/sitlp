<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];

$sumber = isset($_GET['sumber']) ? strtoupper(trim($_GET['sumber'])) : '';
if ($sumber !== '' && !in_array($sumber, ['BPK', 'BPKP', 'KAP'])) {
    $sumber = '';
}

$filterTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$w = " EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$unit_id)";
if ($sumber !== '') {
    $w .= " AND l.sumber='$sumber'";
}
if ($filterTahun > 0) {
    $w .= " AND l.tahun=$filterTahun";
}

$qTahun = mysqli_query($conn, "SELECT DISTINCT l.tahun AS th FROM lhp l
    WHERE EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$unit_id)
      AND l.tahun IS NOT NULL ORDER BY l.tahun DESC");

$sql = "SELECT t.id AS tl_id, t.status,
        l.id AS lhp_id, l.sumber, l.nomor_lhp, l.tahun, l.judul_temuan,
        r.no AS rek_no, r.uraian AS rek_uraian,
        t.no AS tl_no, t.uraian AS tl_uraian,
        (SELECT COUNT(*) FROM lhp_tl_bukti b WHERE b.tl_id=t.id) AS jml_bukti
        FROM lhp_tl t
        JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id
        JOIN lhp l ON r.lhp_id=l.id
        WHERE $w
        ORDER BY l.tahun DESC, l.id DESC, r.no, t.no";

$qData = mysqli_query($conn, $sql);

$badgeMap = [
    'Proses' => 'is-info',
    'Sesuai' => 'is-success',
    'Belum Sesuai' => 'is-danger',
    'Belum Ditindak Lanjut' => 'is-warn',
    'Tidak Dapat Ditindak Lanjut' => 'is-neutral',
];
$sumberBadge = ['BPK' => 'is-info', 'BPKP' => 'is-warn', 'KAP' => 'is-neutral'];

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-file-upload me-2 text-primary"></i>Bukti Tindak Lanjut LHP</h1>
                    <div class="jxb-page-subtitle">Daftar tindak lanjut LHP untuk unit kerja Anda &mdash; BPK, BPKP, dan KAP</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Daftar Tindak Lanjut LHP</h6>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-3 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Sumber</label>
                            <select name="sumber" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <?php foreach (['BPK', 'BPKP', 'KAP'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $sumber === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Tahun</label>
                            <select name="tahun" class="form-select form-select-sm">
                                <option value="">Semua</option>
                                <?php while ($th = mysqli_fetch_assoc($qTahun)): ?>
                                    <option value="<?= (int)$th['th'] ?>" <?= $filterTahun === (int)$th['th'] ? 'selected' : '' ?>><?= (int)$th['th'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive-wrapper">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Sumber / LHP</th>
                                    <th>Rekomendasi</th>
                                    <th>Tindak Lanjut</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($qData) === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="jxb-empty">
                                                <i class="fas fa-check-circle"></i>
                                                <div class="jxb-empty-title mt-1">Tidak ada tindak lanjut LHP</div>
                                                <div>Belum ada tindak lanjut LHP untuk unit Anda.</div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else:
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($qData)): ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td>
                                                <span class="jxb-status-badge <?= $sumberBadge[$row['sumber']] ?? 'is-neutral' ?>"><?= htmlspecialchars($row['sumber']) ?></span>
                                                <strong class="d-block mt-1"><?= htmlspecialchars($row['nomor_lhp']) ?></strong>
                                                <small><?= htmlspecialchars($row['judul_temuan']) ?></small>
                                                <br>
                                                <small class="text-muted">TA <?= (int)$row['tahun'] ?></small>
                                            </td>
                                            <td><strong>Rek. <?= (int)$row['rek_no'] ?>.</strong> <?= nl2br(htmlspecialchars($row['rek_uraian'])) ?></td>
                                            <td><strong>TL <?= (int)$row['tl_no'] ?>.</strong> <?= nl2br(htmlspecialchars($row['tl_uraian'])) ?></td>
                                            <td>
                                                <?php
                                                $sts = $row['status'];
                                                $bc = isset($badgeMap[$sts]) ? $badgeMap[$sts] : 'is-neutral';
                                                echo '<span class="jxb-status-badge ' . $bc . '">' . htmlspecialchars($sts) . '</span>';
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="jxb-status-badge <?= (int)$row['jml_bukti'] > 0 ? 'is-success' : 'is-warn' ?>">
                                                    <i class="fas fa-paperclip"></i> <?= (int)$row['jml_bukti'] ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="detail.php?id=<?= (int)$row['tl_id'] ?>" class="btn btn-primary btn-sm tb-icon btn-blink-border" title="Detail / Upload Bukti" aria-label="Detail"><i class="fas fa-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if (isset($_SESSION['success'])): ?>
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

<?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '<?= addslashes($_SESSION['error']) ?>'
        });
    </script>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php include "../templates/footer.php"; ?>