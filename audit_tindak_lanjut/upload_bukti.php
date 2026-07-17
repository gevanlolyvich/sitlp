<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'AUDITOR', 'AUDITEE']);

$id = (int) $_GET['id'];

$q = mysqli_query($conn, "SELECT * FROM audit_tindak_lanjut WHERE id=$id");

$tl = mysqli_fetch_assoc($q);

if (!$tl) {
    die("Data tidak ditemukan");
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Upload Bukti Tindak Lanjut</h3>
                </div>

                <form action="upload_bukti_process.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="rekomendasi_id" value="<?= $tl['rekomendasi_id'] ?>">
                    <div class="card-body">
                        <div class="mb-3">
                            <label>Nomor Tindak Lanjut</label>
                            <input type="text" class="form-control" value="<?= $tl['nomor_tindak_lanjut'] ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label>Bukti</label>
                            <input type="file" name="bukti" class="form-control" required>
                            <small>PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG</small>
                        </div>
                        <div class="mb-3">
                            <label>Hasil Tindak Lanjut</label>
                            <textarea name="hasil_tindak_lanjut" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <a href="../auditee_temuan/index.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</main>

<?php
include "../templates/footer.php";
?>