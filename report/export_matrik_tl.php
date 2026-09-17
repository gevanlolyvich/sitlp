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

checkRole([
    'ADMIN',
    'KEPALA_SIA',
    'DIREKSI',
    'KOMISARIS'
]);

require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();

/*
|--------------------------------------------------------------------------
| MATRIKS PEMANTAUAN TINDAK LANJUT  (rincian per tindak lanjut)
|--------------------------------------------------------------------------
*/
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Matrik Pemantauan TL');

// Lebar kolom
$colWidths = [
    'A'=>7.71, 'B'=>32, 'C'=>7.71, 'D'=>42, 'E'=>7.71,
    'F'=>32, 'G'=>12, 'H'=>16, 'I'=>18, 'J'=>22,
    'K'=>22, 'L'=>24
];
foreach ($colWidths as $col=>$w) { $sheet->getColumnDimension($col)->setWidth($w); }

// Judul
$sheet->mergeCells('A1:L1');
$sheet->setCellValue('A1', 'MATRIKS PEMANTAUAN TINDAK LANJUT');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$sheet->mergeCells('A2:L2');
$sheet->setCellValue('A2', 'LAPORAN HASIL PEMERIKSAAN (MATRIKS PEMANTAUAN TINDAK LANJUT)');
$sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);

// ============================================================
// Header (baris 5-7)
// ============================================================
$sheet->mergeCells('A5:A7');
$sheet->mergeCells('B5:C6');
$sheet->mergeCells('D5:E6');
$sheet->mergeCells('F5:F7');
$sheet->mergeCells('G5:J5');
$sheet->mergeCells('K5:K7');
$sheet->mergeCells('L5:L7');

$sheet->setCellValue('A5', 'No');
$sheet->setCellValue('B5', 'Temuan Pemeriksaan');
$sheet->setCellValue('D5', 'Rekomendasi');
$sheet->setCellValue('F5', 'Tindak Lanjut Entitas yang Diperiksa');
$sheet->setCellValue('G5', 'Hasil Pemantauan Tindak Lanjut');
$sheet->setCellValue('K5', 'Kesimpulan');
$sheet->setCellValue('L5', 'Nilai Penyerahan aset atau penyetoran uang ke kas negara/daerah');

$sheet->setCellValue('G6', 'Sesuai');
$sheet->setCellValue('H6', 'Belum Sesuai');
$sheet->setCellValue('I6', 'Belum Ditindaklanjuti');
$sheet->setCellValue('J6', 'Tidak Dapat Ditindaklanjuti dengan alasan yang sah');

// Sub header baris 7
$sheet->setCellValue('B7', 'Judul');
$sheet->setCellValue('C7', 'Jml');
$sheet->setCellValue('D7', 'Uraian');
$sheet->setCellValue('E7', 'Jml');
$sheet->setCellValue('G7', 'Jml');
$sheet->setCellValue('H7', 'Jml');
$sheet->setCellValue('I7', 'Jml');
$sheet->setCellValue('J7', 'Jml');

$thin = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$center = ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]];
$boldCenter = ['font' => ['bold' => true]] + $center;
$sheet->getStyle('A5:L7')->applyFromArray($boldCenter);
$sheet->getStyle('A5:L7')->applyFromArray($thin);

function getMatrixData($conn, $tglAwal = '', $tglAkhir = '')
{
    $list = [];
    $where = '';
    if ($tglAwal !== '' && $tglAkhir !== '') {
        $where = "WHERE DATE(tl.created_at) BETWEEN '$tglAwal' AND '$tglAkhir'";
    }
    $q = mysqli_query($conn, "
        SELECT tl.id, tl.nomor_tindak_lanjut, tl.uraian_tindak_lanjut, tl.hasil_tindak_lanjut,
               tl.status, tl.catatan_spi, tl.nilai_penyerahan,
               t.nomor_temuan, t.judul_temuan,
               r.nomor_rekomendasi, r.rekomendasi,
               p.tahun, ap.nomor_audit, ap.judul_audit, u.nama_unit
        FROM audit_tindak_lanjut tl
        LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
        LEFT JOIN audit_temuan t ON r.temuan_id=t.id
        LEFT JOIN audit_pemeriksaan ap ON ap.id=t.audit_id
        LEFT JOIN audit_program p ON p.id=ap.program_id
        LEFT JOIN unit_kerja u ON tl.unit_id=u.id
        $where
        ORDER BY p.tahun, ap.id, t.id, r.id, tl.id
    ");
    while ($d = mysqli_fetch_assoc($q)) { $list[] = $d; }
    return $list;
}

$tglAwal  = isset($_GET['tanggal_awal']) ? mysqli_real_escape_string($conn, $_GET['tanggal_awal']) : '';
$tglAkhir = isset($_GET['tanggal_akhir']) ? mysqli_real_escape_string($conn, $_GET['tanggal_akhir']) : '';

$matrix = getMatrixData($conn, $tglAwal, $tglAkhir);

$r = 8;
$no = 1;
$statusMap = [
    'Sesuai'                    => 'G',
    'Belum Sesuai'              => 'H',
    'Belum Ditindak Lanjut'     => 'I',
    'Tidak Dapat Ditindak Lanjut'=> 'J'
];

foreach ($matrix as $m) {
    $temuan = trim($m['judul_temuan'] . ' (' . $m['nomor_temuan'] . ')');
    $rekom  = trim($m['nomor_rekomendasi'] ? '[' . $m['nomor_rekomendasi'] . '] ' : '') . trim($m['rekomendasi']);
    $tldesc = trim($m['uraian_tindak_lanjut'] . ($m['hasil_tindak_lanjut'] ? "\nHasil: " . $m['hasil_tindak_lanjut'] : ''));

    // Kesimpulan sesuai status
    $kesimpulan = $m['status'];
    if (!empty($m['catatan_spi'])) { $kesimpulan .= "\nCatatan: " . $m['catatan_spi']; }

    $sheet->setCellValue('A'.$r, $no);
    $sheet->setCellValue('B'.$r, $temuan);
    $sheet->setCellValue('C'.$r, 1);                 // Jml temuan
    $sheet->setCellValue('D'.$r, $rekom);
    $sheet->setCellValue('E'.$r, 1);                 // Jml rekomendasi
    $sheet->setCellValue('F'.$r, $tldesc);
    $stsCol = isset($statusMap[$m['status']]) ? $statusMap[$m['status']] : null;
    if ($stsCol) { $sheet->setCellValue($stsCol.$r, 1); }
    $sheet->setCellValue('K'.$r, $kesimpulan);
    if ($m['nilai_penyerahan'] !== null && $m['nilai_penyerahan'] !== '') {
        $sheet->setCellValue('L'.$r, (float)$m['nilai_penyerahan']);
        $sheet->getStyle('L'.$r)->getNumberFormat()->setFormatCode('#,##0.00');
    } else {
        $sheet->setCellValue('L'.$r, '');
    }

    $sheet->getRowDimension($r)->setRowHeight(-1);
    $no++;
    $r++;
}

// Border + wrap data
$last = $r - 1;
if ($last >= 8) {
    $sheet->getStyle('A8:L'.$last)->applyFromArray($thin);
}
$sheet->getStyle('A8:L'.$last)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
$sheet->getStyle('A5:L'.$last)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

/*
|--------------------------------------------------------------------------
| Output
|--------------------------------------------------------------------------
*/
while (ob_get_level() > 0) { ob_end_clean(); }

$fileName = 'Matrik_TL_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;