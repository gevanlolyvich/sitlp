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
                    <h1 class="jxb-page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah <?= $submenuMap[$sumber] ?></h1>
                    <div class="jxb-page-subtitle">Input data pemantauan tindak lanjut <?= $judulMap[$sumber] ?></div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php?sumber=<?= $sumber ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>

            <div class="card mt-3">
                <form action="store.php" method="post" enctype="multipart/form-data" data-rek-form>
                    <input type="hidden" name="sumber" value="<?= $sumber ?>">
                    <div class="card-body">
                        <h6 class="jxb-form-section"><i class="fas fa-file-invoice me-1"></i>Data LHP</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nomor LHP <span class="jxb-required">*</span></label>
                                <input type="text" name="nomor_lhp" class="form-control" placeholder="cth: LHP-XXX/XVII/2026" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tahun Pemeriksaan <span class="jxb-required">*</span></label>
                                <input type="number" name="tahun" class="form-control" min="2000" max="<?= date('Y') + 1 ?>" value="<?= date('Y') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Judul Temuan Pemeriksaan <span class="jxb-required">*</span></label>
                                <input type="text" name="judul_temuan" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Unit / Entitas yang Diperiksa (bisa pilih lebih dari satu)</label>
                                <div class="row g-2">
                                    <?php while ($u = mysqli_fetch_assoc($qUnit)): ?>
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="unit_id[]" value="<?= (int)$u['id'] ?>" id="unit_<?= (int)$u['id'] ?>">
                                                <label class="form-check-label" for="unit_<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['nama_unit']) ?></label>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <small class="text-muted">Kosongkan bila belum diketahui unitnya.</small>
                            </div>
                        </div>

                        <hr>
                        <h6 class="jxb-form-section"><i class="fas fa-list-check me-1"></i>Rekomendasi & Tindak Lanjut</h6>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Jml Rekomendasi</label>
                                <input type="number" name="jml_rekomendasi" id="jml_rekomendasi" class="form-control" min="0" max="20" value="0" oninput="renderRek()">
                            </div>
                            <div class="col-md-9">
                                <small class="text-muted">Jumlah rekomendasi menentukan banyaknya kartu rekomendasi. Tiap rekomendasi memiliki jml tindak lanjut sendiri.</small>
                            </div>
                        </div>
                        <div id="rekContainer" class="mt-3"></div>

                        <hr>
                        <h6 class="jxb-form-section"><i class="fas fa-chart-pie me-1"></i>Hasil Pemantauan Tindak Lanjut
                            <small class="text-muted text-normal">(otomatis dari status tiap tindak lanjut)</small>
                        </h6>
                        <div class="row g-3">
                            <div class="col-6 col-md">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Proses</div>
                                    <strong id="sum_Proses">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Sesuai</div>
                                    <strong id="sum_Sesuai">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Belum Sesuai</div>
                                    <strong id="sum_Belum_Sesuai">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Belum Ditindak Lanjut</div>
                                    <strong id="sum_Belum_Ditindak_Lanjut">0</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md">
                                <div class="border rounded p-3 text-center">
                                    <div class="text-muted small">Tidak Dapat Ditindak Lanjut</div>
                                    <strong id="sum_Tidak_Dapat_Ditindak_Lanjut">0</strong>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Kesimpulan</label>
                                <textarea name="kesimpulan" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nilai Penyerahan Aset / Penyetoran Uang ke Kas Negara/Daerah (Rp)</label>
                                <input type="text" name="nilai" class="form-control" placeholder="cth: 1.500.000">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                        <a href="index.php?sumber=<?= $sumber ?>" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
<script>window.LHP_SEED = [];</script>
<?php include __DIR__ . '/_rekom_js.php'; ?>
<?php include "../templates/footer.php"; ?>