<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

//hasRole(['ADMIN']);
checkRole(['ADMIN']);

include "../templates/header.php";
include "../templates/navbar.php";
include "../templates/sidebar.php";

$sql =
mysqli_query(
    $conn,
    "SELECT a.*, (select nama_unit from unit_kerja where id = a.unit_id) as nama_unit
     FROM users a
     ORDER BY a.nama"
);
?>

<main class="app-main">

<div class="app-content">

<div class="container-fluid">

<?php
if(
isset($_GET['msg'])
&&
$_GET['msg']=='reset_success'
){
?>

<script>

document.addEventListener(
'DOMContentLoaded',
function(){

Swal.fire({

icon:'success',

title:'Berhasil',

text:'Password berhasil direset'

});

});

</script>

<?php
}
?>

<?php
if(
isset($_GET['msg'])
&&
$_GET['msg']=='reset_failed'
){
?>

<script>

document.addEventListener(
'DOMContentLoaded',
function(){

Swal.fire({

icon:'error',

title:'Gagal',

text:'Password baru dan ulangi password harus sama (minimal 6 karakter).'

});

});

</script>

<?php
}
?>

<div class="row mt-3 mb-3">
<div class="col-sm-6">
<div class="jxb-page-header">
<div>
<h1 class="jxb-page-title"><i class="fas fa-users me-2 text-primary"></i>Master User</h1>
<div class="jxb-page-subtitle">Kelola akun pengguna sistem</div>
</div>
</div>
</div>
<div class="col-sm-6 text-end">
<a href="create.php" class="btn btn-primary">
<i class="fas fa-plus"></i>
Tambah User
</a>
</div>
</div>

<div class="card">

<div class="card-body">

<!--
<table class="table table-bordered table-hover">
-->

<div class="table-responsive-wrapper"><table
id="tblUser"
class="table table-bordered table-striped">

<thead>

<tr>

<th>ID</th>
<th>Nama</th>
<th>Username</th>
<th>Unit Kerja</th>
<th>Role</th>
<th>Status</th>
<th width="150">Aksi</th>

</tr>

</thead>

<tbody>

<?php
while(
$r=mysqli_fetch_assoc($sql)
){
?>

<tr>

<td><?= $r['id'] ?></td>
<td><?= htmlspecialchars($r['nama']) ?></td>
<td><?= htmlspecialchars($r['username']) ?></td>
<td><?= htmlspecialchars($r['nama_unit'] ?? '') ?></td>
<td><?= htmlspecialchars($r['role']) ?></td>
<td>

<?=
$r['aktif']
?
'<span class="jxb-status-badge is-success">Aktif</span>'
:
'<span class="jxb-status-badge is-danger">Nonaktif</span>'
?>

</td>

<td>

<a
href="edit.php?id=<?= $r['id'] ?>"
class="btn btn-outline-warning btn-sm">

 Edit

</a>

<a href="#" class="btn btn-outline-primary btn-sm" onclick="resetPassword(<?= $r['id'] ?>, '<?= htmlspecialchars($r['nama'], ENT_QUOTES) ?>')">Reset</a>

</td>

</tr>

<?php
}
?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

</main>

<div class="modal fade" id="modalReset" tabindex="-1" aria-labelledby="modalResetLabel" aria-hidden="true">
 <div class="modal-dialog">
  <div class="modal-content">
   <form action="reset_password.php" method="post" id="formReset" autocomplete="off">
    <div class="modal-header">
     <h5 class="modal-title" id="modalResetLabel">Reset Password</h5>
     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
     <input type="hidden" name="id" id="reset_user_id">
     <div class="mb-3">
      <label class="form-label">User</label>
      <input type="text" id="reset_user_nama" class="form-control" readonly>
     </div>
     <div class="mb-3">
      <label class="form-label">Password Baru</label>
      <input type="password" name="password_baru" id="password_baru" class="form-control" minlength="6" required>
     </div>
     <div class="mb-3">
      <label class="form-label">Ulangi Password</label>
      <input type="password" name="password_ulang" id="password_ulang" class="form-control" minlength="6" required>
     </div>
     <div id="reset_error" class="text-danger small d-none">Password baru dan ulangi password tidak sama!</div>
    </div>
    <div class="modal-footer">
     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
     <button type="submit" class="btn btn-primary">Simpan</button>
    </div>
   </form>
  </div>
 </div>
</div>

<?php
include "../templates/footer.php";
?>
<script>

$(document).ready(function(){

$('#tblUser').DataTable({

responsive:true,
pageLength:10,
dom:'Bfrtip',
buttons:[
'excel',
'pdf',
'print'
]

});

});

function resetPassword(id, nama)
{

document.getElementById('reset_user_id').value = id;
document.getElementById('reset_user_nama').value = nama;
document.getElementById('password_baru').value = '';
document.getElementById('password_ulang').value = '';
document.getElementById('reset_error').classList.add('d-none');

var modal = new bootstrap.Modal(
document.getElementById('modalReset')
);
modal.show();

}

$(document).ready(function(){

$('#formReset').on('submit', function(e){

var p1 = $('#password_baru').val();
var p2 = $('#password_ulang').val();

if(p1 !== p2){

e.preventDefault();
$('#reset_error').removeClass('d-none');
return false;

}

});

});

</script>
