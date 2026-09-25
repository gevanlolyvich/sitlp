<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

$sumber = isset($_GET['sumber']) ? strtoupper(trim($_GET['sumber'])) : 'BPK';
if (!in_array($sumber, ['BPK', 'BPKP', 'KAP'])) {
    $sumber = 'BPK';
}
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];
$submenuMap = ['BPK' => 'TL LHP BPK', 'BPKP' => 'TL LHP BPKP', 'KAP' => 'TL LHP KAP'];

$limit = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$where = "WHERE l.sumber='$sumber'";
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
if ($keyword !== '') {
    $where .= " AND (l.nomor_lhp LIKE '%$keyword%' OR l.judul_temuan LIKE '%$keyword%'
        OR EXISTS(SELECT 1 FROM lhp_rekomendasi r WHERE r.lhp_id=l.id AND r.uraian LIKE '%$keyword%')
        OR EXISTS(SELECT 1 FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.uraian LIKE '%$keyword%'))";
}
$filterUnit = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
if ($filterUnit > 0) {
    $where .= " AND EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$filterUnit)";
}
$qUnitFilter = mysqli_query($conn, "SELECT id, nama_unit FROM unit_kerja WHERE aktif=1 ORDER BY nama_unit");

$countResult = mysqli_query($conn, "SELECT COUNT(*) total FROM lhp l $where");
$totalRecords = mysqli_fetch_assoc($countResult)['total'];
$totalPages = max(1, (int)ceil($totalRecords / $limit));

$q = mysqli_query($conn, "SELECT l.*,
    (SELECT COUNT(*) FROM lhp_rekomendasi r WHERE r.lhp_id=l.id) AS jml_rekomendasi,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id) AS jml_tl,
    (SELECT GROUP_CONCAT(uk.nama_unit ORDER BY uk.nama_unit SEPARATOR ', ') FROM lhp_unit lu JOIN unit_kerja uk ON lu.unit_id=uk.id WHERE lu.lhp_id=l.id) AS nama_unit,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Proses') AS hasil_proses,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Sesuai') AS hasil_sesuai,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Belum Sesuai') AS hasil_belum_sesuai,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Belum Ditindak Lanjut') AS hasil_belum_tl,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Tidak Dapat Ditindak Lanjut') AS hasil_tidak_tl
    FROM lhp l $where ORDER BY l.tahun DESC, l.id DESC LIMIT $limit OFFSET $offset");

$rows = [];
while ($rw = mysqli_fetch_assoc($q)) {
    $rows[] = $rw;
}

$rekMap = [];
$tlMap = [];
$rowIds = array_column($rows, 'id');
if ($rowIds) {
    $in = implode(',', array_map('intval', $rowIds));
    $qRek = mysqli_query($conn, "SELECT lhp_id, no, uraian FROM lhp_rekomendasi WHERE lhp_id IN ($in) ORDER BY lhp_id, no");
    while ($rk = mysqli_fetch_assoc($qRek)) {
        $rekMap[$rk['lhp_id']][] = $rk;
    }
    $qTl = mysqli_query($conn, "SELECT r.lhp_id, r.no AS rek_no, t.no AS tl_no, t.uraian, t.status
        FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id
        WHERE r.lhp_id IN ($in) ORDER BY r.lhp_id, r.no, t.no");
    while ($tx = mysqli_fetch_assoc($qTl)) {
        $tlMap[$tx['lhp_id']][] = $tx;
    }
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
                    <h1 class="jxb-page-title"><i class="fas fa-tasks me-2 text-primary"></i><?= $submenuMap[$sumber] ?></h1>
                    <div class="jxb-page-subtitle">Pemantauan tindak lanjut rekomendasi hasil pemeriksaan <?= $judulMap[$sumber] ?></div>
                </div>
                <div class="jxb-page-actions">
                    <a href="export.php?sumber=<?= $sumber ?><?= $filterUnit > 0 ? '&unit=' . $filterUnit : '' ?><?= $keyword !== '' ? '&keyword=' . urlencode($keyword) : '' ?>" class="btn btn-outline-success"><i class="fas fa-file-excel"></i> Export Excel</a>
                    <a href="create.php?sumber=<?= $sumber ?>" class="btn btn-primary"><i class="fas fa-plus"></i>Tambah TL LHP</a>
                </div>
            </div>

            <div class="jxb-filter-tabs mb-3">
                <?php foreach ($judulMap as $k => $v): ?>
                    <a href="?sumber=<?= $k ?>" class="btn btn-sm <?= $sumber === $k ? 'btn-primary' : 'btn-outline-secondary' ?>"><i class="fas fa-clipboard-check me-1"></i><?= $submenuMap[$k] ?></a>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="search-form mb-3">
                        <input type="hidden" name="sumber" value="<?= $sumber ?>">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <input type="text" name="keyword" class="form-control" placeholder="Cari nomor LHP / judul temuan..." value="<?= htmlspecialchars($keyword) ?>">
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <select name="unit" class="form-select">
                                    <option value="">Semua Unit</option>
                                    <?php foreach (mysqli_fetch_all($qUnitFilter, MYSQLI_ASSOC) as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= $filterUnit === (int)$u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nama_unit']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i><span class="d-none d-sm-inline ps-1">Cari</span></button>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive-wrapper">
                        <table class="table table-bordered table-hover align-middle jxb-tl-table">
                            <thead>
                                <tr class="text-center align-middle">
                                    <th rowspan="2" width="40">No</th>
                                    <th colspan="2">Temuan Pemeriksaan</th>
                                    <th colspan="2">Rekomendasi</th>
                                    <th rowspan="2" width="380">Tindak Lanjut Entitas yang Diperiksa</th>
                                    <th rowspan="2">Unit / Entitas yang Diperiksa</th>
                                    <th colspan="5">Hasil Pemantauan Tindak Lanjut</th>
                                    <th rowspan="2">Kesimpulan</th>
                                    <th rowspan="2" width="150"><abbr title="Nilai Penyerahan Aset / Penyetoran Uang ke Kas Negara/Daerah">Nilai Penyerahan</abbr></th>
                                    <th rowspan="2" width="150">Aksi</th>
                                </tr>
                                <tr class="text-center">
                                    <th>Judul</th>
                                    <th width="55">Jml</th>
                                    <th width="360">Uraian</th>
                                    <th width="55">Jml</th>
                                    <th width="70">Proses</th>
                                    <th width="70">Sesuai</th>
                                    <th width="70">Belum Sesuai</th>
                                    <th width="70">Belum Ditindak Lanjut</th>
                                    <th width="70">Tidak Dapat Ditindak Lanjut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rows) > 0): $no = $offset + 1; foreach ($rows as $r): ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td>
                                        <strong class="d-block"><?= htmlspecialchars($r['judul_temuan']) ?></strong>
                                        <small class="text-muted"><?= htmlspecialchars($r['nomor_lhp']) ?> &middot; TA <?= (int)$r['tahun'] ?></small>
                                    </td>
                                    <td class="text-center"><?= (int)$r['jml_rekomendasi'] ?></td>
                                    <td>
                                        <?php if (!empty($rekMap[$r['id']])): ?>
                                            <div class="jxb-split-list">
                                                <?php foreach ($rekMap[$r['id']] as $rk): ?>
                                                    <div class="jxb-split-item"><?= (int)$rk['no'] ?>. <?= nl2br(htmlspecialchars($rk['uraian'])) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (int)$r['jml_tl'] ?></td>
                                    <td>
                                        <?php if (!empty($tlMap[$r['id']])): ?>
                                            <div class="jxb-split-list">
                                                <?php
                                                $groups = []; $current = []; $lastRek = null;
                                                foreach ($tlMap[$r['id']] as $tx) {
                                                    if ($lastRek !== null && (string)$tx['rek_no'] !== (string)$lastRek) { $groups[] = $current; $current = []; }
                                                    $lastRek = $tx['rek_no'];
                                                    $current[] = '<span>' . (int)$tx['tl_no'] . '. ' . nl2br(htmlspecialchars($tx['uraian'])) . ' (' . htmlspecialchars($tx['status']) . ')</span>';
                                                }
                                                if ($current) { $groups[] = $current; }
                                                foreach ($groups as $g): ?>
                                                    <div class="jxb-split-item"><?= implode('<br>', $g) ?></div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>-<?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($r['nama_unit'] ?? '-') ?></td>
                                    <td class="text-center"><?= (int)$r['hasil_proses'] ?></td>
                                    <td class="text-center"><?= (int)$r['hasil_sesuai'] ?></td>
                                    <td class="text-center"><?= (int)$r['hasil_belum_sesuai'] ?></td>
                                    <td class="text-center"><?= (int)$r['hasil_belum_tl'] ?></td>
                                    <td class="text-center"><?= (int)$r['hasil_tidak_tl'] ?></td>
                                    <td style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($r['kesimpulan'])) ?></td>
                                    <td class="text-end text-nowrap"><?= $r['nilai'] !== null ? 'Rp ' . number_format((float)$r['nilai'], 0, ',', '.') : '-' ?></td>
                                    <td>
                                        <a href="detail.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm tb-icon btn-blink-border" title="Detail" aria-label="Detail"><i class="fas fa-eye"></i></a>
                                        <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-outline-warning btn-sm tb-icon btn-blink-border" title="Edit" aria-label="Edit"><i class="fas fa-edit"></i></a>
                                        <a href="javascript:void(0)" class="btn btn-danger btn-sm tb-icon btn-blink-border" onclick="hapusLHP(<?= $r['id'] ?>)" title="Hapus" aria-label="Hapus"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="15" class="text-center py-4">
                                        <div class="jxb-empty"><i class="fas fa-tasks"></i><div class="jxb-empty-title mt-1">Belum ada data <?= $submenuMap[$sumber] ?></div><div>Klik "Tambah TL LHP" untuk menambahkan.</div></div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted">Total: <strong><?= $totalRecords ?></strong></small>
                    <?php if ($totalPages > 1): ?>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php $pageQuery = 'sumber=' . $sumber . '&unit=' . $filterUnit . ($keyword !== '' ? '&keyword=' . urlencode($keyword) : ''); ?>
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $pageQuery ?>&page=<?= $page - 1 ?>" aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php
                            $range = [];
                            for ($i = 1; $i <= $totalPages; $i++) {
                                if ($i == 1 || $i == $totalPages || abs($i - $page) <= 1) {
                                    $range[] = $i;
                                } elseif (end($range) !== '...') {
                                    $range[] = '...';
                                }
                            }
                            foreach ($range as $i):
                                if ($i === '...'):
                            ?>
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                            <?php else: ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= $pageQuery ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endif; endforeach; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= $pageQuery ?>&page=<?= $page + 1 ?>" aria-label="Selanjutnya"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
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
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= addslashes($_SESSION['error']) ?>'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

<script>
function hapusLHP(id)
{
    Swal.fire({
        title: 'Hapus TL LHP?',
        text: 'Data tidak dapat dikembalikan',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    })
    .then((result) => {
        if (result.isConfirmed) {
            window.location = 'delete.php?id=' + id;
        }
    });
}
</script>

<?php include "../templates/footer.php"; ?>