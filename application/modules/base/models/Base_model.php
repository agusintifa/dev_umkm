<?php
	class Base_model extends CI_Model {
		public function __construct() {
			parent::__construct();
			if (!$this->session->userdata('user_session'))
				return redirect('login');
		}

		public function generate_new_code($tablename) {
			$sql = "SELECT Id, FormatCode, LastCodeNumber FROM SysSequenceNumber WHERE TableName = '".$tablename."' LIMIT 1";
			$res = $this->db->query($sql)->row();
			$num = (int)$res->LastCodeNumber;
			$num += 1;
			$this->db->query("UPDATE SysSequenceNumber SET LastCodeNumber = '".$num."' WHERE Id = " . $res->Id);
			return substr($res->FormatCode, 0, (strlen((string)$num) * -1)) . (string)$num;
		}

		public function generate_code_table($tablename) {
			$obj = $this->db->query("SELECT generate_code_table('".$tablename."') AS res")->row();
			return $obj->res;
		}

		public function get_column_order($arr, $total_column) {
			$res = array();
			$i = 0;
			while ($i < $total_column) {
				if (isset($arr[$i]))
					$res[$i] = $arr[$i];
				else
					$res[$i] = null;
				$i++;
			}
			return $res;
		}

		public function get_total_billing($custid, $unitid) {
			$header = array();
			$details = array();
			$total_billing = 0.00;
			$total_penalty = 0.00;
			$query = "SELECT cust.Id AS CustomerId, CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
						unit.NameValue AS Unit, unit.Id AS UnitId,
						blk.NameValue AS Block, clus.NameValue AS Cluster, clus.Id AS ClusterId,
						ipl.NameValue AS Tariff, ipl.Amount AS TariffAmount,
						ipl.Tax AS Tax, TRUNCATE((ipl.Amount * (100 + ipl.Tax) / 100), 2) AS BillPerMonth,
						bill.Period, bill.DueDate, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, CURDATE() AS Today,
						bill.Id AS BillId
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
						INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI' AND cust.Id = ".$custid." AND unit.Id = ".$unitid."
						AND bill.IsSettle = 0 AND bill.RecordStatus = 1 AND ipl.ModuleId = bill.ModuleId
						ORDER BY bill.Period";
			$penalty_list = $this->db->query($query)->result();
			$counter = 0;
			foreach ($penalty_list as $row) {
				$penalty_amt = $row->Today > $row->DueDate && $counter < $this->config->item('ipl_max_penalty_month') ? $row->PenaltyAmount : 0.00;
				$tot_amt = (($row->TariffAmount + $penalty_amt) * (100 + $row->Tax) / 100);
				$item = array('Period' => $row->Period,
								'DueDate' => $row->DueDate,
							 	'BillAmount' => $row->TariffAmount,
								'Tax' => $row->Tax,
								'PenaltyName' => $row->PenaltyName,
								'PenaltyAmount' => $penalty_amt,
								'TotalAmount' => $tot_amt,
								'BillId' => $row->BillId
							);
				$total_billing += $tot_amt;
				$total_penalty += $penalty_amt;
				array_push($details, $item);
				$counter++;
			}
			$header = array('TariffName' => $penalty_list[0]->Tariff,
							'TotalBilling' => $total_billing,
							'TotalPenalty' => $total_penalty,
							'CustomerId' => $penalty_list[0]->CustomerId,
							'CustomerName' => $penalty_list[0]->CustomerName,
							'UnitId' => $penalty_list[0]->UnitId,
							'Unit' => $penalty_list[0]->Unit,
							'Block' => $penalty_list[0]->Block,
							'ClusterId' => $penalty_list[0]->ClusterId,
							'Cluster' => $penalty_list[0]->Cluster,
							'BillPerMonth' => $penalty_list[0]->BillPerMonth,
							'DetailAmount' => $details
						);
			return $header;
		}

		public function get_total_multiple_billing($custid, $unitid) {
			$header = array();
			$details = array();
			$multi_bills = array();
			$total_billing = 0.00;
			$total_penalty = 0.00;
			$query = "SELECT cust.Id AS CustomerId, unit.Id AS UnitId, modul.Id AS ModuleId,
					CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
					unit.NameValue AS Unit,
					blk.NameValue AS Block, clus.NameValue AS Cluster,
					ipl.NameValue AS Tariff, ipl.Amount AS TariffAmount,
					ipl.Tax AS Tax, TRUNCATE((ipl.Amount * (100 + ipl.Tax) / 100), 2) AS BillPerMonth,
					bill.Period, bill.DueDate, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, CURDATE() AS Today,
					bill.Id AS BillId
					FROM RelCustomerUnit rel
					INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
					INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
					INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
					INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
					INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
					INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
					INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
					INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
					INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
					WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI' AND cust.Id = ".$custid." AND unit.Id = ".$unitid."
					AND bill.IsSettle = 0 AND bill.RecordStatus = 1 AND ipl.ModuleId = bill.ModuleId
					ORDER BY bill.Period";
			$bill_infor = $this->db->query($query)->result();
			$counter = 0;
			//generate_penalty_details
			foreach ($bill_infor as $row) {
				$penalty_amt = $row->Today > $row->DueDate && $counter < $this->config->item('ipl_max_penalty_month') ? $row->PenaltyAmount : 0.00;
				$tot_amt = (($row->TariffAmount + $penalty_amt) * (100 + $row->Tax) / 100);
				$item = array('Period' => $row->Period,
								'DueDate' => $row->DueDate,
							 	'BillAmount' => $row->TariffAmount,
								'Tax' => $row->Tax,
								'PenaltyName' => $row->PenaltyName,
								'PenaltyAmount' => $penalty_amt,
								'TotalAmount' => $tot_amt,
								'BillId' => $row->BillId
							);
				$total_billing += $tot_amt;
				$total_penalty += $penalty_amt;
				array_push($details, $item);
				$counter++;
			}

			//generate_multi_bills
			$b_det = array('ModuleId' => $bill_infor[0]->ModuleId,
							'Amount' => $bill_infor[0]->TariffAmount,
							'Tax' => $bill_infor[0]->Tax,
							'TotalAmount' => $bill_infor[0]->BillPerMonth
						);
			array_push($multi_bills, $b_det);

			$header = array('TariffName' => $bill_infor[0]->Tariff,
							'TotalBilling' => $total_billing,
							'TotalPenalty' => $total_penalty,
							'CustomerId' => $bill_infor[0]->CustomerId,
							'UnitId' => $bill_infor[0]->UnitId,
							'CustomerName' => $bill_infor[0]->CustomerName,
							'Unit' => $bill_infor[0]->Unit,
							'Block' => $bill_infor[0]->Block,
							'Cluster' => $bill_infor[0]->Cluster,
							'DetailAmount' => $details,
							'MultiBillDetails' => $multi_bills
						);
			return $header;
		}

		public function calculate_pam_billing($totalcubic, $amount_per_cubic, $abonemen) {
			$bill = $totalcubic * $amount_per_cubic;
			if ($bill < $abonemen)
				return $abonemen;
			return $bill;
		}

		public function get_total_billing_pam($custid, $unitid) {
			$header = array();
			$details = array();
			$total_billing = 0.00;
			$total_penalty = 0.00;
			$pam_cost = 0.00;
			$query = "SELECT cust.Id AS CustomerId, CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
					unit.NameValue AS Unit, unit.Id AS UnitId,
					blk.NameValue AS Block, clus.NameValue AS Cluster,
					pam.NameValue AS Tariff, bill.PAMCubic,
					bill.Tax AS Tax, bill.TotalAmount,
					bill.Period, bill.DueDate, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, CURDATE() AS Today,
					bill.Id AS BillId
					FROM RelCustomerUnit rel
					INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
					INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
					INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
					INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
					INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
					INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
					INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
					INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
					INNER JOIN RefPenalty pen ON pam.PenaltyId = pen.Id
					WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI' AND cust.Id = ".$custid." AND unit.Id = ".$unitid."
					AND bill.IsSettle = 0 AND bill.RecordStatus = 1 AND pam.ModuleId = bill.ModuleId
					ORDER BY bill.Period";
			$penalty_list = $this->db->query($query)->result();
			// var_dump($penalty_list);die();
			$counter = 0;
			foreach ($penalty_list as $row) {
				$penalty_amt = $row->Today > $row->DueDate && $counter < $this->config->item('pam_max_penalty_month') ? $row->PenaltyAmount : 0.00;
				$item = array('Period' => $row->Period,
								'DueDate' => $row->DueDate,
							 	'BillAmount' => $row->TotalAmount,
								'Tax' => $row->Tax,
								'PenaltyName' => $row->PenaltyName,
								'PenaltyAmount' => $penalty_amt,
								'TotalAmount' => ($row->TotalAmount + $penalty_amt),
								'BillId' => $row->BillId
							);
				$total_billing += ($row->TotalAmount + $penalty_amt);
				$total_penalty += $penalty_amt;
				array_push($details, $item);
				$counter++;
			}

			//check_cost_for_reactivate
			if (count($details) >= $this->config->item('pam_max_penalty_month')) {
				$pam_cost = $this->config->item('pam_reactivate_cost');
			}

			$header = array('TariffName' => $penalty_list[0]->Tariff,
							'TotalBilling' => $total_billing,
							'TotalPenalty' => $total_penalty,
							'CustomerId' => $penalty_list[0]->CustomerId,
							'CustomerName' => $penalty_list[0]->CustomerName,
							'UnitId' => $penalty_list[0]->UnitId,
							'Unit' => $penalty_list[0]->Unit,
							'Block' => $penalty_list[0]->Block,
							'Cluster' => $penalty_list[0]->Cluster,
							'PamReactivateCost' => $pam_cost,
							'DetailAmount' => $details
						);
			return $header;
		}

		public function get_ddl_cluster() {
			$query = "SELECT Id AS 'value', NameValue AS 'label' FROM RefCluster
					WHERE RecordStatus = 1 ORDER BY NameValue";
			return $this->db->query($query)->result();
		}

		public function removeAllComma($param) {
			return strtr($param, array(',' => ''));
		}

		public function is_customer_has_unsettle_pam($custid, $unitid) {
			$query = "SELECT COUNT(cust.Id) AS TotalUnsettleBill
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
						INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
						LEFT JOIN TrxPayment payment ON bill.PaymentId = payment.Id
						LEFT JOIN RefPaymentMethod pay ON payment.PaymentMethodId = pay.Id
						WHERE rel.RecordStatus = 1 AND bill.RecordStatus = 1 AND (bill.IsSettle IS NULL OR bill.IsSettle = 0)
						AND stat.NameValue = 'BERPENGHUNI' AND pam.ModuleId = bill.ModuleId
						AND cust.Id = ".$custid." AND unit.Id = ".$unitid."
						GROUP BY cust.Id, cust.FirstName, cust.LastName, unit.Id
						HAVING COUNT(cust.Id) >= " . $this->config->item('pam_max_penalty_month');
			$res = $this->db->query($query)->row();
			if ($res == null) {
				return array('stat' => false, 'PamUnsettleBill' => null);
			}
			return array('stat' => true, 'PamUnsettleBill' => $res->TotalUnsettleBill);
		}

		public function toNum($str_num) {
		return (int)str_replace(',', '', $str_num);
		}

		public function create_ddl_cluster() {
			$list = $this->get_ddl_cluster();
			$opt = array('' => 'All Cluster');
			foreach ($list as $cluster) {
				$opt[$cluster->value] = $cluster->label;
			}
			return form_dropdown('',$opt,'','id="cluster_filter" class="form-control"');
		}

		public function create_ddl_paymethod() {
			$query = "SELECT Id AS 'value', NameValue AS 'label' FROM RefPaymentMethod WHERE RecordStatus = 1";
			$list = $this->db->query($query)->result();
			$opt = array('' => '');
			foreach ($list as $item) {
				$opt[$item->value] = $item->label;
			}
			return form_dropdown('',$opt,'','id="PaymentMethodId" name="PaymentMethodId" class="form-control"');
		}

		public function create_ddl_role() {
			$query = "SELECT Id AS 'value', NameValue AS 'label' FROM SysRole WHERE RecordStatus = 1";
			$list = $this->db->query($query)->result();
			$opt = array();
			foreach ($list as $item) {
				$opt[$item->value] = $item->label;
			}
			return form_dropdown('',$opt,'','id="RoleId" name="RoleId" class="form-control"');
		}

		public function create_ddl_penalty() {
			$query = "SELECT Id AS 'value', NameValue AS 'label' FROM RefPenalty WHERE RecordStatus = 1";
			$list = $this->db->query($query)->result();
			$opt = array();
			foreach ($list as $item) {
				$opt[$item->value] = $item->label;
			}
			return form_dropdown('',$opt,'','id="PenaltyId" name="PenaltyId" class="form-control"');
		}

		public function create_ddl_paymethod2() {
			$query = "SELECT Id AS 'value', NameValue AS 'label' FROM RefPaymentMethod WHERE RecordStatus = 1";
			$list = $this->db->query($query)->result();
			$opt = array('All' => '-All-');
			foreach ($list as $item) {
				$opt[$item->value] = $item->label;
			}
			return form_dropdown('',$opt,'','id="PaymentMethodId" name="PaymentMethodId" class="form-control"');
		}

		public function terbilang($angka) {
	        $angka = (float)$this->toNum($angka);
	        $bilangan = array('','SATU','DUA','TIGA','EMPAT','LIMA','ENAM','TUJUH','DELAPAN','SEMBILAN','SEPULUH','SEBELAS');
	        if ($angka < 12) {
	            return $bilangan[$angka];
	        } else if ($angka < 20) {
	            return $bilangan[$angka - 10] . ' BELAS';
	        } else if ($angka < 100) {
	            $hasil_bagi = (int)($angka / 10);
	            $hasil_mod = $angka % 10;
	            return trim(sprintf('%s PULUH %s', $bilangan[$hasil_bagi], $bilangan[$hasil_mod]));
	        } else if ($angka < 200) { return sprintf('SERATUS %s', $this->terbilang($angka - 100));
	        } else if ($angka < 1000) { $hasil_bagi = (int)($angka / 100); $hasil_mod = $angka % 100; return trim(sprintf('%s RATUS %s', $bilangan[$hasil_bagi], $this->terbilang($hasil_mod)));
	        } else if ($angka < 2000) { return trim(sprintf('SERIBU %s', $this->terbilang($angka - 1000)));
	        } else if ($angka < 1000000) { $hasil_bagi = (int)($angka / 1000); $hasil_mod = $angka % 1000; return sprintf('%s RIBU %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod));
	        } else if ($angka < 1000000000) { $hasil_bagi = (int)($angka / 1000000); $hasil_mod = $angka % 1000000; return trim(sprintf('%s JUTA %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
	        } else if ($angka < 1000000000000) { $hasil_bagi = (int)($angka / 1000000000); $hasil_mod = fmod($angka, 1000000000); return trim(sprintf('%s MILYAR %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
	        } else if ($angka < 1000000000000000) { $hasil_bagi = $angka / 1000000000000; $hasil_mod = fmod($angka, 1000000000000); return trim(sprintf('%s TRILIUN %s', $this->terbilang($hasil_bagi), $this->terbilang($hasil_mod)));
	        } else {
	            return 'FORMAT ANGKA SALAH';
	        }
    	}
	}
?>