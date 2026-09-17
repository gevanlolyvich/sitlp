<?php
$_SERVER['SCRIPT_NAME'] = '/sisia/audit_tindak_lanjut/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/sisia/audit_tindak_lanjut/index.php';
$_GET = [];
$_SESSION = ['role' => 'ADMIN', 'nama' => 'Admin Test', 'id' => 1];
ob_start();
include 'audit_tindak_lanjut/index.php';
$html = ob_get_clean();
$checks = [
    'has_unit_kerja_header' => strpos($html, '>Unit Kerja<') !== false,
    'no_pic_header'         => strpos($html, '>PIC<') === false,
    'has_tl_subtext'        => strpos($html, 'judul_temuan') !== false || preg_match('/<small class="text-muted d-block"/', $html) === 1,
    'has_no_parsed_error'   => strpos($html, 'Fatal error') === false && strpos($html, 'Warning:') === false,
];
foreach ($checks as $k => $v) { echo ($v ? 'PASS ' : 'FAIL ') . $k . "\n"; }
if (!$checks['has_no_parsed_error']) { echo substr(strip_tags($html), 0, 500); }