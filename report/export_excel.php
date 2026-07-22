<?php

session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole([
    'ADMIN',
    'KEPALA_SPI',
    'DIREKSI'
]);

require_once '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$awal = isset($_GET['tanggal_awal']) ? mysqli_real_escape_string($conn, $_GET['tanggal_awal']) : '';
$akhir = isset($_GET['tanggal_akhir']) ? mysqli_real_escape_string($conn, $_GET['tanggal_akhir']) : '';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
//if($jenis=='all'){ exportAll($conn,$awal,$akhir); exit;}
if($jenis=='all'){ exportAll();}
function exportAll(){
//function exportAll($conn,$awal,$akhir){
 $spreadsheet = new Spreadsheet();
 /*
 |--------------------------------------------------------------------------
 | Sheet 1 - Audit
 |--------------------------------------------------------------------------
 */
 $sheet1 = $spreadsheet->getActiveSheet();
 $sheet1->setTitle('Pemeriksaan Audit');

 //$summary = $spreadsheet->getActiveSheet();
 //$summary->setTitle('SUMMARY');
 //$summary->setCellValue('A1','EXECUTIVE SUMMARY');
 //$summary->mergeCells('A1:B1');
 //$totalAudit =mysqli_num_rows(mysqli_query($conn,"SELECT id FROM audit_pemeriksaan WHERE tanggal_mulai BETWEEN '$awal' AND '$akhir'"));

 //$totalTemuan = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM audit_temuan WHERE created_at BETWEEN '$awal' AND '$akhir'"));
 //$totalRekomendasi = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM audit_rekomendasi WHERE created_at BETWEEN '$awal' AND '$akhir'"));
 //$totalTL = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM audit_tindak_lanjut WHERE created_at BETWEEN '$awal' AND '$akhir'"));

 //$data = [
 //['Total Audit',$totalAudit],
 //['Total Temuan',$totalTemuan],
 //['Total Rekomendasi',$totalRekomendasi],
 //['Total Tindak Lanjut',$totalTL]
 //];
 //$row = 3;
 //foreach($data as $d)
 //{
 //   $summary->setCellValue(
 //       'A'.$row,
 //       $d[0]
 //   );
 //   $summary->setCellValue(
 //       'B'.$row,
 //       $d[1]
 //   );
 //   $row++;
 //} 

// $sheet1 = $spreadsheet->getActiveSheet();
// $sheet1->setTitle('Pemeriksaan Audit');

 /*
 | Sheet 2 - Temuan
 |--------------------------------------------------------------------------
 */
 $sheet2 = $spreadsheet->createSheet();
 $sheet2->setTitle('Temuan Audit');
 /*query temuan, isi data*/

 $auditSheet = $spreadsheet->createSheet();
 $auditSheet->setTitle('Pemeriksaan Audit');
 $qr = "SELECT nomor_audit, judul_audit, jenis_audit, tanggal_mulai, tanggal_selesai,
status
 FROM audit_pemeriksaan
 WHERE tanggal_mulai
 BETWEEN '$awal'
 AND '$akhir'";


 /*
 |--------------------------------------------------------------------------
 | Sheet 3 - Rekomendasi
 |--------------------------------------------------------------------------
 */

 $sheet3 = $spreadsheet->createSheet();
 $sheet3->setTitle('Rekomendasi');
 /*query rekomendasi isi data*/
 /*
 |--------------------------------------------------------------------------
 | Sheet 4 - Tindak Lanjut
 |--------------------------------------------------------------------------
 */
 $sheet4 = $spreadsheet->createSheet();
 $sheet4->setTitle('Tindak Lanjut');
 /* query TL, isi data */
};

/* ----- */

$query = mysqli_query($conn,"SELECT
    tl.nomor_tindak_lanjut,
    uk.nama_unit,
    t.judul_temuan,
    r.rekomendasi,
    tl.pic,
    tl.target_selesai,
    tl.tanggal_realisasi,
    tl.status,
    tl.catatan_spi
    FROM audit_tindak_lanjut tl
    LEFT JOIN unit_kerja uk ON tl.unit_id=uk.id
    LEFT JOIN audit_rekomendasi r ON tl.rekomendasi_id=r.id
    LEFT JOIN audit_temuan t ON r.temuan_id=t.id
    WHERE DATE(tl.created_at) BETWEEN '$awal' AND '$akhir'
    ORDER BY tl.id DESC");

/* header */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Tindak Lanjut');
$headers = [
 'No',
 'Nomor TL',
 'Unit',
 'Temuan',
 'Rekomendasi',
 'PIC',
 'Target',
 'Realisasi',
 'Status Tindak Lanjut',
 'Catatan SPI'];

$col='A';
foreach($headers as $header)
{
    $sheet->setCellValue(
        $col.'1',
        $header
    );
    $col++;
}

/* fill data */
$row = 2;
$no = 1;
while(
 $data = mysqli_fetch_assoc($query)){ $sheet->setCellValue('A'.$row,$no++);
 $sheet->setCellValue('B'.$row,$data['nomor_tindak_lanjut']);
 $sheet->setCellValue('C'.$row,$data['nama_unit']);
 $sheet->setCellValue('D'.$row,$data['judul_temuan']);
 $sheet->setCellValue('E'.$row,$data['rekomendasi']);
 $sheet->setCellValue('F'.$row,$data['pic']);
 $sheet->setCellValue('G'.$row,$data['target_selesai']);
 $sheet->setCellValue('H'.$row,$data['tanggal_realisasi']);
 $sheet->setCellValue('I'.$row,$data['status']);
 $sheet->setCellValue('J'.$row,$data['catatan_spi']);
 $row++;
 }

$fileName = 'Report_Tindak_Lanjut_'.date('Ymd_His').'.xlsx';
header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition:attachment;
filename="'.$fileName.'"');
header('Cache-Control:max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
