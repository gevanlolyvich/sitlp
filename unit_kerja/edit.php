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

<div class="content-wrapper">
    <section class="content-header">
        <h1>Edit Unit Kerja</h1>
    </section>

    <section class="content">
        <div class="card">

            <div class="card-header">
                <h3 class="card-title">
                    Form Edit Unit Kerja
                </h3>
            </div>

            <form action="update.php" method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $data['id'] ?>"
                >

                <div class="card-body">

                    <div class="form-group">
                        <label>Nama Unit</label>

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
                        class="btn btn-secondary"
                    >
                        Kembali
                    </a>

                </div>

            </form>

        </div>
    </section>
</div>

<?php include "../templates/footer.php"; ?>
