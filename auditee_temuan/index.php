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
    tl.verifikasi_status,
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
ORDER BY
    CASE
        WHEN tl.status='OPEN' THEN 1
        WHEN tl.status='PROSES' THEN 2
        WHEN tl.status='SELESAI' THEN 3
    END,
    tl.target_selesai ASC

";

//echo $sql; exit;

$qData = mysqli_query($conn, $sql);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        Tindak Lanjut Saya
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="../dashboard">
                                Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">
                            Tindak Lanjut Saya
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Daftar Temuan Audit dan Rekomendasi
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Audit</th>
                                <th>Temuan</th>
                                <th style="width: 20%;">Rekomendasi</th>
                                <th style="width: 18%;">Hasil Tindak Lanjut</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th>Verifikasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($qData)):
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= $row['nomor_audit'] ?></strong>
                                        <br>
                                        <?= $row['judul_audit'] ?>
                                    </td>
                                    <td>
                                        <strong><?= $row['nomor_temuan'] ?></strong>
                                        <br>
                                        <?= $row['judul_temuan'] ?>
                                    </td>
                                    <td>
                                        <div style="white-space: normal; word-wrap: break-word; overflow-wrap: break-word; max-width: 250px;">
                                        <?= nl2br(
                                            htmlspecialchars(
                                                $row['rekomendasi']
                                            )
                                        ) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="white-space: normal; word-wrap: break-word; overflow-wrap: break-word; max-width: 250px;">
                                        <?= htmlspecialchars($row['hasil_tindak_lanjut']) ?>
                                        </div>
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
                                        $status = strtoupper(trim($row['status']));
                                        $badge = [
                                            'OPEN' => 'primary',
                                            'PROSES' => 'warning',
                                            'SELESAI' => 'success'
                                        ];
                                        if (isset($badge[$status])) {
                                            echo '<span class="badge bg-' . $badge[$status] . '">' .
                                                $status .
                                                '</span>';
                                        } else {
                                            echo '[' . $status . ']';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $verifikasi = strtoupper(trim($row['verifikasi_status']));
                                        $badge = [
                                            'BELUM' => 'secondary',
                                            'DITERIMA' => 'success',
                                            'DITOLAK' => 'danger'
                                        ];
                                        if (isset($badge[$verifikasi])) {
                                            echo '<span class="badge bg-' . $badge[$verifikasi] . '">' .
                                                $verifikasi .
                                                '</span>';
                                        } else {
                                            echo '[' . $verifikasi . ']';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="../audit_tindak_lanjut/index.php?rekomendasi_id=<?= $row['rekomendasi_id'] ?>"
                                            class="btn btn-primary btn-sm"><i class="fas fa-arrow-right"></i>Tindak
                                            Lanjut</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
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