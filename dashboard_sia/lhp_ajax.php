<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR', 'DIREKSI', 'KOMISARIS', 'KOMITE_AUDIT', 'AUDITEE']);

$role = $_SESSION['role'];
$isDireksi = in_array($role, ['DIREKSI', 'KOMISARIS', 'KOMITE_AUDIT']);
$isAuditee = ($role === 'AUDITEE');
$hideNav    = $isDireksi || $isAuditee;

$sumber = isset($_GET['sumber']) ? strtoupper(trim($_GET['sumber'])) : '';
if (!in_array($sumber, ['BPK', 'BPKP', 'KAP'])) {
    http_response_code(400);
    echo '<div class="px-3 py-2 text-danger">Sumber tidak valid.</div>';
    exit;
}

$allowedStatus = ['Proses', 'Sesuai', 'Belum Sesuai', 'Belum Ditindak Lanjut', 'Tidak Dapat Ditindak Lanjut'];
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
if (!in_array($status, $allowedStatus)) {
    http_response_code(400);
    echo '<div class="px-3 py-2 text-danger">Status tidak valid.</div>';
    exit;
}
$statusBadge = [
    'Proses'                    => 'is-info',
    'Sesuai'                    => 'is-success',
    'Belum Sesuai'              => 'is-danger',
    'Belum Ditindak Lanjut'     => 'is-warn',
    'Tidak Dapat Ditindak Lanjut' => 'is-neutral',
];
$sumberBadge = ['BPK' => 'is-info', 'BPKP' => 'is-warn', 'KAP' => 'is-neutral'];

$filterUnit  = $isAuditee ? (int)$_SESSION['unit_id'] : (isset($_GET['unit']) ? (int)$_GET['unit'] : 0);
$filterTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;
$page        = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage     = 10;

$w = " WHERE l.sumber='$sumber' AND t.status='" . mysqli_real_escape_string($conn, $status) . "'";
if ($filterUnit > 0)  { $w .= " AND EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$filterUnit)"; }
if ($filterTahun > 0) { $w .= " AND l.tahun=$filterTahun"; }

$total = (int)mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id JOIN lhp l ON r.lhp_id=l.id $w"))[0];
$pages = max(1, (int)ceil($total / $perPage));
if ($page < 1) { $page = 1; }
if ($page > $pages) { $page = $pages; }
$off = ($page - 1) * $perPage;

$q = mysqli_query($conn, "SELECT t.id, t.status, l.id AS lhp_id, l.nomor_lhp, l.tahun, l.judul_temuan,
    r.no AS rek_no, r.uraian AS rek_uraian,
    t.no AS tl_no, t.uraian AS tl_uraian,
    (SELECT GROUP_CONCAT(uk.nama_unit ORDER BY uk.nama_unit SEPARATOR ', ') FROM lhp_unit lu JOIN unit_kerja uk ON lu.unit_id=uk.id WHERE lu.lhp_id=l.id) AS nama_unit
    FROM lhp_tl t
    JOIN lhp_rekomendasi r ON t.rekomendasi_id = r.id
    JOIN lhp l ON r.lhp_id = l.id
    $w
    ORDER BY l.tahun DESC, l.id DESC, r.no, t.no
    LIMIT $perPage OFFSET $off");
$rows = [];
while ($rw = mysqli_fetch_assoc($q)) {
    $rows[] = $rw;
}
?>

<div class="alert alert-light border py-2 mb-2 d-flex justify-content-between align-items-center">
    <span class="small fw-bold">
        <i class="fas fa-tasks me-1"></i>Daftar tindak lanjut LHP
        <span class="jxb-status-badge <?= $sumberBadge[$sumber] ?>"><?= htmlspecialchars($sumber) ?></span>
        &mdash; Status: <span class="jxb-status-badge <?= $statusBadge[$status] ?>"><?= htmlspecialchars($status) ?></span>
    </span>
    <span class="small text-muted">Total: <strong><?= $total ?></strong> TL</span>
</div>

<div class="table-responsive-wrapper">
<table class="table table-bordered table-hover align-middle mb-0 w-100 jxb-tl-table">
    <thead>
        <tr class="text-center align-middle">
            <th width="40">No</th>
            <th>Nomor LHP / Judul Temuan</th>
            <th style="min-width:200px;">Rekomendasi</th>
            <th style="min-width:220px;">Tindak Lanjut</th>
            <th style="min-width:160px;">Unit</th>
            <th width="100">Status</th>
            <?php if(!$hideNav): ?><th width="70" class="text-center">Aksi</th><?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (count($rows) > 0): $no = $off + 1; foreach ($rows as $r): ?>
        <tr>
            <td class="text-center"><?= $no++ ?></td>
            <td>
                <strong class="d-block"><?= htmlspecialchars($r['judul_temuan']) ?></strong>
                <small class="text-muted"><?= htmlspecialchars($r['nomor_lhp']) ?> &middot; TA <?= (int)$r['tahun'] ?></small>
            </td>
            <td class="small"><strong>Rek. <?= (int)$r['rek_no'] ?>.</strong> <span style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($r['rek_uraian'])) ?></span></td>
            <td><strong>TL <?= (int)$r['tl_no'] ?>.</strong> <span style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($r['tl_uraian'])) ?></span></td>
            <td><?= htmlspecialchars($r['nama_unit'] ?? '-') ?></td>
            <td class="text-center"><span class="jxb-status-badge <?= $statusBadge[$status] ?>"><?= htmlspecialchars($status) ?></span></td>
            <?php if(!$hideNav): ?>
            <td class="text-center"><a href="../tl_lhp/detail.php?id=<?= (int)$r['lhp_id'] ?>" class="btn btn-primary btn-sm tb-icon btn-blink-border" title="Detail" aria-label="Detail"><i class="fas fa-eye"></i></a></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; else: ?>
        <tr>
            <td colspan="<?= $hideNav ? 6 : 7 ?>" class="text-center py-4">
                <div class="jxb-empty"><i class="fas fa-tasks"></i><div class="jxb-empty-title mt-1">Belum ada tindak lanjut berstatus <?= htmlspecialchars($status) ?></div><div>Ubah filter atau status yang dipilih.</div></div>
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<div class="d-flex justify-content-between align-items-center mt-2 border-top pt-2 small">
    <span class="text-muted">Menampilkan <?= $total > 0 ? ($off + 1) . '&ndash;' . min($off + $perPage, $total) : '0' ?> dari <strong><?= $total ?></strong> TL <?= $sumber ?> (status <?= htmlspecialchars($status) ?>).</span>
    <div class="d-flex align-items-center gap-2">
        <?php if ($pages > 1): ?>
            <?php if ($page > 1): ?>
            <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary" onclick="return loadLhp('<?= $sumber ?>', '<?= htmlspecialchars($status, ENT_QUOTES) ?>', <?= $page - 1 ?>)">« Prev</a>
            <?php else: ?>
            <span class="btn btn-sm btn-outline-secondary disabled">« Prev</span>
            <?php endif; ?>
            <span class="text-muted">Halaman <strong><?= $page ?></strong> / <?= $pages ?></span>
            <?php if ($page < $pages): ?>
            <a href="javascript:void(0)" class="btn btn-sm btn-outline-primary" onclick="return loadLhp('<?= $sumber ?>', '<?= htmlspecialchars($status, ENT_QUOTES) ?>', <?= $page + 1 ?>)">Next »</a>
            <?php else: ?>
            <span class="btn btn-sm btn-outline-secondary disabled">Next »</span>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!$hideNav): ?>
        <a href="../tl_lhp/index.php?sumber=<?= $sumber ?><?= $filterUnit > 0 && !$isAuditee ? '&unit=' . $filterUnit : '' ?><?= $filterTahun > 0 ? '&tahun=' . $filterTahun : '' ?>" class="fw-bold text-primary text-decoration-none ms-2">Lihat semua <i class="fas fa-arrow-right"></i></a>
        <?php endif; ?>
    </div>
</div>

<?php exit; ?>