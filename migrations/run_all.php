<?php
/**
 * SISIA - Migration Runner (satu-klik)
 * ---------------------------------------------------------------
 * Menjalankan migrasi database yang belum terpasang secara otomatis:
 *   - Mendeteksi kondisi skema DB saat ini (BUKAN menempel pada tabel versi)
 *   - Menjalankan file SQL di migrations/ urut versi naik (v2.0.0 -> v2.8.0)
 *   - Aman dijalankan berulang (idempotent, deteksi mencegah run ganda)
 *
 * Cara pakai:
 *   CLI:
 *     php migrations/run_all.php --check   # cek saja, tanpa mengubah DB
 *     php migrations/run_all.php           # jalankan migrasi yang tersisa
 *     php migrations/run_all.php --force   # termasuk v2.7.0 (DESTRUKTIF)
 *   Web (harus login sebagai ADMIN):
 *     migrations/run_all.php
 *
 * CATATAN:
 *   - v2.7.0 bersifat DESTRUKTIF (mengosongkan data lhp) -> wajib konfirmasi.
 *   - Untuk server baru / DB kosong: impor dump penuh DB yang sudah final
 *     adalah cara yang direkomendasikan; runner ini untuk DB lama/incremental.
 *   - Pastikan config/database.php sudah menunjuk DB target di server.
 */

$IS_CLI = (PHP_SAPI === 'cli');

if ($IS_CLI) {
    $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
    $MODE_CHECK = in_array('--check', $argv, true);
    $MODE_FORCE = in_array('--force', $argv, true);
    $cliDb = null;
    foreach ($argv as $arg) {
        if (stripos($arg, '--db=') === 0) {
            $cliDb = substr($arg, 5);
        }
    }
} else {
    session_start();
    require_once dirname(__DIR__) . '/config/app.php';
    require_once dirname(__DIR__) . '/auth/check.php';
    require_once dirname(__DIR__) . '/auth/role.php';
    checkRole(array('ADMIN'));

    $action = isset($_POST['action']) ? $_POST['action'] : 'view';
    $MODE_CHECK = false;
    $MODE_FORCE = ($action === 'run' && isset($_POST['confirm_destructive']));
}

require_once dirname(__DIR__) . '/config/database.php';

// mysql_error terkendali (kembalikan false, bukan exception)
mysqli_report(MYSQLI_REPORT_OFF);

if ($IS_CLI && $cliDb !== null) {
    $db = $cliDb;
    $conn = mysqli_connect($host, $user, $pass, $db);
    if (!$conn) {
        die("Koneksi database gagal ($db)");
    }
}

/* ================================================================
 * Helper deteksi skema
 * ============================================================== */
function db_table_exists($conn, $table)
{
    $q = mysqli_query($conn, "SHOW TABLES LIKE '" . mysqli_real_escape_string($conn, $table) . "'");
    return $q !== false && mysqli_num_rows($q) > 0;
}

function db_column_exists($conn, $table, $column)
{
    $q = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '" . mysqli_real_escape_string($conn, $column) . "'");
    return $q !== false && mysqli_num_rows($q) > 0;
}

function db_column_type($conn, $table, $column)
{
    $q = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '" . mysqli_real_escape_string($conn, $column) . "'");
    if ($q === false || mysqli_num_rows($q) === 0) {
        return null;
    }
    $row = mysqli_fetch_assoc($q);
    return isset($row['Type']) ? (string) $row['Type'] : null;
}

function db_enum_contains($conn, $table, $column, $value)
{
    $type = db_column_type($conn, $table, $column);
    if ($type === null) {
        return false;
    }
    return strpos($type, "'" . $value . "'") !== false;
}

function db_enum_title_only($conn, $table, $column, $value)
{
    $type = db_column_type($conn, $table, $column);
    if ($type === null) {
        return false;
    }
    return strpos($type, "'" . $value . "'") !== false
        && strpos($type, "'" . strtoupper($value) . "'") === false;
}

function tl_uppercase_left($conn)
{
    $list = "'SESUAI','SELESAI','PROSES','BELUM SESUAI','BELUM DITINDAK LANJUT','TIDAK DAPAT DITINDAK LANJUT'";
    if (db_table_exists($conn, 'audit_tindak_lanjut')) {
        $q = mysqli_query($conn, "SELECT 1 FROM audit_tindak_lanjut WHERE BINARY status IN ($list) LIMIT 1");
        if ($q !== false && mysqli_num_rows($q) > 0) {
            return true;
        }
    }
    if (db_table_exists($conn, 'audit_tindak_lanjut_log')) {
        $q = mysqli_query($conn, "SELECT 1 FROM audit_tindak_lanjut_log WHERE BINARY status_baru IN ($list) OR BINARY status_lama IN ($list) LIMIT 1");
        if ($q !== false && mysqli_num_rows($q) > 0) {
            return true;
        }
    }
    return false;
}

function lhp_old_status_left($conn)
{
    $q = mysqli_query($conn, "SELECT 1 FROM lhp_tl WHERE status IN ('Belum Ditindaklanjuti','Tidak Dapat Ditindaklanjuti') LIMIT 1");
    return $q !== false && mysqli_num_rows($q) > 0;
}

/* ================================================================
 * Daftar migrasi (urut versi naik)
 * ============================================================== */
$MIGRATIONS = array(
    array(
        'version'    => 'v2.0.0',
        'file'       => 'v2.0.0_audit_enhancements.sql',
        'title'      => 'Audit System Enhancements',
        'desc'       => 'jenis audit & peran tim jadi VARCHAR, estimasi hari, catatan SPI, status TL baru',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.1.0',
        'file'       => 'v2.1.0_tindak_lanjut_history.sql',
        'title'      => 'Tindak Lanjut History & Workflow',
        'desc'       => 'status TL jadi VARCHAR, tabel log audit_tindak_lanjut_log',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.2.0',
        'file'       => 'v2.2.0_nilai_penyerahan.sql',
        'title'      => 'Nilai Penyerahan',
        'desc'       => 'kolom nilai_penyerahan pada audit_tindak_lanjut & log',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.3.0',
        'file'       => 'v2.3.0_role_komisaris.sql',
        'title'      => 'Role KOMISARIS',
        'desc'       => 'tambah role KOMISARIS pada users.role',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.3.1',
        'file'       => 'v2.3.1_status_normalization.sql',
        'title'      => 'Normalisasi status (camelCase)',
        'desc'       => 'normalisasi status TL uppercase -> camelCase (jaring pengaman)',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.3.2',
        'file'       => 'v2.3.2_status_title_case.sql',
        'title'      => 'Normalisasi status/risiko Title Case',
        'desc'       => 'ENUM status & risiko jadi Title Case (program/pemeriksaan/temuan)',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.4.0',
        'file'       => 'v2.4.0_tl_updated_at.sql',
        'title'      => 'updated_at Tindak Lanjut',
        'desc'       => 'kolom updated_at auto pada audit_tindak_lanjut',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.5.0',
        'file'       => 'v2.5.0_lhp_unit.sql',
        'title'      => 'Unit pada TL LHP',
        'desc'       => 'kolom lhp.unit_id + FK (dirawat v2.7.0)',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.6.0',
        'file'       => 'v2.6.0_role_komite_audit.sql',
        'title'      => 'Role KOMITE_AUDIT',
        'desc'       => 'tambah role KOMITE_AUDIT pada users.role',
        'destructive'=> false,
    ),
    array(
        'version'    => 'v2.7.0',
        'file'       => 'v2.7.0_lhp_rekomendasi_tl.sql',
        'title'      => 'TL LHP Multiple (Rekomendasi/TL/Unit/Bukti)',
        'desc'       => 'MENGOSONGKAN data lhp + tabel anak lhp_rekomendasi/lhp_tl/lhp_tl_bukti/lhp_unit',
        'destructive'=> true,
    ),
    array(
        'version'    => 'v2.8.0',
        'file'       => 'v2.8.0_lhp_status.sql',
        'title'      => 'Status TL LHP disamakan dengan SPI',
        'desc'       => 'rename status "Ditindaklanjuti" -> "Ditindak Lanjut"',
        'destructive'=> false,
    ),
);

$DETECT = array(
    'v2.0.0' => function () use ($conn) { return db_column_exists($conn, 'audit_pemeriksaan', 'estimasi_hari'); },
    'v2.1.0' => function () use ($conn) { return db_table_exists($conn, 'audit_tindak_lanjut_log'); },
    'v2.2.0' => function () use ($conn) { return db_column_exists($conn, 'audit_tindak_lanjut', 'nilai_penyerahan'); },
    'v2.3.0' => function () use ($conn) { return db_enum_contains($conn, 'users', 'role', 'KOMISARIS'); },
    'v2.3.1' => function () use ($conn) { return !tl_uppercase_left($conn); },
    'v2.3.2' => function () use ($conn) { return db_enum_title_only($conn, 'audit_program', 'status', 'Rencana'); },
    'v2.4.0' => function () use ($conn) { return db_column_exists($conn, 'audit_tindak_lanjut', 'updated_at'); },
    'v2.5.0' => function () use ($conn) { return db_column_exists($conn, 'lhp', 'unit_id') || db_table_exists($conn, 'lhp_rekomendasi'); },
    'v2.6.0' => function () use ($conn) { return db_enum_contains($conn, 'users', 'role', 'KOMITE_AUDIT'); },
    'v2.7.0' => function () use ($conn) { return db_table_exists($conn, 'lhp_rekomendasi'); },
    'v2.8.0' => function () use ($conn) {
        if (!db_table_exists($conn, 'lhp_tl')) {
            return true; // modul LHP multiple belum ada -> tidak ada yang perlu di-update
        }
        return !lhp_old_status_left($conn);
    },
);

/* ================================================================
 * Eksekusi SQL
 * ============================================================== */
function db_split_sql($sql)
{
    $lines = preg_split('/\R/', $sql);
    $stmts = array();
    $buf   = '';
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '--') === 0) {
            continue;
        }
        $buf .= ($buf === '' ? '' : ' ') . $line;
        if (substr(rtrim($line), -1) === ';') {
            $stmts[] = $buf;
            $buf     = '';
        }
    }
    if (trim($buf) !== '') {
        $stmts[] = $buf;
    }
    return $stmts;
}

function db_execute_file($conn, $path)
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        return "tidak dapat membaca file: $path";
    }
    foreach (db_split_sql($sql) as $stmt) {
        if (mysqli_query($conn, $stmt) === false) {
            $short = mb_substr(preg_replace('/\s+/', ' ', $stmt), 0, 220);
            return mysqli_error($conn) . "\n  Statement: " . $short;
        }
    }
    return null;
}

/* ================================================================
 * Alur utama
 * ============================================================== */
$runMode = $IS_CLI ? !$MODE_CHECK : ($action === 'run');

if ($runMode) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS schema_migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        version VARCHAR(20) NOT NULL,
        filename VARCHAR(255) NOT NULL,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_version (version)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

$counts = array('applied' => 0, 'pending' => 0, 'ok' => 0, 'fail' => 0, 'blocked' => 0, 'skipped' => 0);
$rows   = array();
$stopped = false;

foreach ($MIGRATIONS as $m) {
    $ver     = $m['version'];
    $detect  = isset($DETECT[$ver]) ? $DETECT[$ver] : null;
    $applied = $detect ? $detect() : false;
    $status  = 'applied';
    $note    = '';

    if (!$applied) {
        if ($stopped) {
            $status = 'skipped';
            $counts['skipped']++;
        } elseif ($m['destructive'] && !$MODE_FORCE) {
            $status = 'blocked';
            $note   = 'DESTRUKTIF - butuh konfirmasi (CLI: --force / web: centang konfirmasi)';
            $counts['blocked']++;
        } elseif ($runMode) {
            $err = db_execute_file($conn, __DIR__ . '/' . $m['file']);
            if ($err === null) {
                $status = 'ok';
                $note   = 'berhasil dijalankan (' . date('Y-m-d H:i:s') . ')';
                $counts['ok']++;
                mysqli_query(
                    $conn,
                    "INSERT IGNORE INTO schema_migrations (version, filename) VALUES ('" .
                    mysqli_real_escape_string($conn, $ver) . "', '" .
                    mysqli_real_escape_string($conn, $m['file']) . "')"
                );
            } else {
                $status = 'fail';
                $note   = $err;
                $counts['fail']++;
                $stopped = true; // berhenti pada error pertama, sisa DILEWAT
            }
        } else {
            $status = 'pending';
            $note   = 'belum terpasang - akan dijalankan';
            $counts['pending']++;
        }
    } else {
        $counts['applied']++;
    }

    $rows[] = array('m' => $m, 'status' => $status, 'note' => $note);
}

$dbName = isset($db) ? $db : '';
$dbHost = isset($host) ? $host : '';

/* ================================================================
 * Output
 * ============================================================== */
if ($IS_CLI) {
    cli_render($rows, $counts, $dbName, $dbHost, $MODE_CHECK, $MODE_FORCE);
    exit($counts['fail'] > 0 ? 1 : 0);
}

web_render($rows, $counts, $dbName, $dbHost, $action, $MODE_FORCE);

/* ================================================================
 * Render CLI
 * ============================================================== */
function cli_render($rows, $counts, $dbName, $dbHost, $modeCheck, $modeForce)
{
    echo "==============================================\n";
    echo " SISIA Migration Runner\n";
    echo " DB  : $dbName @ $dbHost\n";
    echo " Mode: " . ($modeCheck ? "cek saja (--check)" : "jalankan") .
         ($modeForce ? " [--force: destruktif diizinkan]" : "") . "\n";
    echo "==============================================\n";

    foreach ($rows as $r) {
        $m  = $r['m'];
        $tag = str_pad(strtoupper($r['status']), 8, ' ', STR_PAD_LEFT);
        $line = "[$tag] {$m['version']} {$m['title']}";
        if ($r['note'] !== '') {
            $line .= " - " . $r['note'];
        }
        echo $line . "\n";
    }

    echo "\nRingkasan: sudah={$counts['applied']}, belum={$counts['pending']}, "
        . "berhasil={$counts['ok']}, gagal={$counts['fail']}, "
        . "butuh-konfirmasi={$counts['blocked']}, dilewat={$counts['skipped']}\n";
}

/* ================================================================
 * Render Web
 * ============================================================== */
function web_render($rows, $counts, $dbName, $dbHost, $action, $modeForce)
{
    $badge = array(
        'applied' => array('#1d7a33', 'SUDAH DIPASANG'),
        'pending' => array('#a06a00', 'BELUM DIPASANG'),
        'ok'      => array('#1d7a33', 'BERHASIL DIJALANKAN'),
        'fail'    => array('#b3261e', 'GAGAL'),
        'blocked' => array('#c0392b', 'BUTUH KONFIRMASI'),
        'skipped' => array('#6c757d', 'DILEWAT'),
    );

    $hasPendingDestructive = false;
    $hasFail = $counts['fail'] > 0;
    foreach ($rows as $r) {
        if ($r['status'] === 'blocked') {
            $hasPendingDestructive = true;
        }
    }

    $h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };

    $msg = '';
    if ($action === 'run' && !$modeForce && $hasPendingDestructive) {
        $msg = '<div class="alert alert-danger">Migrasi tidak dijalankan. Centang konfirmasi untuk mengizinkan migrasi yang bersifat DESTRUKTIF (v2.7.0 mengosongkan data LHP).</div>';
    } elseif ($action === 'run' && $modeForce) {
        $msg = '<div class="alert alert-success">Migrasi telah diproses. Periksa status di bawah.</div>';
    } elseif ($action === 'check') {
        $msg = '<div class="alert alert-info">Mode cek: tidak ada perubahan pada database.</div>';
    }

    $table = '';
    foreach ($rows as $r) {
        $m     = $r['m'];
        $b     = $badge[$r['status']];
        $destr = $m['destructive'] ? ' <span class="tag-dest">DESTRUKTIF</span>' : '';
        $note  = $r['note'] !== '' ? '<div class="note">' . nl2br($h($r['note'])) . '</div>' : '';
        $table .= '<tr>'
            . '<td><code>' . $h($m['version']) . '</code></td>'
            . '<td><strong>' . $h($m['title']) . '</strong>' . $destr
            . '<div class="muted">' . $h($m['desc']) . '</div></td>'
            . '<td><span class="badge" style="background:' . $b[0] . '">' . $b[1] . '</span>' . $note . '</td>'
            . '</tr>';
    }

    $summary = 'Sudah terpasang: <b>' . $counts['applied'] . '</b> &middot; '
        . 'Belum: <b>' . $counts['pending'] . '</b> &middot; '
        . 'Berhasil dijalankan: <b>' . $counts['ok'] . '</b> &middot; '
        . 'Gagal: <b>' . $counts['fail'] . '</b> &middot; '
        . 'Butuh konfirmasi: <b>' . $counts['blocked'] . '</b> &middot; '
        . 'Dilewat: <b>' . $counts['skipped'] . '</b>';

    $confirmBlock = '<label class="confirm">'
        . '<input type="checkbox" name="confirm_destructive" value="1"> '
        . 'Saya sudah backup dan paham bahwa <b>v2.7.0 mengosongkan seluruh data LHP</b>.'
        . '</label>';

    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>Migrasi Database - SISIA</title><style>'
        . 'body{font-family:Segoe UI,Arial,sans-serif;background:#f4f6f8;color:#1c2733;margin:0;padding:24px}'
        . '.wrap{max-width:860px;margin:0 auto}'
        . '.card{background:#fff;border:1px solid #e2e6ea;border-radius:10px;padding:20px 24px;margin-bottom:16px}'
        . 'h1{font-size:20px;margin:0 0 4px}h2{font-size:15px;margin:18px 0 8px}'
        . '.muted{color:#6c757d;font-size:12px;margin-top:2px}'
        . '.alert{padding:12px 14px;border-radius:8px;font-size:14px;margin-bottom:14px}'
        . '.alert-danger{background:#fdecea;color:#8f1d15;border:1px solid #f5c6c2}'
        . '.alert-success{background:#eaf6ee;color:#13642c;border:1px solid #b8e3c4}'
        . '.alert-info{background:#e9f2fb;color:#1d4f91;border:1px solid #bcdcf5}'
        . 'table{width:100%;border-collapse:collapse;font-size:13px}'
        . 'th{text-align:left;padding:8px 10px;background:#eef1f4;color:#445;text-transform:uppercase;font-size:11px;letter-spacing:.05em}'
        . 'td{padding:10px;border-top:1px solid #edf0f3;vertical-align:top}'
        . 'code{background:#eff2f5;padding:1px 6px;border-radius:4px;font-size:12px}'
        . '.badge{display:inline-block;color:#fff;font-size:10px;font-weight:600;padding:3px 8px;border-radius:10px;letter-spacing:.03em}'
        . '.tag-dest{background:#c0392b;color:#fff;font-size:10px;padding:2px 7px;border-radius:9px;margin-left:6px}'
        . '.note{margin-top:6px;color:#8a1010;font-size:12px;background:#fdf3f2;padding:6px 8px;border-radius:6px}'
        . '.row{display:flex;gap:10px;margin-top:16px;flex-wrap:wrap}'
        . 'button{border:0;border-radius:8px;padding:10px 18px;font-size:14px;cursor:pointer;font-weight:600}'
        . '.btn-run{background:#1d7a33;color:#fff}.btn-run:hover{background:#155b26}'
        . '.btn-check{background:#55606c;color:#fff}.btn-check:hover{background:#414b55}'
        . '.btn-dash{background:#fff;color:#1d7a33;border:1px solid #1d7a33}'
        . '.confirm{display:block;background:#fdf3f2;border:1px solid #f0cbc8;border-radius:8px;padding:10px 12px;font-size:13px;margin-top:12px}'
        . '.confirm b{color:#8f1d15}'
        . '.btn-dash2{display:inline-block}'
        . '.meta{color:#6c757d;font-size:12px}'
        . '</style></head><body><div class="wrap">'
        . '<div class="card"><h1>Migrasi Database SISIA</h1>'
        . '<div class="meta">DB target: ' . $h($dbName) . ' @ ' . $h($dbHost) . '</div></div>'
        . $msg
        . '<div class="card"><h2>Status Migrasi</h2>'
        . '<div class="meta" style="margin-bottom:10px">' . $summary . '</div>'
        . '<table><thead><tr><th>Versi</th><th>Migrasi</th><th>Status</th></tr></thead><tbody>'
        . $table . '</tbody></table>'
        . '<form method="post">'
        . '<div class="row">'
        . '<button type="submit" name="action" value="check" class="btn-check">Cek Saja</button>'
        . '<button type="submit" name="action" value="run" class="btn-run"' . ($hasFail ? ' disabled' : '') . '>Jalankan Migrasi</button>'
        . '</div>'
        . $confirmBlock
        . '</form>'
        . '<div class="row"><a class="btn-dash btn-dash2" href="../dashboard/" style="display:inline-block;text-decoration:none;padding:10px 18px;border-radius:8px">Kembali ke Dashboard</a></div>'
        . '</div></div></body></html>';
}