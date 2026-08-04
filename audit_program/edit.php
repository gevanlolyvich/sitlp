<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int) $_GET['id'];
$q = mysqli_query($conn, "SELECT * FROM audit_program WHERE id=$id");
$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Program Audit tidak ditemukan");
}
if ($data['status'] != 'RENCANA') {
    die("Program Audit dengan status " . $data['status'] . " tidak dapat diedit.");
}
$triwulanList = ['TW1', 'TW2', 'TW3', 'TW4'];
$jenisAuditList = [
    'Operasional|Keuangan|Kepatuhan',
    'Verifikasi',
    'Investigasi',
    'Khusus'
];
$risikoList = [
    'RENDAH',
    'SEDANG',
    'TINGGI'
];
$unit = mysqli_query($conn, "SELECT * FROM unit_kerja WHERE aktif=1 ORDER BY nama_unit");
$auditor = mysqli_query($conn, "SELECT * FROM auditor WHERE aktif=1 ORDER BY nama_auditor");

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                Edit Program Audit
                            </h3>
                        </div>
                        <form action="update.php" method="post">
                            <input type="hidden" name="id" value="<?= $data['id'] ?>">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Kode Program</label>
                                        <input type="text" name="kode_program" value="<?= $data['kode_program'] ?>"
                                            class="form-control" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Tahun</label>
                                        <input type="number" name="tahun" value="<?= $data['tahun'] ?>"
                                            class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Triwulan</label>
                                        <select name="triwulan" class="form-select" required>
                                            <option value="TW1" <?= $data['triwulan'] == 'TW1'
                                                ? 'selected'
                                                : '' ?>>
                                                TW1
                                            </option>
                                            <option value="TW2" <?= $data['triwulan'] == 'TW2'
                                                ? 'selected'
                                                : '' ?>>
                                                TW2
                                            </option>
                                            <option value="TW3" <?= $data['triwulan'] == 'TW3'
                                                ? 'selected'
                                                : '' ?>>
                                                TW3
                                            </option>
                                            <option value="TW4" <?= $data['triwulan'] == 'TW4'
                                                ? 'selected'
                                                : '' ?>>
                                                TW4
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Bulan</label>
                                        <select name="bulan_rencana" class="form-select">
                                            <?php
                                            for ($i = 1; $i <= 12; $i++) {
                                                ?>
                                                <option value="<?= $i ?>">
                                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Unit Kerja</label>
                                        <select name="unit_id" class="form-select" required>
                                            <?php
                                            while ($u = mysqli_fetch_assoc($unit)) {
                                                ?>
                                                <option value="<?= $u['id'] ?>" <?= $u['id'] == $data['unit_id']
                                                      ? 'selected'
                                                      : '' ?>>
                                                    <?= htmlspecialchars($u['nama_unit']) ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Penanggung Jawab</label>
                                        <select name="penanggung_jawab_id" class="form-select">
                                            <option value="">Pilih Auditor</option>
                                            <?php
                                            while ($a = mysqli_fetch_assoc($auditor)) {
                                                ?>
                                                <option value="<?= $a['id'] ?>" <?= $a['id'] == $data['penanggung_jawab_id']
                                                      ? 'selected'
                                                      : '' ?>>
                                                    <?= htmlspecialchars($a['nama_auditor']) ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Jenis Audit</label>
                                        <select name="jenis_audit" class="form-select">
                                            <?php
                                            $jenisAudit = [
                                                'Operasional',
                                                'Keuangan',
                                                'Kepatuhan',
                                                'Operasional|Keuangan|Kepatuhan',
                                                'Verifikasi',
                                                'Investigasi',
                                                'Khusus'
                                            ];
                                            foreach ($jenisAudit as $jenis) {
                                                ?>
                                                 <option value="<?= $jenis ?>" <?= (strcasecmp($data['jenis_audit'], $jenis) == 0)
                                                       ? 'selected'
                                                       : '' ?>>
                                                    <?= $jenis ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Level Risiko</label>
                                        <select name="level_risiko" class="form-select">
                                            <?php
                                            $risikoList = [
                                                'RENDAH',
                                                'SEDANG',
                                                'TINGGI'
                                            ];
                                            foreach ($risikoList as $risiko) {
                                                ?>
                                                <option value="<?= $risiko ?>" <?= $data['level_risiko'] == $risiko
                                                      ? 'selected'
                                                      : '' ?>>
                                                    <?= $risiko ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <br>
                                <div class="mb-3">
                                    <label>Judul Program</label>
                                    <input type="text" name="judul_program"
                                        value="<?= htmlspecialchars($data['judul_program']) ?>" class="form-control"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label>Estimasi Hari</label>
                                    <input type="number" name="estimasi_hari" value="<?= $data['estimasi_hari'] ?>"
                                        class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Keterangan</label>
                                    <textarea name="keterangan" class="form-control"
                                        rows="3"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a href="index.php" class="btn btn-secondary">Kembali</a>
                        </form>
                    </div>
