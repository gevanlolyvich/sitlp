<?php
session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$qRow = mysqli_query($conn, "SELECT * FROM lhp WHERE id=$id");
$r = mysqli_fetch_assoc($qRow);
if (!$r) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: index.php");
    exit;
}

$sumber = $r['sumber'];
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];
$submenuMap = ['BPK' => 'TL LHP BPK', 'BPKP' => 'TL LHP BPKP', 'KAP' => 'TL LHP KAP'];

$qUnit = mysqli_query($conn, "SELECT id, nama_unit FROM unit_kerja WHERE aktif=1 ORDER BY nama_unit");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit <?= $submenuMap[$sumber] ?></h1>
                    <div class="jxb-page-subtitle">Perbarui data pemantauan tindak lanjut <?= $judulMap[$sumber] ?></div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php?sumber=<?= $sumber ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>

            <div class="card mt-3">
                <form action="update.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="card-body">
                        <h6 class="jxb-form-section"><i class="fas fa-file-invoice me-1"></i>Data LHP</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nomor LHP <span class="jxb-required">*</span></label>
                                <input type="text" name="nomor_lhp" class="form-control" value="<?= htmlspecialchars($r['nomor_lhp']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tahun Pemeriksaan <span class="jxb-required">*</span></label>
                                <input type="number" name="tahun" class="form-control" min="2000" max="<?= date('Y') + 1 ?>" value="<?= (int)$r['tahun'] ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sumber LHP</label>
                                <select name="sumber" class="form-control">
                                    <?php foreach ($judulMap as $k => $v): ?>
                                        <option value="<?= $k ?>" <?= $sumber === $k ? 'selected' : '' ?>><?= $submenuMap[$k] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit / Entitas yang Diperiksa</label>
                                <select name="unit_id" class="form-select">
                                    <option value="">Pilih Unit</option>
                                    <?php while ($u = mysqli_fetch_assoc($qUnit)): ?>
                                        <option value="<?= $u['id'] ?>" <?= (int)$r['unit_id'] === (int)$u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['nama_unit']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Judul Temuan Pemeriksaan <span class="jxb-required">*</span></label>
                                <input type="text" name="judul_temuan" class="form-control" value="<?= htmlspecialchars($r['judul_temuan']) ?>" required>
                            </div>
                        </div>

                        <hr>
                        <h6 class="jxb-form-section"><i class="fas fa-list-check me-1"></i>Rekomendasi</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Jml Rekomendasi</label>
                                <input type="number" name="jml_rekomendasi" class="form-control" min="0" value="<?= (int)$r['jml_rekomendasi'] ?>">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Uraian Rekomendasi</label>
                                <textarea name="uraian_rekomendasi" class="form-control" rows="3"><?= htmlspecialchars($r['uraian_rekomendasi']) ?></textarea>
                            </div>
                        </div>

                        <hr>
                        <h6 class="jxb-form-section"><i class="fas fa-clipboard-check me-1"></i>Tindak Lanjut Entitas yang Diperiksa</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Jml Tindak Lanjut</label>
                                <input type="number" name="jml_tl" class="form-control" min="0" value="<?= (int)$r['jml_tl'] ?>">
                            </div>
                            <div class="col-md-9">
                                <label class="form-label">Uraian Tindak Lanjut</label>
                                <textarea name="uraian_tl" class="form-control" rows="3"><?= htmlspecialchars($r['uraian_tl']) ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bukti Tindak Lanjut (ganti file, opsional)</label>
                                <?php if ($r['bukti_file']): ?>
                                    <div class="mb-2">
                                        <a href="../uploads/tl_lhp/<?= htmlspecialchars($r['bukti_file']) ?>" target="_blank" class="btn btn-outline-success btn-sm"><i class="fas fa-file-alt"></i> <?= htmlspecialchars($r['bukti_file']) ?></a>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="bukti_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">PDF/JPG/PNG maks. 5MB. Kosongkan bila tidak mengganti bukti.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nilai Penyerahan Aset / Penyetoran Uang ke Kas Negara/Daerah (Rp)</label>
                                <input type="text" name="nilai" class="form-control" value="<?= $r['nilai'] !== null ? number_format((float)$r['nilai'], 0, ',', '.') : '' ?>" placeholder="cth: 1.500.000">
                            </div>
                        </div>

                        <hr>
                        <h6 class="jxb-form-section"><i class="fas fa-chart-pie me-1"></i>Hasil Pemantauan Tindak Lanjut</h6>
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label">Sesuai</label>
                                <input type="number" name="hasil_sesuai" class="form-control" min="0" value="<?= (int)$r['hasil_sesuai'] ?>">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Belum Sesuai</label>
                                <input type="number" name="hasil_belum_sesuai" class="form-control" min="0" value="<?= (int)$r['hasil_belum_sesuai'] ?>">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Belum Ditindaklanjuti</label>
                                <input type="number" name="hasil_belum_tl" class="form-control" min="0" value="<?= (int)$r['hasil_belum_tl'] ?>">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label">Tidak Dapat Ditindaklanjuti</label>
                                <input type="number" name="hasil_tidak_tl" class="form-control" min="0" value="<?= (int)$r['hasil_tidak_tl'] ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Kesimpulan</label>
                                <textarea name="kesimpulan" class="form-control" rows="2"><?= htmlspecialchars($r['kesimpulan']) ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                        <a href="index.php?sumber=<?= $sumber ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<?php include "../templates/footer.php"; ?>