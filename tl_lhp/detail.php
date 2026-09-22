<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q = mysqli_query($conn, "SELECT l.*, u.nama AS nama_user, uk.nama_unit FROM lhp l LEFT JOIN users u ON l.created_by=u.id LEFT JOIN unit_kerja uk ON l.unit_id=uk.id WHERE l.id=$id");
$r = mysqli_fetch_assoc($q);
if (!$r) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$sumber = $r['sumber'];
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];
$submenuMap = ['BPK' => 'TL LHP BPK', 'BPKP' => 'TL LHP BPKP', 'KAP' => 'TL LHP KAP'];

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
                                    <td><?= htmlspecialchars($r['nama_unit'] ?? '-') ?></td>
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
                                <tr>
                                    <th>Rekomendasi</th>
                                    <td colspan="3"><?= nl2br(htmlspecialchars($r['uraian_rekomendasi'])) ?> <strong>(Jml: <?= (int)$r['jml_rekomendasi'] ?>)</strong></td>
                                </tr>
                                <tr>
                                    <th>Tindak Lanjut Entitas yang Diperiksa</th>
                                    <td colspan="3"><?= nl2br(htmlspecialchars($r['uraian_tl'])) ?> <strong>(Jml: <?= (int)$r['jml_tl'] ?>)</strong></td>
                                </tr>
                                <tr>
                                    <th rowspan="2">Hasil Pemantauan Tindak Lanjut</th>
                                    <td>Sesuai: <strong><?= (int)$r['hasil_sesuai'] ?></strong></td>
                                    <td>Belum Sesuai: <strong><?= (int)$r['hasil_belum_sesuai'] ?></strong></td>
                                    <td rowspan="2">
                                        Bukti:<br>
                                        <?php if ($r['bukti_file']): ?>
                                            <a href="../uploads/tl_lhp/<?= htmlspecialchars($r['bukti_file']) ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="fas fa-file-alt"></i> Lihat bukti</a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Belum Ditindaklanjuti: <strong><?= (int)$r['hasil_belum_tl'] ?></strong></td>
                                    <td>Tidak Dapat Ditindaklanjuti: <strong><?= (int)$r['hasil_tidak_tl'] ?></strong></td>
                                </tr>
                                <tr>
                                    <th>Kesimpulan</th>
                                    <td colspan="3"><?= nl2br(htmlspecialchars($r['kesimpulan'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Nilai Penyerahan Aset / Penyetoran Uang ke Kas Negara/Daerah</th>
                                    <td colspan="3"><?= $r['nilai'] !== null && $r['nilai'] !== '' ? 'Rp ' . number_format((float)$r['nilai'], 0, ',', '.') : '-' ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include "../templates/footer.php"; ?>