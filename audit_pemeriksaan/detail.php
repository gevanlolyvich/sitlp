<?php

session_start();
require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR', 'AUDITEE']);

$id = (int) $_GET['id'];

$q = mysqli_query($conn, "SELECT ap.*, uk.nama_unit, au.nama_auditor
	FROM audit_pemeriksaan ap
	LEFT JOIN unit_kerja uk ON ap.unit_id=uk.id
	LEFT JOIN auditor au ON ap.ketua_auditor_id=au.id
	WHERE ap.id=$id");

$audit = mysqli_fetch_assoc($q);
if (!$audit) {
  die('Data audit tidak ditemukan');
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
          <h1 class="jxb-page-title"><i class="fas fa-eye me-2 text-primary"></i><?= htmlspecialchars($audit['nomor_audit']) ?></h1>
          <div class="jxb-page-subtitle">Detail pemeriksaan audit</div>
        </div>
        <div class="jxb-page-actions">
          <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
      </div>
      <div class="card mt-3">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <table class="table table-bordered">
                <tr>
                  <th width="220">Judul Audit</th>
                  <td><?= htmlspecialchars($audit['judul_audit']) ?></td>
                </tr>
                <tr>
                  <th>Unit Kerja</th>
                  <td><?= htmlspecialchars($audit['nama_unit']) ?></td>
                </tr>
                <tr>
                  <th>Jenis Audit</th>
                  <td><?= htmlspecialchars($audit['jenis_audit']) ?></td>
                </tr>
                <tr>
                  <th>Ketua Auditor</th>
                  <td><?= htmlspecialchars($audit['nama_auditor']) ?></td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><?php
                    $status = $audit['status'];
                    if ($status == 'Draft') {
                      echo '<span class="jxb-status-badge is-neutral">Draft</span>';
                    } elseif ($status == 'Berjalan') {
                      echo '<span class="jxb-status-badge is-info">Berjalan</span>';
                    } else {
                      echo '<span class="jxb-status-badge is-success">Selesai</span>';
                    }
                  ?></td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <table class="table table-bordered">
                <tr>
                  <th width="220">Surat Tugas</th>
                  <td><?= htmlspecialchars($audit['nomor_surat_tugas']) ?></td>
                </tr>
                <tr>
                  <th>Tanggal ST</th>
                  <td><?= htmlspecialchars($audit['tanggal_surat_tugas']) ?></td>
                </tr>
                <tr>
                  <th>Tanggal Mulai</th>
                  <td><?= htmlspecialchars($audit['tanggal_mulai']) ?></td>
                </tr>
                <tr>
                  <th>Tanggal Selesai</th>
                  <td><?= htmlspecialchars($audit['tanggal_selesai']) ?></td>
                </tr>
                <tr>
                  <th>Estimasi Hari</th>
                  <td><?= htmlspecialchars($audit['estimasi_hari']) ?> hari</td>
                </tr>
                <tr>
                  <th>Tahun Audit</th>
                  <td><?= htmlspecialchars($audit['tahun_audit']) ?></td>
                </tr>
              </table>
            </div>
          </div>
          <hr>
          <h5>Ruang Lingkup Audit</h5>
          <div class="alert alert-light"><?= nl2br(htmlspecialchars($audit['ruang_lingkup'])) ?></div>


          <div class="mt-3">
            <?php if ($audit['status'] != 'Selesai'): ?>
              <a href="close.php?id=<?= $audit['id'] ?>" class="btn btn-primary btn-attention btn-close-audit">
                <i class="fas fa-check-circle"></i>Tutup Audit</a>
            <?php else: ?>
              <button class="btn btn-secondary" disabled>
                <i class="fas fa-lock"></i>Audit Sudah Ditutup</button>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <!-- MENU AKSI -->
      <div class="row">
        <div class="col-md-4">
          <div class="card">
            <div class="card-body text-center">
              <h5>Tim Audit</h5>
              <p>Kelola anggota tim audit</p>
              <a href="../audit_tim/index.php?audit_id=<?= $audit['id'] ?>" class="btn btn-primary">Buka</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body text-center">
              <h5>Lampiran</h5>
              <p>Dokumen audit</p>
              <a href="../audit_lampiran/index.php?audit_id=<?= $audit['id'] ?>" class="btn btn-success">Buka</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-body text-center">
              <h5>Temuan Audit</h5>
              <p>Hasil pemeriksaan</p>
              <a href="../audit_temuan/index.php?audit_id=<?= $audit['id'] ?>" class="btn btn-warning">Buka</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
  document.querySelectorAll('.btn-close-audit').forEach(function (btn) {
    btn.addEventListener(
      'click',
      function (e) {
        e.preventDefault();
        let url =
          this.href;
        let auditId =
          <?= $audit['id'] ?>;
        fetch('check_close.php?id=' + auditId)
          .then(r => r.json())
          .then(data => {
            if (!data.ok) {
              Swal.fire({
                title: 'Tidak Dapat Menutup Audit',
                text: data.message,
                icon: 'error'
              });
              return;
            }
            Swal.fire({
              title:
                'Tutup Audit?',
              text:
                'Audit yang sudah ditutup tidak boleh diubah lagi.',
              icon:
                'warning',
              showCancelButton:
                true,
              confirmButtonText:
                'Ya, Tutup Audit',
              cancelButtonText:
                'Batal'
            }).then((result) => {
              if (result.isConfirmed) {
                window.location =
                  url;
              }
            });
          });
      });
  });
</script>

<?php if (isset($_SESSION['error'])): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: '<?= addslashes($_SESSION['error']) ?>'
    });
  </script>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: '<?= addslashes($_SESSION['success']) ?>',
      timer: 2500,
      showConfirmButton: false
    });
  </script>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php
include "../templates/footer.php";
?>