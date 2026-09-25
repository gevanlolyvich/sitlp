<?php

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN', 'KEPALA_SIA', 'AUDITOR']);

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$sumber = isset($_GET['sumber']) ? strtoupper(trim($_GET['sumber'])) : 'BPK';
if (!in_array($sumber, ['BPK', 'BPKP', 'KAP'])) {
    $sumber = 'BPK';
}
$judulMap = ['BPK' => 'LHP BPK', 'BPKP' => 'LHP BPKP', 'KAP' => 'LHP KAP'];

$where = "WHERE l.sumber='$sumber'";
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
if ($keyword !== '') {
    $where .= " AND (l.nomor_lhp LIKE '%$keyword%' OR l.judul_temuan LIKE '%$keyword%'
        OR EXISTS(SELECT 1 FROM lhp_rekomendasi r WHERE r.lhp_id=l.id AND r.uraian LIKE '%$keyword%')
        OR EXISTS(SELECT 1 FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.uraian LIKE '%$keyword%'))";
}
$filterUnit = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
if ($filterUnit > 0) {
    $where .= " AND EXISTS(SELECT 1 FROM lhp_unit lu WHERE lu.lhp_id=l.id AND lu.unit_id=$filterUnit)";
}

$q = mysqli_query($conn, "SELECT l.*,
    (SELECT COUNT(*) FROM lhp_rekomendasi r WHERE r.lhp_id=l.id) AS jml_rekomendasi,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id) AS jml_tl,
    (SELECT GROUP_CONCAT(uk.nama_unit ORDER BY uk.nama_unit SEPARATOR ', ') FROM lhp_unit lu JOIN unit_kerja uk ON lu.unit_id=uk.id WHERE lu.lhp_id=l.id) AS nama_unit,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Proses') AS hasil_proses,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Sesuai') AS hasil_sesuai,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Belum Sesuai') AS hasil_belum_sesuai,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Belum Ditindak Lanjut') AS hasil_belum_tl,
    (SELECT COUNT(*) FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id WHERE r.lhp_id=l.id AND t.status='Tidak Dapat Ditindak Lanjut') AS hasil_tidak_tl
    FROM lhp l $where ORDER BY l.tahun DESC, l.id DESC");

$rows = [];
while ($rw = mysqli_fetch_assoc($q)) {
    $rows[] = $rw;
}

$rekMap = [];
$tlMap = [];
$rowIds = array_column($rows, 'id');
if ($rowIds) {
    $in = implode(',', array_map('intval', $rowIds));
    $qRek = mysqli_query($conn, "SELECT lhp_id, no, uraian FROM lhp_rekomendasi WHERE lhp_id IN ($in) ORDER BY lhp_id, no");
    while ($rk = mysqli_fetch_assoc($qRek)) {
        $rekMap[$rk['lhp_id']][] = $rk;
    }
    $qTl = mysqli_query($conn, "SELECT r.lhp_id, r.no AS rek_no, t.no AS tl_no, t.uraian, t.status
        FROM lhp_tl t JOIN lhp_rekomendasi r ON t.rekomendasi_id=r.id
        WHERE r.lhp_id IN ($in) ORDER BY r.lhp_id, r.no, t.no");
    while ($tx = mysqli_fetch_assoc($qTl)) {
        $tlMap[$tx['lhp_id']][] = $tx;
    }
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Matrik TL LHP');

$colWidths = [
    'A' => 5, 'B' => 32, 'C' => 5, 'D' => 42, 'E' => 5,
    'F' => 40, 'G' => 18, 'H' => 8, 'I' => 8, 'J' => 10,
    'K' => 12, 'L' => 14, 'M' => 24, 'N' => 14,
];
foreach ($colWidths as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

$sheet->mergeCells('A1:N1');
$sheet->setCellValue('A1', 'MATRIKS PEMANTAUAN TINDAK LANJUT');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$sheet->mergeCells('A2:N2');
$sheet->setCellValue('A2', $judulMap[$sumber] . ' - DATA PEMANTAUAN TINDAK LANJUT');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

$sheet->mergeCells('A4:A5');
$sheet->mergeCells('B4:C4');
$sheet->mergeCells('D4:E4');
$sheet->mergeCells('F4:F5');
$sheet->mergeCells('G4:G5');
$sheet->mergeCells('H4:L4');
$sheet->mergeCells('M4:M5');
$sheet->mergeCells('N4:N5');

$sheet->setCellValue('A4', 'No');
$sheet->setCellValue('B4', 'Temuan Pemeriksaan');
$sheet->setCellValue('D4', 'Rekomendasi');
$sheet->setCellValue('F4', 'Tindak Lanjut Entitas yang Diperiksa');
$sheet->setCellValue('G4', 'Unit / Entitas yang Diperiksa');
$sheet->setCellValue('H4', 'Hasil Pemantauan Tindak Lanjut');
$sheet->setCellValue('M4', 'Kesimpulan');
$sheet->setCellValue('N4', 'Nilai Penyerahan');

$sheet->setCellValue('H5', 'Proses');
$sheet->setCellValue('I5', 'Sesuai');
$sheet->setCellValue('J5', 'Belum Sesuai');
$sheet->setCellValue('K5', 'Belum Ditindak Lanjut');
$sheet->setCellValue('L5', 'Tidak Dapat Ditindak Lanjut');

$sheet->setCellValue('B5', 'Judul');
$sheet->setCellValue('C5', 'Jml');
$sheet->setCellValue('D5', 'Uraian');
$sheet->setCellValue('E5', 'Jml');

$thin = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$center = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
$boldCenter = ['font' => ['bold' => true]] + $center;
$sheet->getStyle('A4:N5')->applyFromArray($boldCenter);
$sheet->getStyle('A4:N5')->applyFromArray($thin);

$r = 6;
$no = 1;
foreach ($rows as $row) {
    $temuan = $row['judul_temuan'];
    if ($row['nomor_lhp'] !== null && $row['nomor_lhp'] !== '') {
        $temuan .= "\n" . $row['nomor_lhp'] . ' - TA ' . (int)$row['tahun'];
    }

    $reks = [];
    foreach (($rekMap[$row['id']] ?? []) as $rk) {
        $reks[] = (int)$rk['no'] . '. ' . $rk['uraian'];
    }

    $tlLines = [];
    $groups = [];
    $current = [];
    $lastRek = null;
    foreach (($tlMap[$row['id']] ?? []) as $tx) {
        if ($lastRek !== null && (string)$tx['rek_no'] !== (string)$lastRek) {
            $groups[] = $current;
            $current = [];
        }
        $lastRek = $tx['rek_no'];
        $current[] = (int)$tx['tl_no'] . '. ' . $tx['uraian'] . ' (' . $tx['status'] . ')';
    }
    if ($current) {
        $groups[] = $current;
    }
    foreach ($groups as $g) {
        $tlLines[] = implode("\n", $g);
    }
    $tlDesc = implode("\n\n", $tlLines);

    $sheet->setCellValue('A' . $r, $no);
    $sheet->setCellValue('B' . $r, $temuan);
    $sheet->setCellValue('C' . $r, (int)$row['jml_rekomendasi']);
    $sheet->setCellValue('D' . $r, implode("\n", $reks));
    $sheet->setCellValue('E' . $r, (int)$row['jml_tl']);
    $sheet->setCellValue('F' . $r, $tlDesc);
    $sheet->setCellValue('G' . $r, $row['nama_unit'] ?? '-');
    $sheet->setCellValue('H' . $r, (int)$row['hasil_proses']);
    $sheet->setCellValue('I' . $r, (int)$row['hasil_sesuai']);
    $sheet->setCellValue('J' . $r, (int)$row['hasil_belum_sesuai']);
    $sheet->setCellValue('K' . $r, (int)$row['hasil_belum_tl']);
    $sheet->setCellValue('L' . $r, (int)$row['hasil_tidak_tl']);
    $sheet->setCellValue('M' . $r, $row['kesimpulan'] ?? '');
    if ($row['nilai'] !== null && $row['nilai'] !== '') {
        $sheet->setCellValue('N' . $r, (float)$row['nilai']);
        $sheet->getStyle('N' . $r)->getNumberFormat()->setFormatCode('#,##0.00');
    } else {
        $sheet->setCellValue('N' . $r, '');
    }

    foreach (['B', 'D', 'F', 'G', 'M'] as $c) {
        $sheet->getStyle($c . $r)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
    }
    $sheet->getRowDimension($r)->setRowHeight(-1);
    $no++;
    $r++;
}

$last = $r - 1;
if ($last >= 6) {
    $sheet->getStyle('A6:N' . $last)->applyFromArray($thin);
}
$sheet->getStyle('A4:N' . $last)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A6:N' . $last)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

while (ob_get_level() > 0) {
    ob_end_clean();
}

$fileName = 'Matrik_TL_LHP_' . $sumber . '_' . date('Ymd_His') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;