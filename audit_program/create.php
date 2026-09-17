<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

$tahun = date('Y');

$q = mysqli_query($conn, "SELECT COUNT(*) total FROM audit_program WHERE tahun='$tahun'");
$d = mysqli_fetch_assoc($q);
$urut = $d['total'] + 1;

$kode_program = 'PKPT-' . $tahun . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

$unit = mysqli_query($conn, "SELECT * FROM unit_kerja WHERE aktif=1 ORDER BY nama_unit");
$auditor = mysqli_query($conn, "SELECT * FROM auditor WHERE aktif=1 ORDER BY nama_auditor");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
	<div class="app-content">
		<div class="container-fluid">
			<div class="jxb-page-header">
				<div>
					<h1 class="jxb-page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Program Audit</h1>
					<div class="jxb-page-subtitle">Buat program audit baru pada rencana audit tahun berjalan</div>
				</div>
				<div class="jxb-page-actions">
					<a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-md-12">
					<div class="card">
						<form action="store.php" method="post">
							<div class="card-body">
								<div class="row">
									<div class="col-md-4">
										<label class="form-label">Kode Program</label>
										<input type="text" name="kode_program" value="<?= $kode_program ?>"
											class="form-control" readonly>
									</div>
									<div class="col-md-2">
										<label class="form-label">Tahun <span class="jxb-required">*</span></label>
										<input type="number" name="tahun" value="<?= $tahun ?>" class="form-control"
											required>
									</div>
									<div class="col-md-3">
										<label class="form-label">Triwulan <span class="jxb-required">*</span></label>
										<select name="triwulan" class="form-select" required>
											<option value="TW1">TW1</option>
											<option value="TW2">TW2</option>
											<option value="TW3">TW3</option>
											<option value="TW4">TW4</option>
										</select>
									</div>
									<div class="col-md-3">
										<label class="form-label">Bulan</label>
										<select name="bulan_rencana" class="form-select">
											<?php
											for ($i = 1; $i <= 12; $i++) {
												?>
												<option value="<?= $i ?>"><?= date('F', mktime(0, 0, 0, $i, 1)) ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="col-md-6">
										<label class="form-label">Unit Kerja <span class="jxb-required">*</span></label>
										<select name="unit_id" class="form-select" required>
											<option value="">Pilih Unit Kerja</option>
											<?php
											while ($u = mysqli_fetch_assoc($unit)) {
												?>
												<option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nama_unit']) ?>
												</option>
											<?php } ?>
										</select>
									</div>
									<div class="col-md-6">
										<label class="form-label">Penanggung Jawab</label>
										<select name="penanggung_jawab_id" class="form-select">
											<option value="">Pilih Auditor</option>
											<?php
											while ($a = mysqli_fetch_assoc($auditor)) {
												?>
												<option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nama_auditor']) ?>
												</option>
											<?php } ?>
										</select>
									</div>
								</div>
								<br>
								<div class="row">
									<div class="col-md-4">
										<label class="form-label">Jenis Audit</label>
										<select name="jenis_audit" class="form-select">
											<option value="Operasional">Operasional</option>
											<option value="Keuangan">Keuangan</option>
											<option value="Kepatuhan">Kepatuhan</option>
											<option value="Operasional|Keuangan|Kepatuhan">Operasional|Keuangan|Kepatuhan</option>
											<option value="Verifikasi">Verifikasi</option>
											<option value="Investigasi">Investigasi</option>
											<option value="Khusus">Khusus</option>
										</select>
									</div>
									<div class="col-md-4">
										<label class="form-label">Level Risiko</label>
										<select name="level_risiko" class="form-select">
											<option>Rendah</option>
											<option selected>Sedang</option>
											<option>Tinggi</option>
										</select>
									</div>
								</div>
								<br>
								<div class="mb-3">
									<label class="form-label">Judul Program <span class="jxb-required">*</span></label>
									<input type="text" name="judul_program" class="form-control" required>
								</div>
								<div class="mb-3">
									<label class="form-label">Estimasi Hari</label>
									<input type="number" name="estimasi_hari" value="14" class="form-control">
								</div>
								<div class="mb-3">
									<label class="form-label">Keterangan</label>
									<textarea name="keterangan" class="form-control" rows="3"></textarea>
								</div>
							</div>
							<div class="card-footer">
								<button type="submit" class="btn btn-primary">Simpan</button>
								<a href="index.php" class="btn btn-outline-secondary">Kembali</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
include "../templates/footer.php";
?>