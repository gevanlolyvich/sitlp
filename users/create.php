<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah User</h1>
                    <div class="jxb-page-subtitle">Buat akun pengguna baru</div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <form action="store.php" method="post" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap <span class="jxb-required">*</span></label>
                                            <input type="text" name="nama" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Username <span class="jxb-required">*</span></label>
                                            <input type="text" name="username" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Role <span class="jxb-required">*</span></label>
                                            <select name="role" class="form-select" required>
                                                <option value="">Pilih Role</option>
                                                <option value="ADMIN">ADMIN</option>
                                                <option value="KEPALA_SIA">KEPALA SIA</option>
                                                <option value="AUDITOR">AUDITOR</option>
                                                <option value="AUDITEE">AUDITEE</option>
                                                <option value="DIREKSI">DIREKSI</option>
                                                <option value="KOMISARIS">KOMISARIS</option>
                                                <option value="KOMITE_AUDIT">KOMITE AUDIT</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Password <span class="jxb-required">*</span></label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Konfirmasi Password <span class="jxb-required">*</span></label>
                                            <input type="password" name="confirm_password" class="form-control"
                                                required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="aktif" value="1" checked class="form-check-input">
                                    <label class="form-check-label">User Aktif</label>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="form-label">Unit Kerja</label>
                                    <select name="unit_id" id="unit_id" class="form-control">
                                        <option value="">Pilih Unit Kerja</option>
                                        <?php
                                        $qUnit = mysqli_query($conn, "SELECT * FROM unit_kerja ORDER BY nama_unit");
                                        while (
                                            $u = mysqli_fetch_assoc($qUnit)
                                        ): ?>
                                            <option value="<?= $u['id'] ?>"><?= $u['nama_unit'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="form-group" id="unit_group" style="display:none;"> -->
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <a href="index.php" class="btn btn-outline-secondary">Kembali</a>
                            </div>
                            <!-- </div> -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<script>
    document
        .getElementById('role')
        .addEventListener(
            'change',
            function () {
                const unitGroup =
                    document.getElementById(
                        'unit_group'
                    );
                if (
                    this.value
                    ==
                    'AUDITEE'
                ) {
                    unitGroup.style.display =
                        'block';
                }
                else {
                    unitGroup.style.display =
                        'none';
                    document.getElementById(
                        'unit_id'
                    ).value = '';
                }
            }
        );
</script>

<?php
include "../templates/footer.php";
?>

<?php if (isset($_SESSION['error'])) : ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= addslashes($_SESSION['error']) ?>'
});
</script>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>