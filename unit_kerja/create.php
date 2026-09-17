<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";

hasRole(['ADMIN','KEPALA_SIA']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

?>

<main class="app-main">
<div class="app-content">
<div class="container-fluid">
<div class="card mt-3">
<div class="card-header">
Tambah Unit Kerja
</div>

<form
action="store.php"
method="post">

<div class="card-body">

<div class="row g-3">

<div class="col-md-6">

<label>Kode Unit</label>

<input
type="text"
name="kode_unit"
class="form-control"
required>

</div>

<div class="col-md-6">

<label>Nama Unit</label>

<input
type="text"
name="nama_unit"
class="form-control"
required>

</div>

<div class="col-md-6">

<label>Email</label>

<input
type="email"
name="email"
class="form-control">

</div>

<div class="col-md-6">

<label>Telepon</label>

<input
type="text"
name="telepon"
class="form-control">

</div>

<div class="col-12">

<label>Alamat</label>

<textarea
name="alamat"
class="form-control"></textarea>

</div>

</div>

</div>

<div class="card-footer">

<button
class="btn btn-primary">

Simpan

</button>

<a
href="index.php"
class="btn btn-secondary">

Kembali

</a>

</div>

</form>

</div>

</div>

</div>

</main>

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

<?php
include "../templates/footer.php";
?>
