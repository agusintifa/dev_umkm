<?php
	/*$pdf = new FPDF('L','mm','A4');
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);
	$pdf->Cell(40,10,'Hello World!');
	$pdf->Output();*/

	$pdf = new FPDF("L","cm","A3");
	// kita set marginnya dimulai dari kiri, atas, kanan. jika tidak diset, defaultnya 1 cm
	$pdf->SetMargins(0.75,1,0.5); 
	/* AliasNbPages() merupakan fungsi untuk menampilkan total halaman di footer, nanti kita akan membuat page number dengan format : number page / total page */
	$pdf->AliasNbPages(); 
	// AddPage merupakan fungsi untuk membuat halaman baru 
	$pdf->AddPage(); 
	// Setting Font : String Family, String Style, Font size 
	$pdf->SetFont('Times','B',12); 

	/* Kita akan membuat header dari halaman pdf yang kita buat -------------- Header Halaman dimulai dari baris ini ----------------------------- */ 
	$pdf->Cell(41,0.7,$this->config->item('invoice_header'),0,0,'C'); 
	// fungsi Ln untuk membuat baris baru 
	$pdf->Ln();
	/* Setting ulang Font : String Family, String Style, Font size kenapa disetting ulang ??? jika tidak disetting ulang, ukuran font akan mengikuti settingan sebelumnya. tetapi karena kita menginginkan settingan untuk penulisan alamatnya berbeda, maka kita harus mensetting ulang Font nya. jika diatas settingannya : helvetica, 'B', '12' khusus untuk penulisan alamat, kita setting : helvetica, '', 10 yang artinya string stylenya normal / tidak Bold dan ukurannya 10 */
	$pdf->SetFont('helvetica','',10); 
	$pdf->Cell(41,0.5,$this->config->item('invoice_address'),0,0,'C'); 
	$pdf->Ln(); 
	$pdf->Cell(41,0.5,$this->config->item('invoice_contact'),0,0,'C'); 
	/* Fungsi Line untuk membuat garis */ 
	$pdf->Line(0.3,3.0,41.4,3.0); 
	$pdf->Line(0.3,3.1,41.4,3.1); 
	/* -------------- Header Halaman selesai ------------------------------------------------*/ 
	$pdf->Ln(1); 
	$pdf->SetFont('Times','B',12); 
	$pdf->Cell(41.3,1,'LAPORAN TAGIHAN',0,0,'C');

	$pdf->Ln(1.5); 
	$pdf->Cell(5.8 , 0.7, 'Payment Date' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['StartDate'] . ' - ' . $hasil['EndDate'] , 0, 'LR', 'L');

	/* setting header table */ 
	$pdf->Ln(1.2);
	$pdf->SetFont('Times','B',12);
	$pdf->Cell(7 , 1, 'Customer' , 1, 'LR', 'C'); 
	$pdf->Cell(3 , 1, 'Unit' , 1, 'LR', 'C'); 
	$pdf->Cell(3 , 1, 'Block' , 1, 'LR', 'C'); 
	$pdf->Cell(3 , 1, 'Cluster' , 1, 'LR', 'C'); 
	$pdf->Cell(3 , 1, 'Module' , 1, 'LR', 'C');
	$pdf->Cell(4 , 1, 'Period' , 1, 'LR', 'C');
	$pdf->Cell(5 , 1, 'Payment Date' , 1, 'LR', 'C');
	$pdf->Cell(4 , 1, 'Payment Method' , 1, 'LR', 'C');
	$pdf->Cell(7 , 1, 'Total Amount' , 1, 'LR', 'C');
	/* generate hasil query disini */
	foreach($hasil['Details'] as $data) {
		$pdf->Ln(); 
		$pdf->SetFont('Times','',12); 
		$pdf->Cell(7 , 0.7, $data['CustomerName'] , 1, 'LR', 'L'); 
		$pdf->Cell(3 , 0.7, $data['Unit'] , 1, 'LR', 'C'); 
		$pdf->Cell(3 , 0.7, $data['Block'] , 1, 'LR', 'C');
		$pdf->Cell(3 , 0.7, $data['Cluster'] , 1, 'LR', 'C');
		$pdf->Cell(3 , 0.7, $data['Module'] , 1, 'LR', 'C');
		$pdf->Cell(4 , 0.7, $data['Period'] , 1, 'LR', 'C');
		$pdf->Cell(5 , 0.7, $data['PaymentDate'] , 1, 'LR', 'C');
		$pdf->Cell(4 , 0.7, $data['PaymentMethod'] , 1, 'LR', 'C');
		$pdf->Cell(7 , 0.7, $data['TotalAmount'] , 1, 'LR', 'R');
	}

	$pdf->Ln(1);
	$pdf->SetFont('Times','B',14);
	$pdf->Cell(30 , 1, 'GRAND TOTAL' , 0, 'LR', 'L'); 
	$pdf->Cell(9 , 1, $hasil['GrandTotalAmount'] , 0, 'LR', 'R');

	/* setting posisi footer 3 cm dari bawah */
	$pdf->SetMargins(0.75,1,0.5);
	$pdf->SetY(-3); 
	/* setting font untuk footer */ 
	$pdf->SetFont('Times','',10); 
	/* setting cell untuk waktu pencetakan */ 
	$pdf->Cell(9.5, 0.5, 'Printed on : '.date('d/m/Y H:i').'  |  Created by : ' . $this->session->userdata('user_session')->Username,0,'LR','L'); /* setting cell untuk page number */ 
	$pdf->Cell(31, 0.5, 'Page '.$pdf->PageNo().'/{nb}',0,0,'R'); 
	/* generate pdf jika semua konstruktor, data yang akan ditampilkan, dll sudah selesai */ 
	$pdf->Output("Laporan Billing - (" . $hasil['StartDate'] . '-' . $hasil['EndDate'] .").pdf","I"); ?>
?>