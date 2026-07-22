<?php

if (function_exists('opcache_reset')) {
    opcache_reset();
}

ob_start();
session_start();

require_once "../config/app.php";
require_once "../config/database.php";
require_once "../config/functions.php";
require_once "../auth/check.php";
require_once "../auth/role.php";

checkRole(['ADMIN','KEPALA_SPI']);

require_once "../tcpdf/tcpdf.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die("ID Audit tidak valid");
}

/* QUERY DATA AUDIT */
$qAudit = mysqli_query($conn,"
	SELECT ap.*, uk.nama_unit, au.nama_auditor 
	FROM audit_pemeriksaan ap LEFT JOIN unit_kerja uk ON ap.unit_id = uk.id LEFT JOIN auditor au ON ap.ketua_auditor_id = au.id 
	WHERE ap.id = '$id'"
);
$audit = mysqli_fetch_assoc($qAudit);
if (!$audit) {
    die("Data audit tidak ditemukan");
}

/* QUERY TIM AUDIT */
$qTim = mysqli_query($conn,"
	SELECT t.*, a.nama_auditor
	FROM audit_tim t LEFT JOIN auditor a ON t.auditor_id = a.id
	WHERE t.audit_id = '$id'
	ORDER BY t.id");

/* QUERY TEMUAN */
$qTemuan = mysqli_query($conn,"SELECT * FROM audit_temuan WHERE audit_id = '$id' ORDER BY id");

/* QUERY REKOMENDASI */
$qRekomendasi = mysqli_query($conn,"
	SELECT r.*, t.nomor_temuan, t.judul_temuan
	FROM audit_rekomendasi r INNER JOIN audit_temuan t ON r.temuan_id = t.id
	WHERE t.audit_id = '$id'
	ORDER BY r.id");


/* QUERY TINDAK LANJUT */
$qTL = mysqli_query($conn,"
	SELECT tl.*, uk.nama_unit, r.nomor_rekomendasi
	FROM audit_tindak_lanjut tl LEFT JOIN unit_kerja uk ON tl.unit_id = uk.id INNER JOIN audit_rekomendasi r ON tl.rekomendasi_id = r.id INNER JOIN audit_temuan t ON r.temuan_id = t.id
	WHERE t.audit_id = '$id'
	ORDER BY tl.target_selesai ASC");

/* query risiko */
$qRisiko = mysqli_query($conn,"
    SELECT
        tingkat_risiko,
        COUNT(*) jumlah
    FROM audit_temuan
    WHERE audit_id='$id'
    GROUP BY tingkat_risiko
    "
);


/* SUMMARY */
$totalTemuan = mysqli_num_rows($qTemuan);
mysqli_data_seek($qTemuan, 0);
$totalRekomendasi = mysqli_num_rows($qRekomendasi);
mysqli_data_seek($qRekomendasi, 0);
$totalTL = mysqli_num_rows($qTL);
mysqli_data_seek($qTL, 0);
$totalSelesai = mysqli_num_rows(
    mysqli_query($conn,"
	SELECT tl.id 
	FROM audit_tindak_lanjut tl INNER JOIN audit_rekomendasi r ON tl.rekomendasi_id = r.id INNER JOIN audit_temuan t ON r.temuan_id = t.id
	WHERE t.audit_id='$id' AND tl.status='Sesuai'")
);
$totalBelum = $totalTL - $totalSelesai;

/* class */
class MYPDF extends TCPDF
{
    public function Header()
    {
        if ($this->getPage() == 1) {
            return;
        }
        $logo = __DIR__ . '/../assets/images/LogoJXB_new.png';
        if (file_exists($logo)) {
            $this->Image($logo, 15, 8, 18);
        }
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(0,10,'LAPORAN HASIL AUDIT (LHA)',0,0,'R');
        $this->Ln(10);
        $this->SetDrawColor(0, 51, 153);
	$this->Line(15, 22, 195, 22);
	/* watermark */
	    $this->StartTransform();
    	    $this->Rotate(45,70,190);
            $this->SetFont('helvetica','B',28);
    	    $this->SetTextColor(245,245,245);
    	    $this->Text(35,190,'CONFIDENTIAL');
    	    $this->StopTransform();
    	    $this->SetTextColor(0,0,0);
        /* end */
    }

    public function Footer()
    {
        if ($this->getPage() == 1) {
            return;
        }

        $this->SetY(-15);
        $this->SetDrawColor(0, 51, 153);
        $this->Line(15,$this->GetY(),195,$this->GetY());
        $this->Ln(2);
        $this->SetFont('helvetica','',8);
        $this->Cell(90,10,'PT Jakarta Tourisindo',0,0,'L');
        $this->Cell(90,10,'Halaman ' .$this->getAliasNumPage() .' / ' .$this->getAliasNbPages(),0,0,'R');
    }
}

/* inisialisasi */
$pdf = new MYPDF(PDF_PAGE_ORIENTATION,PDF_UNIT,PDF_PAGE_FORMAT,true,'UTF-8',false);

$pdf->SetCreator('SITLP');
$pdf->SetAuthor('PT Jakarta Tourisindo');
$pdf->SetTitle('LHA - ' . $audit['nomor_audit']);
$pdf->SetMargins(20,20,20);
$pdf->SetAutoPageBreak(true,25);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
$pdf->SetFont('helvetica','',11);

/* cover */
$pdf->AddPage();
/* LOGO */
$logo = __DIR__ . '/../assets/images/LogoJXB_new.png';

if (file_exists($logo)) {
    $pdf->Image($logo,75,35,60);
}

$pdf->Ln(70);
$pdf->SetFont('helvetica','B',20);
$pdf->Cell(0,10,'LAPORAN HASIL AUDIT',0,1,'C');
$pdf->Cell(0,10,'(LHA)',0,1,'C');
$pdf->Ln(10);
$pdf->SetTextColor(255,140,0);
$pdf->SetFont('helvetica','B',16);
$pdf->Cell(0,10,$audit['judul_audit'],0,1,'C');
$pdf->SetTextColor(0,0,0);
$pdf->Ln(10);


/* daftar isi */
$pdf->AddPage();
$pdf->Bookmark('Daftar Isi', 0, 0);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0,10,'DAFTAR ISI',0,1,'C');
$pdf->Ln(10);
$pdf->SetFont('helvetica', '', 12);
$daftarIsi = [
    'BAB I     Pendahuluan',
    'BAB II    Hasil Temuan Audit',
    'BAB III   Rekomendasi',
    'BAB IV    Tindak Lanjut',
    'BAB V     Kesimpulan',
    'Pengesahan'
];
foreach($daftarIsi as $isi)
{
    $pdf->Cell(0,8,$isi,0,1);
}


/* bab 1, pendahuluan */
$pdf->AddPage();
$pdf->Bookmark('BAB I - Pendahuluan',0,0);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,10,'BAB I',0,1,'C');
$pdf->Cell(0,10,'PENDAHULUAN',0,1,'C');
$pdf->Ln(10);


/* ruang lingkup */
$html = "
 <h3>1. Ruang Lingkup Audit</h3>
  <p>{$audit['ruang_lingkup']}</p>
 <h3>2. Jenis Audit</h3>
  <p>{$audit['jenis_audit']}</p>
 <h3>3. Periode Audit</h3>
  <p>".date('d-m-Y',strtotime($audit['tanggal_mulai']))." s/d ".date('d-m-Y',strtotime($audit['tanggal_selesai']))."</p>
 <h3>4. Tim Audit</h3>
";
while($tim = mysqli_fetch_assoc($qTim))
{
    $html .= "
    • {$tim['nama_auditor']} ({$tim['peran']})<br>
    ";
}
$pdf->writeHTML($html,true,false,true,false,'');
mysqli_data_seek($qTim,0);


/* bab 2, hasil temuan */
$pdf->AddPage();
$pdf->Bookmark('BAB II - Hasil Temuan Audit',0,0);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,10,'BAB II',0,1,'C');
$pdf->Cell(0,10,'HASIL TEMUAN AUDIT',0,1,'C');
$pdf->Ln(8);


/* detail temuan */
$no = 1;
while($temuan = mysqli_fetch_assoc($qTemuan))
{
    $pdf->SetFillColor(68,114,196);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('helvetica','B',11);
    $judul = "TEMUAN ".$no." : ".$temuan['nomor_temuan']." - ".$temuan['judul_temuan'];
    $pdf->Cell(0,10,$judul,1,1,'L',true);
    $pdf->SetTextColor(0,0,0);
    $rows = [
        'Kondisi' =>
            $temuan['kondisi'],
        'Kriteria' =>
            $temuan['kriteria'],
        'Sebab' =>
            $temuan['sebab'],
        'Akibat' =>
            $temuan['akibat'],
        'Tingkat Risiko' =>
            $temuan['tingkat_risiko']
    ];
    foreach($rows as $label => $isi)
    {
        $yStart = $pdf->GetY();
        $pdf->SetFont('helvetica','B',10);
        $pdf->SetFillColor(242,242,242);
        $pdf->MultiCell(40,8,$label,1,'L',true,0);
        $pdf->SetFont('helvetica','',10);
        $pdf->MultiCell(145,8,$isi,1,'J',false,1);
    }
    switch(
        strtoupper($temuan['tingkat_risiko']
        )
    )
    {
        case 'TINGGI':
            $bg = [255,199,206];
            break;
        case 'SEDANG':
            $bg = [255,235,156];
            break;
        default:
            $bg = [198,239,206];
    }
    $pdf->SetFillColor($bg[0],$bg[1],$bg[2]);
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(50,8,"RISIKO : ".strtoupper($temuan['tingkat_risiko']),1,1,'C',true);
    $pdf->Ln(6);
    $no++;

}
mysqli_data_seek($qTemuan,0);


/* bab 3, rekomendasi */
$pdf->AddPage();
$pdf->Bookmark('BAB III - Rekomendasi',0,0);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,10,'BAB III',0,1,'C');
$pdf->Cell(0,10,'REKOMENDASI',0,1,'C');
$pdf->Ln(8);


/* isi rekomendasi */
$no = 1;
while($rek = mysqli_fetch_assoc($qRekomendasi))
{
    $pdf->SetFillColor(112,173,71);
    $pdf->SetTextColor(255,255,255);
    $pdf->SetFont('helvetica','B',11);
    $pdf->Cell(0,10,"REKOMENDASI ".$no,1,1,'L',true);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(45,8,'Temuan',1,0,'L',true);
    $pdf->SetFont('helvetica','',10);
    $pdf->MultiCell(140,8,$rek['nomor_temuan'].' - '.$rek['judul_temuan'],1,'J',false,1);
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(45,8,'Rekomendasi',1,0,'L',true);
    $pdf->SetFont('helvetica','',10);
    $pdf->MultiCell(140,8,$rek['rekomendasi'],1,'J',false,1);
    switch(strtoupper($rek['prioritas'])){
        case 'TINGGI':
            $bg = [255,199,206];
            break;
        case 'SEDANG':
            $bg = [255,235,156];
            break;
        default:
            $bg = [198,239,206];
    }
    $pdf->SetFillColor($bg[0],$bg[1],$bg[2]);
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(45,8,'Prioritas',1,0,'L',true);
    $pdf->Cell(140,8,strtoupper($rek['prioritas']),1,1,'C',true);
    $pdf->Ln(6);
    $no++;
}

/* bab 4 , tindak lanjut */
//$pdf->AddPage();
$pdf->AddPage('L');
$pdf->Bookmark('BAB IV - Tindak Lanjut',0,0);
$pdf->SetFont('helvetica','B',8);
$pdf->Cell(0,10,'BAB IV',0,1,'C');
$pdf->Cell(0,10,'TINDAK LANJUT',0,1,'C');
$pdf->Ln(5);


/* tabel tindak lanjut */
$pdf->SetFont('helvetica','B',9);
$pdf->SetFillColor(68,114,196);
$pdf->SetTextColor(255,255,255);
$header = [['No',10],['Nomor TL',35],['PIC',40],['Unit',40],['Target',25],['Status',30],['Catatan SPI',30],['Overdue',25]];
foreach($header as $col){
  $pdf->Cell($col[1],8,$col[0],1,0,'C',true);
}
$pdf->Ln();
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('helvetica','',8);
$no = 1;
while($tl = mysqli_fetch_assoc($qTL)){
    $overdue = '-';
    if(
        $tl['status'] != 'SELESAI'
        &&
        !empty($tl['target_selesai'])
    )
    {
        $today = new DateTime();
        $target = new DateTime($tl['target_selesai']);
        if($target < $today)
        {
            $diff = $today->diff($target)->days;
            $overdue = $diff . ' Hari';
        }
    }
    switch($tl['status'])
    {
        case 'Sesuai':
            $statusBg = [198,239,206];
            break;
        case 'Proses':
            $statusBg = [255,235,156];
            break;
        default:
            $statusBg = [255,199,206];
    }
    $pdf->Cell(10,8,$no,1,0,'C');
    $pdf->Cell(35,8,$tl['nomor_tindak_lanjut'],1);
    $pdf->Cell(40,8,$tl['pic'],1);
    $pdf->Cell(40,8,$tl['nama_unit'],1);
    $pdf->Cell(25,8,date('d-m-Y',strtotime($tl['target_selesai'])),1,0,'C');
    $pdf->SetFillColor($statusBg[0],$statusBg[1],$statusBg[2]);
    $pdf->Cell(30,8,$tl['status'],1,0,'C',true);
    $pdf->Cell(30,8,$tl['catatan_spi'] ?? '-',1,0,'C');
    if($overdue != '-'){
        $pdf->SetFillColor(255,199,206);
	$fill = true;
    }else{
        $fill = false;
    }
    $pdf->Cell(25,8,$overdue,1,1,'C',$fill);
    $no++;
}
$pdf->Ln(8);
$pdf->SetFont('helvetica','B',10);
$pdf->Cell(60,8,'Total Tindak Lanjut',1);
$pdf->Cell(20,8,$totalTL,1,1,'C');
$pdf->Cell(60,8,'Selesai',1);
$pdf->Cell(20,8,$totalSelesai,1,1,'C');
$pdf->Cell(60,8,'Belum Selesai',1);
$pdf->Cell(20,8,$totalBelum,1,1,'C');


/* bab 5, kesimpulan */
$pdf->AddPage('P');
$pdf->Bookmark('BAB V - Kesimpulan',0,0);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,10,'BAB V',0,1,'C');
$pdf->Cell(0,10,'KESIMPULAN',0,1,'C');
$pdf->Ln(10);


/* ringkasan */
$persentaseSelesai = 0;

if ($totalTL > 0) {
    $persentaseSelesai = round(
        ($totalSelesai / $totalTL) * 100,
        2
    );
}
$pdf->SetFont('helvetica','',11);
$kesimpulan = "

Berdasarkan hasil audit yang telah dilakukan terhadap {$audit['judul_audit']}, diperoleh {$totalTemuan} temuan audit yang menghasilkan {$totalRekomendasi} rekomendasi perbaikan.

Dari {$totalTL} tindak lanjut yang telah ditetapkan, sebanyak {$totalSelesai} ({$persentaseSelesai}%) telah selesai dilaksanakan, sedangkan {$totalBelum} masih memerlukan penyelesaian lebih lanjut.

Manajemen diharapkan dapat memastikan seluruh rekomendasi ditindaklanjuti sesuai target waktu yang telah ditentukan guna meningkatkan efektivitas pengendalian internal dan mendukung pencapaian tujuan organisasi.

";

$pdf->MultiCell(0,7,$kesimpulan,0,'J',false,1);
$pdf->Ln(10);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,8,'Ringkasan Hasil Audit',0,1);
$pdf->Ln(3);
$pdf->SetFont('helvetica','',10);
$summary = [
    ['Total Temuan Audit', $totalTemuan],
    ['Total Rekomendasi', $totalRekomendasi],
    ['Total Tindak Lanjut', $totalTL],
    ['Tindak Lanjut Selesai', $totalSelesai],
    ['Tindak Lanjut Belum Selesai', $totalBelum],
    ['Persentase Penyelesaian', $persentaseSelesai.' %']
];

foreach($summary as $row)
{
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(80,8,$row[0],1);
    $pdf->SetFont('helvetica','',10);
    $pdf->Cell(40,8,$row[1],1,1,'C');
}

$pdf->Ln(10);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,8,'Distribusi Tingkat Risiko',0,1);
while($r = mysqli_fetch_assoc($qRisiko))
{
    switch($r['tingkat_risiko'])
    {
        case 'TINGGI':
            $color = [255,199,206];
            break;
        case 'SEDANG':
            $color = [255,235,156];
            break;
        default:
            $color = [198,239,206];
    }

    $pdf->SetFillColor($color[0],$color[1],$color[2]);
    $pdf->Cell(50,8,$r['tingkat_risiko'],1,0,'C',true);
    $pdf->Cell(20,8,$r['jumlah'],1,1,'C');
}
$pdf->Ln(10);
$pdf->MultiCell(0,7,
    "Laporan Hasil Audit ini disusun untuk digunakan sebagai bahan evaluasi dan perbaikan berkelanjutan dalam rangka meningkatkan tata kelola, manajemen risiko, dan sistem pengendalian internal PT Jakarta Tourisindo.",
    0,'J',false,1);








/* halaman pengesahan */
$pdf->AddPage();
$pdf->Bookmark('Pengesahan',0,0);
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,10,'PENGESAHAN',0,1,'C');
$pdf->Ln(10);


/* teks pengesahan */
$pdf->SetFont('helvetica','',11);
$narasi = "
Laporan Hasil Audit ini telah disusun berdasarkan hasil pemeriksaan yang dilakukan oleh Satuan Internal Audit PT Jakarta Tourisindo.

Laporan ini digunakan sebagai bahan evaluasi dan perbaikan dalam rangka meningkatkan efektivitas pengendalian internal, manajemen risiko, serta tata kelola perusahaan yang baik (Good Corporate Governance).
";
$pdf->MultiCell(0,7,$narasi,0,'J',false,1);
$pdf->Ln(15);

/* info kerahasiaan */
$pdf->Ln(10);
$pdf->SetFont('helvetica','I',8);
$pdf->MultiCell(0,5,
    'Dokumen ini bersifat rahasia dan hanya diperuntukkan bagi pihak yang berwenang di lingkungan PT Jakarta Tourisindo.',
    0,'C',false,1);


/* executive approval box */
$pdf->SetFillColor(242,242,242);
$pdf->SetFont('helvetica','B',10);
$pdf->Cell(0,8,'PERSETUJUAN MANAJEMEN',1,1,'C',true);
$pdf->SetFont('helvetica','',10);
$pdf->MultiCell(0,15,"Laporan Hasil Audit ini telah ditelaah dan disetujui untuk ditindaklanjuti sesuai rekomendasi yang diberikan.",1,'J',false,1);
$pdf->Ln(10);


/* tanggal */
$pdf->Cell(0,7,'Jakarta, ' .date('d F Y'),0,1,'R');
$pdf->Ln(15);


/* area ttd */
$pdf->SetFont('helvetica','B',11);
$pdf->Cell(80,7,'Kepala Satuan Internal Audit',0,0,'C');
$pdf->Cell(30,7,'',0,0);
$pdf->Cell(80,7,'Direktur Utama',0,1,'C');
$pdf->Ln(30);
$pdf->Cell(80,7,'(________________________)',0,0,'C');
$pdf->Cell(30,7,'',0,0);
$pdf->Cell(80,7,'(________________________)',0,1,'C');


/* qr code verifikasi */
$pdf->Ln(20);
$pdf->SetFont('helvetica','B',9);
$pdf->Cell(0,7,'Verifikasi Dokumen:',0,1,'C');



/* url verifikasi */
/* $verifyUrl = APP_URL . '/verify_lha.php?id=' . $id; */
$verifyUrl = APP_URL . '/verify_lha.php?token=' . $audit['verification_token'];


/* qr code */
$style = ['border' => 0,'padding' => 1,'fgcolor' => [0,0,0],'bgcolor' => false];
$x = ($pdf->getPageWidth() - 35) / 2;
$pdf->write2DBarcode($verifyUrl,'QRCODE,H',$x,$pdf->GetY(),35,35,$style);


/* url dibawah qr */
$pdf->Ln(40);
$pdf->SetFont('helvetica','',8);
$pdf->Cell(0,5,$verifyUrl,0,1,'C');

/* dokumen verifikasi */
$pdf->Ln(5);
$pdf->SetFont('helvetica','I',8);
$pdf->Cell(0,5,'Dokumen ini dapat diverifikasi melalui QR Code di atas.',0,1,'C');


/* metadata dokumen */
$pdf->Ln(5);
$pdf->SetFont('helvetica','',8);
$pdf->Cell(0,5,'Nomor Dokumen : LHA-'.$audit['nomor_audit'],0,1,'C');
$pdf->Cell(0,5,'Dibuat : '.date('d-m-Y H:i:s'),0,1,'C');
$pdf->Cell(0,5,'Sistem : SITLP - Satuan Internal Audit',0,1,'C');


/* info audit */
$html = "
   <table cellpadding='6'>
    <tr>
     <td width='40%'><b>Nomor Audit</b></td>
     <td>: {$audit['nomor_audit']}</td>
    </tr>
    <tr>
     <td><b>Nomor Surat Tugas</b></td>
     <td>: {$audit['nomor_surat_tugas']}</td>
    </tr>
    <tr>
     <td><b>Unit Kerja</b></td>
     <td>: {$audit['nama_unit']}</td>
    </tr>
    <tr>
     <td><b>Ketua Auditor</b></td>
     <td>: {$audit['nama_auditor']}</td>
    </tr>
    <tr>
     <td><b>Tanggal Audit</b></td>
     <td>: {$audit['tanggal_mulai']} s.d {$audit['tanggal_selesai']}</td>
    </tr>
    <tr>
     <td><b>Jenis Audit</b></td>
     <td>: {$audit['jenis_audit']}</td>
    </tr>
   </table>
";
$pdf->writeHTML($html,true,false,true,false,'');


/* tim audit */
//$pdf->Ln(10);
//$pdf->SetFont('helvetica','B',12);
//$pdf->Cell(0,8,'Tim Auditor:',0,1);
//$pdf->SetFont('helvetica','',11);
//$no = 1;
//while($tim = mysqli_fetch_assoc($qTim))
//{
//    $pdf->Cell(0,8,$no . '. ' . $tim['nama_auditor'] . ' (' .$tim['peran'] . ')',0,1);
//    $no++;
//}


/* footer */
//$pdf->SetY(-40);
//$pdf->SetFont('helvetica','',8);
//$pdf->Cell(0,6,'Satuan Internal Audit (SIA)',0,1,'C');
//$pdf->Cell(0,6,'PT Jakarta Tourisindo',0,1,'C');
//$pdf->Cell(0,6,'Tahun ' . date('Y'),0,1,'C');

ob_end_clean();
//$pdf->Output('LHA_'.$audit['nomor_audit'].'.pdf','I');
//exit;

$dir = __DIR__.'/../uploads/lha/';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}
$filename ='LHA_'.$audit['nomor_audit'].'.pdf';
$filepath =__DIR__.'/../uploads/lha/'.$filename;
$pdf->Output($filepath,'F');

$pdf->Output(
    $filename,
    'I'
);

logActivity(
    $conn,
    'Generate LHA PDF',
    'audit_pemeriksaan',
    $id
);

mysqli_query($conn,"UPDATE audit_pemeriksaan SET lha_file='$filename' WHERE id='$id'");

exit;

/* utk download */
//$pdf->Output(
//    'LHA_'.$audit['nomor_audit'].'.pdf',
//    'D'
//);
