<?php
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['AUDITEE']);

$unit_id = (int) $_SESSION['unit_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id < 1) {
    $_SESSION['error'] = "Data tindak lanjut tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$q = mysqli_query($conn, "SELECT t.id AS tl_id, t.status, t.uraian AS tl_uraian, t.no AS tl_no,
        r.id AS rek_id, r.no AS rek_no, r.uraian AS rek_uraian,
        l.id AS lhp_id, l.sumber, l.nomor_lhp, l.tahun, l.judul_temuan
    FROM lhp_tl t
    JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id
    JOIN lhp l ON r.lhp_id=l.id
    WHERE t.id=$id
      AND EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$unit_id)");
$tl = mysqli_fetch_assoc($q);

if (!$tl) {
    $_SESSION['error'] = "Tindak lanjut tidak ditemukan atau bukan unit Anda.";
    header("Location: index.php");
    exit;
}

$qB = mysqli_query($conn, "SELECT id, file FROM lhp_tl_bukti WHERE tl_id=$id ORDER BY id");
$bukti = [];
while ($b = mysqli_fetch_assoc($qB)) {
    $bukti[] = $b;
}

$badgeMap = [
    'Proses' => 'is-info',
    'Sesuai' => 'is-success',
    'Belum Sesuai' => 'is-danger',
    'Belum Ditindak Lanjut' => 'is-warn',
    'Tidak Dapat Ditindak Lanjut' => 'is-neutral',
];
$sumberBadge = ['BPK' => 'is-info', 'BPKP' => 'is-warn', 'KAP' => 'is-neutral'];

$isFinal = in_array($tl['status'], ['Sesuai', 'Tidak Dapat Ditindak Lanjut']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-file-upload me-2 text-primary"></i>Upload Bukti Tindak Lanjut LHP</h1>
                    <div class="jxb-page-subtitle">Unggah dokumen bukti untuk tindak lanjut LHP unit Anda</div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Sumber / LHP</th>
                            <td>
                                <span class="jxb-status-badge <?= $sumberBadge[$tl['sumber']] ?? 'is-neutral' ?>"><?= htmlspecialchars($tl['sumber']) ?></span>
                                <strong class="ms-2"><?= htmlspecialchars($tl['nomor_lhp']) ?></strong>
                                <span class="text-muted">&middot; TA <?= (int)$tl['tahun'] ?></span>
                            </td>
                        </tr>
                        <tr>
                            <th>Judul Temuan</th>
                            <td><?= nl2br(htmlspecialchars($tl['judul_temuan'])) ?></td>
                        </tr>
                        <tr>
                            <th>Rekomendasi</th>
                            <td><strong>Rek. <?= (int)$tl['rek_no'] ?>.</strong> <span style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($tl['rek_uraian'])) ?></span></td>
                        </tr>
                        <tr>
                            <th>Tindak Lanjut</th>
                            <td><strong>TL <?= (int)$tl['tl_no'] ?>.</strong> <span style="white-space: pre-wrap;"><?= nl2br(htmlspecialchars($tl['tl_uraian'])) ?></span></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $stsU = $tl['status'];
                                $bcU = isset($badgeMap[$stsU]) ? $badgeMap[$stsU] : 'is-neutral';
                                echo '<span class="jxb-status-badge ' . $bcU . '">' . htmlspecialchars($stsU) . '</span>';
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-paperclip me-2 text-primary"></i>Bukti yang Sudah Diupload</h6>
                </div>
                <div class="card-body">
                    <?php if ($bukti): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($bukti as $b): ?>
                                <a href="<?= htmlspecialchars('/sisia/uploads/tl_lhp/' . $b['file']) ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="fas fa-paperclip"></i> <?= htmlspecialchars($b['file']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted small">Belum ada bukti.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-upload me-2 text-primary"></i>Upload Bukti Baru</h6>
                </div>
                <div class="card-body">
                    <?php if ($isFinal): ?>
                        <div class="alert alert-light border mb-0">
                            <i class="fas fa-lock me-1 text-muted"></i>
                            Status ini sudah final (<?= htmlspecialchars($tl['status']) ?>). Upload bukti tidak dapat dilakukan.
                        </div>
                    <?php else: ?>
                        <?php if (isset($_SESSION['upload_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($_SESSION['upload_success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                            </div>
                            <?php unset($_SESSION['upload_success']); ?>
                        <?php endif; ?>
                        <form action="upload_process.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?= (int)$tl['tl_id'] ?>">
                            <div class="mb-3">
                                <label class="form-label">File Bukti <span class="jxb-required">*</span></label>
                                <input type="file" name="bukti[]" class="form-control" multiple required accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx">
                                <small class="text-muted">Maks 5 file @5MB. <span class="text-warning fw-semibold">Jangan upload file berukuran besar!</span> File yang boleh di upload: PDF, JPG, JPEG, PNG, XLS, XLSX.</small>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Bukti</button>
                        </form>
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