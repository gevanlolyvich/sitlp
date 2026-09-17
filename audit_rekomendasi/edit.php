<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SIA','AUDITOR']);

$temuan_id = (int)$_GET['temuan_id'];

$qTemuan = mysqli_query($conn,"SELECT id, audit_id, nomor_temuan, judul_temuan FROM audit_temuan WHERE id=$temuan_id");
$temuan = mysqli_fetch_assoc($qTemuan);

if(!$temuan){
    die("Temuan tidak ditemukan");
}

blockLockedAudit($conn, (int)$temuan['audit_id']);

$qRek = mysqli_query($conn,"SELECT * FROM audit_rekomendasi WHERE temuan_id=$temuan_id ORDER BY id");
$rekomendasiList = [];
while($r = mysqli_fetch_assoc($qRek)){ $rekomendasiList[] = $r; }
if(count($rekomendasiList) === 0){
    die("Tidak ada rekomendasi untuk temuan ini.");
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
     <h1 class="jxb-page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Rekomendasi</h1>
     <div class="jxb-page-subtitle">Ubah rekomendasi untuk temuan <?= htmlspecialchars($temuan['nomor_temuan']) ?></div>
    </div>
    <div class="jxb-page-actions">
     <a href="../audit_temuan/detail.php?id=<?= $temuan_id ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
   </div>
   <div class="card mt-3">
    <form action="update.php" method="post" id="form-rekomendasi">
     <input type="hidden" name="temuan_id" value="<?= $temuan_id ?>">
     <div class="card-body">
      <div class="mb-3">
       <label class="form-label">Nomor Temuan</label>
       <input type="text" class="form-control" value="<?= htmlspecialchars($temuan['nomor_temuan']) ?>" readonly>
      </div>
      <div id="rekomendasi-container">
       <?php foreach($rekomendasiList as $i => $rek): ?>
       <div class="rekomendasi-item d-flex gap-2 mb-2">
        <input type="hidden" name="rekomendasi_id[]" value="<?= $rek['id'] ?>">
        <textarea name="rekomendasi[]" class="form-control" rows="2" placeholder="Rekomendasi <?= $i + 1 ?>"><?= htmlspecialchars($rek['rekomendasi']) ?></textarea>
        <div class="d-flex flex-column align-self-start">
         <button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-add-rekomendasi mb-1" title="Tambah Rekomendasi"><i class="fas fa-plus"></i></button>
         <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-remove-rekomendasi" title="Hapus Rekomendasi"><i class="fas fa-minus"></i></button>
        </div>
       </div>
       <?php endforeach; ?>
      </div>
      <small class="text-muted d-block mt-1"><i class="fas fa-info-circle"></i> Gunakan tombol <b>+</b> untuk menambah rekomendasi lain.</small>
      <small class="text-muted d-block"><i class="fas fa-info-circle"></i> Tombol <b>−</b> pada baris akan menghapus rekomendasi tersebut dari database saat Simpan.</small>
     </div>
     <div class="card-footer">
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="../audit_temuan/detail.php?id=<?= $temuan_id ?>" class="btn btn-outline-secondary">Kembali</a>
     </div>
    </form>
    <script>
    (function () {
        var container = document.getElementById('rekomendasi-container');
        var form = document.getElementById('form-rekomendasi');
        var itemTemplate =
            '<div class="rekomendasi-item d-flex gap-2 mb-2">' +
            ' <textarea name="rekomendasi_baru[]" class="form-control" rows="2"></textarea>' +
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
                    var item = e.target.closest('.rekomendasi-item');
                    var hid = item.querySelector('input[name="rekomendasi_id[]"]');
                    if (hid && hid.value) {
                        Swal.fire({
                            title: 'Hapus Rekomendasi?',
                            text: 'Rekomendasi ini akan dihapus dari database saat Simpan.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                item.remove();
                                refresh();
                            }
                        });
                    } else {
                        item.remove();
                        refresh();
                    }
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