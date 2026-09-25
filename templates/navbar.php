<header class="app-header navbar navbar-expand">

    <div class="container-fluid">

        <ul class="navbar-nav">

            <li class="nav-item">

                <a class="nav-link" data-lte-toggle="sidebar" href="#">

                    <i class="fas fa-bars"></i>

                </a>

            </li>

        </ul>
        <ul class="navbar-nav app-title-nav">
            <li class="nav-item">
                <span class="nav-link app-title">
                    Sistem Informasi Satuan Internal Audit
                </span>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <?php
                    $nama = $_SESSION['nama'] ?? 'User';
                    $parts = array_filter(array_map('trim', explode(' ', $nama)));
                    $initials = '';
                    if (count($parts) > 1) {
                        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
                    } elseif (count($parts) === 1) {
                        $initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
                    }
                    $roleMap = ['ADMIN' => 'A', 'KEPALA_SIA' => 'K', 'AUDITOR' => 'R', 'AUDITEE' => 'E', 'DIREKSI' => 'D', 'KOMISARIS' => 'C', 'KOMITE_AUDIT' => 'M'];
                    $role = $_SESSION['role'] ?? '';
                    $roleAlias = $roleMap[$role] ?? mb_strtoupper(mb_substr($role, 0, 1));
                ?>
                <span class="nav-link d-inline-flex align-items-center gap-2 py-2">
                    <span class="jxb-avatar"><?= htmlspecialchars($initials) ?></span>
                    <span class="d-none d-md-inline"><?= htmlspecialchars($nama) ?></span>
                    <span class="badge jxb-status-badge is-neutral d-none d-lg-inline" title="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($roleAlias) ?></span>
                </span>
            </li>
        </ul>

    </div>

</header>