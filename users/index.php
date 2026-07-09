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

text:'Password berhasil direset menjadi 123456'

});

});

</script>

<?php
}
?>

<div class="row mt-3 mb-3">

<div class="col-sm-6">

<h3>Master User</h3>

</div>

<div class="col-sm-6 text-end">

<a
href="create.php"
class="btn btn-primary">

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
<td><?= $r['nama'] ?></td>
<td><?= $r['username'] ?></td>
<td><?= $r['nama_unit'] ?></td>
<td><?= $r['role'] ?></td>
<td>

<?=
$r['aktif']
?
'<span class="badge bg-success">Aktif</span>'
:
'<span class="badge bg-danger">Nonaktif</span>'
?>

</td>

<td>

<a
href="edit.php?id=<?= $r['id'] ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<!--
<a href="reset_password.php?id=<?= $r['id'] ?>" class="btn btn-info btn-sm">Reset</a>
-->

<a href="#" class="btn btn-info btn-sm" onclick="resetPassword(<?= $r['id'] ?>)">Reset</a>

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

function resetPassword(id)
{

Swal.fire({

title:'Reset Password ?',
text:'Password akan menjadi 123456',
icon:'warning',
showCancelButton:true,
confirmButtonText:'Ya, Reset',
cancelButtonText:'Batal'

})
.then((result)=>{

if(result.isConfirmed){

window.location=
'reset_password.php?id=' + id;

}

});

}

</script>
