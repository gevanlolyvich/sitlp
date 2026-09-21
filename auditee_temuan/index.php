<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];

$sql = "
SELECT
    tl.id AS tindak_lanjut_id,
    tl.rekomendasi_id,
    tl.target_selesai,
    tl.status,
    tl.catatan_spi,
    tl.bukti_file,
    a.nomor_audit,
    a.judul_audit,
    t.nomor_temuan,
    t.judul_temuan,
    r.nomor_rekomendasi,
    r.rekomendasi,
    tl.uraian_tindak_lanjut,
    tl.hasil_tindak_lanjut
FROM audit_tindak_lanjut tl
INNER JOIN audit_rekomendasi r
    ON tl.rekomendasi_id = r.id
INNER JOIN audit_temuan t
    ON r.temuan_id = t.id
INNER JOIN audit_pemeriksaan a
    ON t.audit_id = a.id
WHERE
    tl.unit_id = '$unit_id'
ORDER BY tl.updated_at DESC, tl.id DESC
";

//echo $sql; exit;

$qData = mysqli_query($conn, $sql);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-tasks me-2 text-primary"></i>Tindak Lanjut Saya</h1>
                    <div class="jxb-page-subtitle">Daftar temuan audit dan rekomendasi untuk unit kerja Anda</div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Daftar Temuan Audit dan Rekomendasi</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive-wrapper">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Audit</th>
                                <th>Temuan</th>
                                <th style="width: 20%;">Uraian</th>
                                <th style="width: 18%;">Hasil Tindak Lanjut</th>
                                <th>Target</th>
                                <th>Status Tindak Lanjut</th>
                                <th>Catatan SIA</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($qData) === 0):
                                ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="jxb-empty">
                                            <i class="fas fa-check-circle"></i>
                                            <div class="jxb-empty-title mt-1">Tidak ada data tindak lanjut</div>
                                            <div>Belum ada temuan/rekomendasi untuk unit Anda.</div>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            else:
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($qData)):
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['nomor_audit']) ?></strong>
                                        <br>
                                        <?= htmlspecialchars($row['judul_audit']) ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nomor_temuan']) ?></strong>
                                        <br>
                                        <?= htmlspecialchars($row['judul_temuan']) ?>
                                    </td>
                                    <td>
                                        <?= nl2br(htmlspecialchars($row['uraian_tindak_lanjut'])) ?>
                                    </td>
                                    <td>
                                        <?= nl2br(htmlspecialchars($row['hasil_tindak_lanjut'])) ?>
                                    </td>
                                    <td>
                                        <?= date(
                                            'd-m-Y',
                                            strtotime(
                                                $row['target_selesai']
                                            )
                                        ) ?>
                                    </td>
                                    <td>
                                        <?php
                                        $sts = $row['status'];
                                        $badge = [
                                            'Proses' => 'is-info',
                                            'Sesuai' => 'is-success',
                                            'Belum Sesuai' => 'is-danger',
                                            'Belum Ditindak Lanjut' => 'is-warn',
                                            'Tidak Dapat Ditindak Lanjut' => 'is-neutral'
                                        ];
                                        $badgeClass = isset($badge[$sts]) ? $badge[$sts] : 'is-neutral';
                                        echo '<span class="jxb-status-badge ' . $badgeClass . '">' . htmlspecialchars($sts) . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['catatan_spi'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <a href="../audit_tindak_lanjut/detail.php?id=<?= $row['tindak_lanjut_id'] ?>"
                                            class="btn btn-primary btn-sm tb-icon btn-blink-border" title="Lihat" aria-label="Lihat"><i class="fas fa-eye"></i></a>
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
<?php if (isset($_GET['upload']) && $_GET['upload'] == 'success'): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Bukti tindak lanjut berhasil diupload',
            timer: 2500,
            showConfirmButton: false
        });
    </script>
<?php endif; ?>

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