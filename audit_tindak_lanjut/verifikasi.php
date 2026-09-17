<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

$id = (int) $_GET['id'];

$q = mysqli_query($conn, "SELECT tl.*, u.nama_unit, r.nomor_rekomendasi, r.rekomendasi, t.nomor_temuan, t.judul_temuan
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja u ON tl.unit_id=u.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    WHERE tl.id=$id");

$tl = mysqli_fetch_assoc($q);

if (!$tl) {
    die("Data tidak ditemukan");
}

if ($tl['status'] == 'Sesuai') {
    $_SESSION['error'] = "Status sudah Sesuai. Tidak dapat diubah lagi.";
    header("Location: index.php");
    exit;
}

// Check if auditee has uploaded evidence
$qUploadCheck = mysqli_query($conn, "SELECT COUNT(*) cnt FROM audit_tindak_lanjut_log WHERE tindak_lanjut_id=$id AND aksi='upload_bukti'");
$uploadData = mysqli_fetch_assoc($qUploadCheck);
if ($uploadData['cnt'] == 0) {
    $_SESSION['error'] = "Belum ada upload bukti dari Auditee. Verifikasi dapat dilakukan setelah Auditee mengupload bukti.";
    header("Location: index.php" . ($tl['rekomendasi_id'] ? "?rekomendasi_id=" . $tl['rekomendasi_id'] : ""));
    exit;
}

// Get history logs
$qLog = mysqli_query($conn, "SELECT l.*, usr.nama
    FROM audit_tindak_lanjut_log l
    LEFT JOIN users usr ON l.dibuat_oleh=usr.id
    WHERE l.tindak_lanjut_id=$id
    ORDER BY l.id ASC");
$logs = [];
while ($log = mysqli_fetch_assoc($qLog)) {
    $logs[] = $log;
}

$badgeMap = [
    'Proses' => 'is-info',
    'Sesuai' => 'is-success',
    'Belum Sesuai' => 'is-danger',
    'Belum Ditindak Lanjut' => 'is-warn',
    'Tidak Dapat Ditindak Lanjut' => 'is-neutral'
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
                    <h1 class="jxb-page-title"><i class="fas fa-check-circle me-2 text-primary"></i>Review Tindak Lanjut</h1>
                    <div class="jxb-page-subtitle">Verifikasi tindak lanjut <?= htmlspecialchars($tl['nomor_tindak_lanjut']) ?></div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php<?= $tl['rekomendasi_id'] ? '?rekomendasi_id=' . $tl['rekomendasi_id'] : '' ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>

            <div class="card mt-3">

                <form action="verifikasi_process.php" method="post">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="rekomendasi_id" value="<?= $tl['rekomendasi_id'] ?>">

                    <div class="card-body">

                        <table class="table table-bordered">
                            <tr>
                                <th width="220">Nomor TL</th>
                                <td><?= htmlspecialchars($tl['nomor_tindak_lanjut']) ?></td>
                            </tr>
                            <tr>
                                <th>Unit Kerja</th>
                                <td><?= htmlspecialchars($tl['nama_unit']) ?></td>
                            </tr>
                            <tr>
                                <th>Uraian Tindak Lanjut</th>
                                <td style="white-space: pre-wrap;"><?= htmlspecialchars($tl['uraian_tindak_lanjut']) ?></td>
                            </tr>
                            <tr>
                                <th>Target Selesai</th>
                                <td><?= $tl['target_selesai'] ? date('d-m-Y', strtotime($tl['target_selesai'])) : '-' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status Saat Ini</th>
                                <td>
                                    <?php
                                    $sts = $tl['status'];
                                    $bc = isset($badgeMap[$sts]) ? $badgeMap[$sts] : 'is-neutral';
                                    echo '<span class="jxb-status-badge ' . $bc . '">' . htmlspecialchars($sts) . '</span>';
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <!-- History Timeline -->
                        <?php if (count($logs) > 0): ?>
                            <div class="mb-4">
                                <h5>Riwayat</h5>
                                <div class="timeline-inline">
                                    <?php foreach ($logs as $lidx => $log):
                                        $dt = date('d-m-Y H:i', strtotime($log['dibuat_pada']));
                                        $icon = ($log['aksi'] == 'buat') ? 'fa-plus-circle' : (($log['aksi'] == 'upload_bukti') ? 'fa-upload' : 'fa-check-circle');
                                        $color = ($log['aksi'] == 'buat') ? 'primary' : (($log['aksi'] == 'upload_bukti') ? 'info' : 'success');
                                        $label = ($log['aksi'] == 'buat') ? 'Dibuat' : (($log['aksi'] == 'upload_bukti') ? 'Upload Bukti' : 'Verifikasi');
                                        ?>
                                        <div class="d-flex mb-2">
                                            <div class="text-center mr-3" style="width:40px;">
                                                <i class="fas <?= $icon ?> text-<?= $color ?>"></i>
                                            </div>
                                            <div>
                                                <strong><?= $label ?></strong> <small class="text-muted"><?= $dt ?></small>
                                                <?php if ($log['aksi'] == 'verifikasi'): ?>
                                                    <div>
                                                        <?php if ($log['status_lama']): ?><span
                                                                class="jxb-status-badge is-neutral"><?= htmlspecialchars($log['status_lama']) ?></span>
                                                            <i class="fas fa-arrow-right mx-1"></i><?php endif; ?>
                                                        <span
                                                            class="jxb-status-badge <?= isset($badgeMap[$log['status_baru']]) ? $badgeMap[$log['status_baru']] : 'is-neutral' ?>"><?= htmlspecialchars($log['status_baru']) ?></span>
                                                    </div>
                                                <?php elseif ($log['aksi'] == 'upload_bukti'): ?>
                                                    <div class="small">Upload bukti baru</div>
                                                    <?php if ($log['file_bukti']): ?><a
                                                            href="../uploads/tindak_lanjut/<?= $log['file_bukti'] ?>" target="_blank"
                                                            class="btn btn-xs btn-outline-primary mt-1">Lihat Bukti</a><?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($lidx < count($logs) - 1): ?>
                                            <div class="text-center text-muted" style="margin-left:20px;">↓</div><?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Status Tindak Lanjut <span class="jxb-required">*</span></label>
                            <select name="status" id="status_tl" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Sesuai">Sesuai</option>
                                <option value="Belum Sesuai">Belum Sesuai</option>
                                <option value="Belum Ditindak Lanjut">Belum Ditindak Lanjut</option>
                                <option value="Tidak Dapat Ditindak Lanjut">Tidak Dapat Ditindak Lanjut</option>
                            </select>
                            <div class="form-text text-muted">
                                * <strong>Sesuai / Tidak Dapat Ditindak Lanjut</strong> = Final, tidak bisa diubah lagi,
                                Auditee tidak bisa upload ulang.<br>
                                * <strong>Belum Sesuai / Belum Ditindak Lanjut</strong> = Perlu revisi, Auditee bisa
                                upload ulang.
                            </div>
                        </div>

                        <div class="mb-3" id="fieldNilaiPenyerahan" style="display:none;">
                            <label class="form-label">Nilai Penyetoran / Penyerahan Uang (Rp)</label>
                            <input type="number" name="nilai_penyerahan" id="nilai_penyerahan" class="form-control"
                                min="0" step="0.01"
                                value="<?= $tl['nilai_penyerahan'] !== null && $tl['nilai_penyerahan'] !== '' ? number_format((float)$tl['nilai_penyerahan'], 2, '.', '') : '' ?>"
                                placeholder="0">
                            <div class="form-text text-muted">
                                * Jumlah uang yang disetor / nilai aset yang diserahkan ke kas negara/daerah.<br>
                                * Jika dikosongkan maka dianggap Rp 0.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan SIA</label>
                            <textarea name="catatan_spi" class="form-control" rows="4"
                                placeholder="Catatan untuk auditee..."><?= htmlspecialchars($tl['catatan_spi'] ?? '') ?></textarea>
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Review</button>
                        <a href="index.php<?= $tl['rekomendasi_id'] ? '?rekomendasi_id=' . $tl['rekomendasi_id'] : '' ?>" class="btn btn-outline-secondary">Kembali</a>
                    </div>

                </form>

            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('status_tl');
    var field = document.getElementById('fieldNilaiPenyerahan');
    function toggleNilai() {
        field.style.display = (select.value === 'Sesuai') ? '' : 'none';
    }
    select.addEventListener('change', toggleNilai);
    toggleNilai();
});
</script>

<style>
    .btn-xs {
        padding: 1px 5px;
        font-size: 12px;
    }
</style>

<?php
include "../templates/footer.php";
?>