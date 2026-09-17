<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$temuan_id = (int)$_GET['temuan_id'];

$qAuditCheck = mysqli_query($conn,"SELECT audit_id FROM audit_temuan WHERE id=$temuan_id");
$auditCheck = mysqli_fetch_assoc($qAuditCheck);
blockLockedAudit($conn, (int)$auditCheck['audit_id']);

$qTemuan = mysqli_query($conn,"SELECT id,nomor_temuan,judul_temuan FROM audit_temuan WHERE id=$temuan_id");
$temuan = mysqli_fetch_assoc($qTemuan);
if(!$temuan)
{
    die("Temuan tidak ditemukan");
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
     <h3 class="card-title">Tambah Rekomendasi</h3>
    </div>
    <form action="store.php" method="post" id="form-rekomendasi">
     <input type="hidden" name="temuan_id" value="<?= $temuan_id ?>">
     <div class="card-body">
      <div class="mb-3">
       <label>Nomor Temuan</label>
       <input type="text" class="form-control" value="<?= htmlspecialchars($temuan['nomor_temuan']) ?>" readonly>
      </div>
      <div id="rekomendasi-container">
       <div class="rekomendasi-item d-flex gap-2 mb-2">
        <textarea name="rekomendasi[]" class="form-control" rows="2" placeholder="Rekomendasi 1"></textarea>
        <div class="d-flex flex-column align-self-start">
         <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-add-rekomendasi mb-1" title="Tambah Rekomendasi"><i class="fas fa-plus"></i></button>
         <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-remove-rekomendasi" title="Hapus Rekomendasi"><i class="fas fa-minus"></i></button>
        </div>
       </div>
      </div>
      <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Gunakan tombol <b>+</b> untuk menambah rekomendasi lain.</small>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="../audit_temuan/detail.php?id=<?= $temuan_id ?>" class="btn btn-secondary">Kembali</a>
     </div>
    </form>
    <script>
    (function () {
        var container = document.getElementById('rekomendasi-container');
        var form = document.getElementById('form-rekomendasi');
        var itemTemplate =
            '<div class="rekomendasi-item d-flex gap-2 mb-2">' +
            ' <textarea name="rekomendasi[]" class="form-control" rows="2"></textarea>' +
            ' <div class="d-flex flex-column align-self-start">' +
            '  <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-add-rekomendasi mb-1" title="Tambah Rekomendasi"><i class="fas fa-plus"></i></button>' +
            '  <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-remove-rekomendasi" title="Hapus Rekomendasi"><i class="fas fa-minus"></i></button>' +
            ' </div>' +
            '</div>';

        function refresh() {
            var items = container.querySelectorAll('.rekomendasi-item');
            for (var i = 0; i < items.length; i++) {
                var ta = items[i].querySelector('textarea');
                ta.placeholder = 'Rekomendasi ' + (i + 1);
                items[i].querySelector('.btn-remove-rekomendasi').disabled = (items.length === 1);
            }
        }

        function addItem(refItem) {
            var tmp = document.createElement('div');
            tmp.innerHTML = itemTemplate;
            var item = tmp.firstChild;
            if (refItem && refItem.parentNode === container) {
                refItem.insertAdjacentElement('afterend', item);
            } else {
                container.appendChild(item);
            }
            item.querySelector('textarea').focus();
            refresh();
        }

        container.addEventListener('click', function (e) {
            if (e.target.closest('.btn-add-rekomendasi')) {
                addItem(e.target.closest('.rekomendasi-item'));
                return;
            }
            if (e.target.closest('.btn-remove-rekomendasi')) {
                if (container.querySelectorAll('.rekomendasi-item').length > 1) {
                    e.target.closest('.rekomendasi-item').remove();
                    refresh();
                }
            }
        });

        form.addEventListener('submit', function (e) {
            var tas = container.querySelectorAll('textarea');
            var filled = false;
            for (var i = 0; i < tas.length; i++) {
                if (tas[i].value.trim() !== '') { filled = true; break; }
            }
            if (!filled) {
                e.preventDefault();
                alert('Isi minimal satu rekomendasi.');
            }
        });

        refresh();
    })();
    </script>
   </div>
  </div>
 </div>
</main>

<?php
include "../templates/footer.php";
?>
