<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA']);

$id = (int)$_GET['id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM unit_kerja WHERE id='$id'"
);

$data = mysqli_fetch_assoc($query);

if(!$data){
    die("Data unit kerja tidak ditemukan");
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";
?>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid">
            <div class="jxb-page-header">
                <div>
                    <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Unit Kerja</h1>
                    <div class="jxb-page-subtitle">Ubah data unit kerja</div>
                </div>
                <div class="jxb-page-actions">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="card">

            <form action="update.php" method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $data['id'] ?>"
                >

                <div class="card-body">

                    <div class="form-group">
                        <label class="form-label">Nama Unit <span class="jxb-required">*</span></label>

                        <input
                            type="text"
                            name="nama_unit"
                            class="form-control"
                            value="<?= htmlspecialchars($data['nama_unit']) ?>"
                            required
                        >
                    </div>

                </div>

                <div class="card-footer">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Simpan
                    </button>

                    <a
                        href="index.php"
                        class="btn btn-outline-secondary"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>
    </div>
</main>

<?php include "../templates/footer.php"; ?>
