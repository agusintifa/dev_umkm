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
	$pdf->Cell(41.3,1,$this->config->item('invoice_title'),0,0,'C');

	$pdf->Ln(1.5); 
	$pdf->SetFont('Times','',12); 
	$pdf->Cell(5.8 , 0.7, 'NOMOR KWITANSI' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['PaymentCode'] , 0, 'LR', 'L');
	$pdf->Cell(26.6 , 0.7, 'UNTUK PEMBAYARAN IPL PERIODE   ' . $hasil['PeriodStart'] . ' s/d ' . $hasil['PeriodEnd'] , 0, 'LR', 'R');
	$pdf->Ln();
	$pdf->Cell(5.8 , 0.7, 'NAMA' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['CustomerName'] , 0, 'LR', 'L');
	$pdf->Ln();
	$pdf->Cell(5.8 , 0.7, 'UNIT/BLOK' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['Unit'] . ' / ' . $hasil['Block'] , 0, 'LR', 'L');
	$pdf->Ln();
	$pdf->Cell(5.8 , 0.7, 'CLUSTER' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['Cluster'] , 0, 'LR', 'L');
	$pdf->Ln();
	$pdf->Cell(5.8 , 0.7, 'SEKTOR' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['Tariff'] , 0, 'LR', 'L');
	$pdf->Ln();
	$pdf->Cell(5.8 , 0.7, 'DIBAYAR TGL' , 0, 'LR', 'L');
	$pdf->Cell(0.75 , 0.7, ':' , 0, 'LR', 'L');
	$pdf->Cell(5.8 , 0.7, $hasil['PaymentDate'] , 0, 'LR', 'L');

	/* setting header table */ 
	$pdf->Ln(1.2);
	$pdf->SetFont('Times','B',12);
	$pdf->Cell(7 , 1, 'Billing Code' , 1, 'LR', 'C'); 
	$pdf->Cell(4 , 1, 'Period' , 1, 'LR', 'C'); 
	$pdf->Cell(4 , 1, 'DueDate' , 1, 'LR', 'C'); 
	$pdf->Cell(7 , 1, 'Tariff' , 1, 'LR', 'C'); 
	$pdf->Cell(7 , 1, 'Denda' , 1, 'LR', 'C');
	$pdf->Cell(3 , 1, 'Tax (%)' , 1, 'LR', 'C');
	$pdf->Cell(7 , 1, 'Total' , 1, 'LR', 'C');
	/* generate hasil query disini */
	foreach($hasil['DetailAmount'] as $data) {
		$pdf->Ln(); 
		$pdf->SetFont('Times','',12); 
		$pdf->Cell(7 , 0.7, $data['BillingCode'] , 1, 'LR', 'L'); 
		$pdf->Cell(4 , 0.7, $data['Period'] , 1, 'LR', 'C'); 
		$pdf->Cell(4 , 0.7, $data['DueDate'] , 1, 'LR', 'C');
		$pdf->Cell(7 , 0.7, $data['TariffAmount'] , 1, 'LR', 'R');
		$pdf->Cell(7 , 0.7, $data['PenaltyAmount'] , 1, 'LR', 'R');
		$pdf->Cell(3 , 0.7, $data['Tax'] , 1, 'LR', 'R');
		$pdf->Cell(7 , 0.7, $data['TotalBillPerMonth'] , 1, 'LR', 'R');
	}

	$pdf->Ln(1);
	$pdf->SetFont('Times','B',14);
	$pdf->Cell(30 , 1, 'TOTAL' , 0, 'LR', 'L'); 
	$pdf->Cell(9 , 1, $hasil['BillAmount'] , 0, 'LR', 'R');

	$pdf->Ln();
	$pdf->SetFont('Times','B',14);
	$pdf->Cell(30 , 1, 'DISCOUNT (%)' , 0, 'LR', 'L'); 
	$pdf->Cell(9 , 1, $hasil['Discount'] , 0, 'LR', 'R');

	$pdf->Ln();
	$pdf->SetFont('Times','B',14);
	$pdf->Cell(30 , 1, 'GRAND TOTAL' , 0, 'LR', 'L'); 
	$pdf->Cell(9 , 1, $hasil['TotalAmount'] , 0, 'LR', 'R');


	// main - start
	/*$pdf->Ln(1.5);
	$pdf->SetFont('Times','',14);
	$pdf->Cell(22 , 2, 'TERBILANG: # ' . $this->base->terbilang($hasil['TotalAmount']) . '#' , 1, 'LR', 'L');
	$pdf->Cell(7.2 , 2, '' , 0, 'LR', 'L');
	$pdf->SetFont('Times','',12);
	$pdf->Cell(8 , 2, 'Jakarta, ' . date('d F Y') , 0, 'LR', 'C');
	$pdf->Ln(3.4);
	$pdf->SetFont('Times','',14);
	$pdf->Cell(17 , 2, '*) PEMBAYARAN INI SAH JIKA DIBUBUHI CASH REGISTER STAMP' , 0, 'LR', 'L');
	$pdf->Cell(12.2 , 2, '' , 0, 'LR', 'L');
	$pdf->Cell(8 , 2, strtoupper($this->session->userdata('user_session')->Username) , 0, 'LR', 'C');*/
	// main - end

	/* setting posisi footer 3 cm dari bawah */
	// $pdf->SetMargins(0.75,1,0.5);
	
	// $pdf->SetY(-7.5);
	$pdf->Ln(1);
	// main - start
	
	$pdf->SetFont('Times','',14);
	$pdf->Cell(22 , 2, 'TERBILANG: # ' . $this->base->terbilang($hasil['TotalAmount']) . '#' , 1, 'LR', 'L');
	$pdf->Cell(7.2 , 2, '' , 0, 'LR', 'L');
	$pdf->SetFont('Times','',12);
	$pdf->Cell(8 , 2, 'Jakarta, ' . date('d F Y') , 0, 'LR', 'C');
	$pdf->Ln(3.4);
	$pdf->SetFont('Times','',14);
	$pdf->Cell(17 , 2, '*) PEMBAYARAN INI SAH JIKA DIBUBUHI CASH REGISTER STAMP' , 0, 'LR', 'L');
	$pdf->Cell(12.2 , 2, '' , 0, 'LR', 'L');
	$pdf->Cell(8 , 2, strtoupper($this->session->userdata('user_session')->Username) , 0, 'LR', 'C');
	// main - end
	$pdf->Ln(2);
	$pdf->Cell(17 , 2, 'Printed on : '.date('d/m/Y H:i').'  |  Created by : ' . $this->session->userdata('user_session')->Username , 0, 'LR', 'L');
	$pdf->Cell(12.2 , 2, '' , 0, 'LR', 'L');
	$pdf->Cell(8 , 2, '' , 0, 'LR', 'C');


	// footer - start
	// $pdf->SetFont('Times','',10); 
	// $pdf->Cell(9.5, 0.5, 'Printed on : '.date('d/m/Y H:i').'  |  Created by : ' . $this->session->userdata('user_session')->Username,0,'LR','L'); /* setting cell untuk page number */ 
	// $pdf->Cell(31, 0.5, 'Page '.$pdf->PageNo().'/{nb}',0,0,'R'); 
	// footer - end



	/* generate pdf jika semua konstruktor, data yang akan ditampilkan, dll sudah selesai */ 
	$pdf->Output("Bukti Pembayaran IPL - ".$hasil['CustomerName']. "(" . $hasil['PeriodStart'] . '-' . $hasil['PeriodEnd'] .").pdf","I"); ?>
?>