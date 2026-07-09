<aside
class="app-sidebar bg-body-secondary shadow"
data-bs-theme="dark">

<div class="sidebar-brand">

<a href="../dashboard/index.php"
class="nav-link">

<span class="brand-text fw-light">

SITLP

</span>

</a>

</div>

<!-- menu + user -->

<div class="sidebar-wrapper">

<div class="sidebar-user text-center py-3">

    <img
        src="/sitlp/assets/images/default-user.png"
        alt="User"
        class="img-circle elevation-2"
        style="width:80px;height:80px;object-fit:cover;">

    <div class="mt-2 text-white">

        <strong>
            <?= htmlspecialchars($_SESSION['nama']) ?>
        </strong>

    </div>

    <small class="text-secondary">

        <?= htmlspecialchars($_SESSION['role']) ?>

    </small>

</div>

<nav class="mt-2">

<ul
class="nav sidebar-menu flex-column"
data-lte-toggle="treeview"
role="menu">

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI','DIREKSI'])): ?>
<li class="nav-item">
<a href="../dashboard_spi/index.php" class="nav-link">
<i class="nav-icon fas fa-chart-line"></i>
<p>Dashboard Monitoring SPI</p>
</a>
</li>
<?php endif; ?>

<?php if($_SESSION['role']=='AUDITOR'): ?>
<li class="nav-item">
<a href="../dashboard/index.php" class="nav-link">
<i class="nav-icon fas fa-home"></i>
<p>Dashboard</p>
</a>
</li>
<?php endif; ?>

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI'])): ?>
<li class="nav-item">
<a href="../audit_program/index.php" class="nav-link">
<i class="nav-icon fas fa-calendar-alt"></i>
<p>Program Audit (PAT)</p>
</a>
</li>
<?php endif; ?>

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI','AUDITOR'])): ?>
<li class="nav-item">
<a href="../audit_pemeriksaan/index.php" class="nav-link">
<i class="nav-icon fas fa-clipboard-check"></i>
<p>Pemeriksaan Audit</p>
</a>
</li>
<?php endif; ?>

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI','DIREKSI'])): ?>
<li class="nav-item">
<a href="../audit_tindak_lanjut/index.php" class="nav-link">
<i class="nav-icon fas fa-tasks"></i>
<p>Monitoring Tindak Lanjut</p>
</a>
</li>
<?php endif; ?>

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI'])): ?>
<li class="nav-item">
<a href="../audit_log/index.php" class="nav-link">
<i class="nav-icon fas fa-history"></i>
<p>LOG Audit Trail</p>
</a>
</li>
<?php endif; ?>

<?php if($_SESSION['role']=='ADMIN'): ?>
<li class="nav-item">
<a href="../users/index.php" class="nav-link">
<i class="nav-icon fas fa-users"></i>
<p>Master User</p>
</a>
</li>
<li class="nav-item">
<a href="../unit_kerja/index.php" class="nav-link">
<i class="nav-icon fas fa-building"></i>
<p>Unit Kerja</p>
</a>
</li>
<li class="nav-item">
<a href="../auditor/index.php" class="nav-link">
<i class="nav-icon fas fa-user-shield"></i>
<p>Auditor</p>
</a>
</li>
<?php endif; ?>

<!-- AUDITEE -->
<?php if($_SESSION['role']=='AUDITEE'): ?>
<li class="nav-item">
    <a href="../dashboard/index.php" class="nav-link">
        <i class="nav-icon fas fa-home"></i>
        <p>Dashboard</p>
    </a>
</li>
<li class="nav-item">
    <a href="../auditee_temuan/index.php" class="nav-link">
        <i class="nav-icon fas fa-tasks"></i>
        <p>Tindak Lanjut Saya</p>
    </a>
</li>
<?php endif; ?>

<?php if(in_array($_SESSION['role'], ['ADMIN','KEPALA_SPI','DIREKSI'])): ?>
<li class="nav-item">
    <a href="../report" class="nav-link">
        <i class="nav-icon fas fa-file-excel"></i>
        <p>Export Excel</p>
    </a>
</li>
<?php endif; ?>

<li class="nav-item">
<a href="../auth/logout.php" class="nav-link">
<i class="nav-icon fas fa-sign-out-alt"></i>
<p>Logout</p></a>
</li>

</ul>

</nav>

</div>

</aside>
