<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/*
|--------------------------------------------------------------------------
| Pengambilan data (batch, tanpa N+1)
|--------------------------------------------------------------------------
*/
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$where = '';
if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where = "WHERE p.kode_program LIKE '%$kw%' OR p.judul_program LIKE '%$kw%'";
}

$programs = [];
$q = mysqli_query($conn, "
    SELECT p.*, u.nama_unit, a.nama_auditor AS penanggung_jawab
    FROM audit_program p
    LEFT JOIN unit_kerja u ON p.unit_id = u.id
    LEFT JOIN auditor a ON p.penanggung_jawab_id = a.id
    $where
    ORDER BY p.tahun, p.kode_program
");
while ($d = mysqli_fetch_assoc($q)) {
    $d['pemeriksaan']  = [];
    $d['auditor_ids']  = [];
    $programs[$d['id']] = $d;
}

if (count($programs) > 0) {
    $ids = implode(',', array_map('intval', array_keys($programs)));

    $q = mysqli_query($conn, "
        SELECT ap.id, ap.program_id, ap.ketua_auditor_id, ap.tanggal_mulai, ap.tanggal_selesai
        FROM audit_pemeriksaan ap
        WHERE ap.program_id IN ($ids)
        ORDER BY ap.tanggal_mulai
    ");
    while ($d = mysqli_fetch_assoc($q)) {
        $programs[$d['program_id']]['pemeriksaan'][] = $d;
    }

    $q = mysqli_query($conn, "
        SELECT ap.program_id, t.auditor_id
        FROM audit_tim t
        JOIN audit_pemeriksaan ap ON t.audit_id = ap.id
        WHERE ap.program_id IN ($ids)
    ");
    while ($d = mysqli_fetch_assoc($q)) {
        $programs[$d['program_id']]['auditor_ids'][$d['auditor_id']] = true;
    }

    $q = mysqli_query($conn, "
        SELECT ap.program_id, COUNT(*) total
        FROM audit_temuan tm
        JOIN audit_pemeriksaan ap ON tm.audit_id = ap.id
        WHERE ap.program_id IN ($ids)
        GROUP BY ap.program_id
    ");
    while ($d = mysqli_fetch_assoc($q)) {
        $programs[$d['program_id']]['jml_laporan'] = (int)$d['total'];
    }
}

/*
|--------------------------------------------------------------------------
| Bulan aktif (X) dari rentang tanggal mulai - selesai
|--------------------------------------------------------------------------
*/
function markBulanRentang($tglMulai, $tglSelesai, &$bulanSet)
{
    if (empty($tglMulai) || empty($tglSelesai)) {
        return;
    }
    $mulai = strtotime($tglMulai);
    $selesai = strtotime($tglSelesai);
    if (!$mulai || !$selesai || $mulai > $selesai) {
        return;
    }

    $iter = new DateTime(date('Y-m-d', $mulai));
    $akhir = new DateTime(date('Y-m-d', $selesai));
    $iter->modify('first day of this month');
    while ($iter <= $akhir) {
        $bulanSet[(int)$iter->format('n')] = true;
        $iter->modify('+1 month');
    }
}

/*
|--------------------------------------------------------------------------
| Spreadsheet
|--------------------------------------------------------------------------
*/
$tahunProgram = [];
foreach ($programs as $p) {
    $tahunProgram[(int)$p['tahun']] = true;
}
ksort($tahunProgram);
$tahunLabel = implode(' & ', array_map('strval', array_keys($tahunProgram)));

$colCount = 19; // A..S : No, Objek, Tujuan, Jenis, HP, JmlAud, JmlLap, Bulan(1..12)
$lastCol = 'S';
$spreadsheet = new Spreadsheet();
$sheet2 = $spreadsheet->getActiveSheet();
$sheet2->setTitle('PKPT');

$colWidths = [
    'A'=>6, 'B'=>40, 'C'=>50, 'D'=>16, 'E'=>8,
    'F'=>12, 'G'=>12, 'H'=>6, 'I'=>6, 'J'=>6,
    'K'=>6, 'L'=>6, 'M'=>6, 'N'=>6, 'O'=>6,
    'P'=>6, 'Q'=>6, 'R'=>6, 'S'=>6
];
foreach ($colWidths as $col => $w) {
    $sheet2->getColumnDimension($col)->setWidth($w);
}

// Judul
$sheet2->mergeCells("A1:{$lastCol}1");
$sheet2->setCellValue('A1', 'PROGRAM KERJA PENGAWASAN TAHUNAN (PKPT)');
$sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$sheet2->mergeCells("A2:{$lastCol}2");
$sheet2->setCellValue('A2', 'MATRIKS PELAKSANAAN AUDIT  TAHUN ' . ($tahunLabel ? $tahunLabel : 'SELURUH TAHUN'));
$sheet2->getStyle('A2')->getFont()->setBold(true)->setSize(12);

// ============================================================
// Header (baris 5-6)
// ============================================================
$sheet2->mergeCells('A5:A6');   // No
$sheet2->mergeCells('B5:B6');   // Objek Audit
$sheet2->mergeCells('C5:C6');   // Tujuan Audit
$sheet2->mergeCells('D5:D6');   // Jenis Audit
$sheet2->mergeCells('E5:E6');   // HP
$sheet2->mergeCells('F5:F6');   // Jml Auditor
$sheet2->mergeCells('G5:G6');   // Jml Laporan
$sheet2->mergeCells('H5:S5');   // Bulan
for ($m = 1; $m <= 12; $m++) {
    $colBulan = Coordinate::stringFromColumnIndex(7 + $m);
    $sheet2->setCellValue($colBulan . '6', $m);
}

$sheet2->setCellValue('A5', 'No');
$sheet2->setCellValue('B5', 'Objek Audit');
$sheet2->setCellValue('C5', 'Tujuan Audit');
$sheet2->setCellValue('D5', 'Jenis Audit');
$sheet2->setCellValue('E5', 'HP');
$sheet2->setCellValue('F5', 'Jml\nAuditor');
$sheet2->setCellValue('G5', 'Jml\nLaporan');
$sheet2->setCellValue('H5', 'Bulan');

$thin = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];

$sheet2->getStyle("A5:{$lastCol}5")->getFont()->setBold(true);
$sheet2->getStyle("A5:{$lastCol}6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet2->getStyle("A5:{$lastCol}6")->applyFromArray($thin);

// ============================================================
// Data
// ============================================================
$r = 7;
$no = 1;

foreach ($programs as $p) {
    $bulanSet = [];
    $jmlAuditorSet = isset($p['auditor_ids']) ? $p['auditor_ids'] : [];

    foreach ($p['pemeriksaan'] as $pk) {
        if (!empty($pk['ketua_auditor_id'])) {
            $jmlAuditorSet[(int)$pk['ketua_auditor_id']] = true;
        }
        markBulanRentang($pk['tanggal_mulai'], $pk['tanggal_selesai'], $bulanSet);
    }

    $jmlAuditor = count($jmlAuditorSet);
    $jmlLaporan = isset($p['jml_laporan']) ? $p['jml_laporan'] : 0;

    $sheet2->setCellValue('A'.$r, $no);
    $sheet2->setCellValue('B'.$r, $p['judul_program']);
    $sheet2->setCellValue('C'.$r, $p['keterangan']);
    $sheet2->setCellValue('D'.$r, $p['jenis_audit']);
    $sheet2->setCellValue('E'.$r, (int)$p['estimasi_hari']);
    $sheet2->setCellValue('F'.$r, $jmlAuditor);
    $sheet2->setCellValue('G'.$r, $jmlLaporan);
    for ($m = 1; $m <= 12; $m++) {
        $colBulan = Coordinate::stringFromColumnIndex(7 + $m);
        $sheet2->setCellValue($colBulan . $r, isset($bulanSet[$m]) ? 'X' : '');
    }

    $sheet2->getRowDimension($r)->setRowHeight(-1);
    $no++;
    $r++;
}

$last = $r - 1;
if ($last >= 7) {
    $sheet2->getStyle("A7:{$lastCol}{$last}")->applyFromArray($thin);
}
$sheet2->getStyle("A7:{$lastCol}{$last}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
$sheet2->getStyle("A7:A{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle("E7:G{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle("H7:S{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle("B7:C{$last}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

// Header fill
$sheet2->getStyle("A5:{$lastCol}6")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9E2F3');

// ============================================================
// Log aktivitas
// ============================================================
if (function_exists('logActivity')) {
    logActivity($conn, 'Export matriks PKPT ke Excel' . ($keyword !== '' ? " (filter: $keyword)" : ''), 'audit_program');
}

/*
|--------------------------------------------------------------------------
| Output
|--------------------------------------------------------------------------
*/
while (ob_get_level() > 0) { ob_end_clean(); }

$fileName = 'Matrik_PKPT_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;