<?php
	class LaporanModel extends CI_Model {
		public function __construct() {
			$this->load->model('base/Base_model','base');
			parent::__construct();
		}

		public function get_customer_ipl_bill_details($id) {
			$header = array();
			$details = array();
			$query = "SELECT pay.CodeValue AS PaymentCode,pay.BillAmount, pay.Discount, pay.TotalAmount,
						bill.CodeValue AS BillingCode, DATE_FORMAT(pay.CreatedOn, '%d %M %Y') AS PaymentDate,
						cust.Id AS CustomerId, CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
						unit.NameValue AS Unit, unit.Id AS UnitId,
						blk.NameValue AS Block, clus.NameValue AS Cluster,
						ipl.NameValue AS Tariff, bill.Amount AS TariffAmount, bill.Tax,
						CASE WHEN pen.Amount IS NULL THEN 0.00 ELSE pen.Amount END AS PenaltyAmount,
						TRUNCATE(((bill.Amount + IFNULL(pen.Amount, 0.00)) * (100 + bill.Tax) / 100), 2) AS TotalBillPerMonth,
						DATE_FORMAT(bill.DueDate, '%d-%m-%Y') AS DueDate,
						DATE_FORMAT(bill.Period, '%d-%m-%Y') AS Period
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
						INNER JOIN TrxPayment pay ON bill.PaymentId = pay.Id
						LEFT JOIN TrxPenaltyDetails pen ON bill.Id = pen.BillingId
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
						AND bill.IsSettle = 1 AND pay.RecordStatus = 1 AND bill.RecordStatus = 1 AND ipl.ModuleId = bill.ModuleId
						AND pay.Id = ".$id."
						ORDER BY bill.Period";
			$billing_list = $this->db->query($query)->result();
			foreach ($billing_list as $row) {
				$item = array('BillingCode' => $row->BillingCode,
								'TariffAmount' => number_format($row->TariffAmount),
								'Tax' => $row->Tax,
							 	'PenaltyAmount' => number_format($row->PenaltyAmount),
								'TotalBillPerMonth' => number_format($row->TotalBillPerMonth),
								'Period' => $row->Period,
								'DueDate' => $row->DueDate
							);
				array_push($details, $item);
			}
			$header = array('PaymentCode' => $billing_list[0]->PaymentCode,
							'PaymentDate' => $billing_list[0]->PaymentDate,
							'BillAmount' => number_format($billing_list[0]->BillAmount),
							'Discount' => $billing_list[0]->Discount,
							'TotalAmount' => number_format($billing_list[0]->TotalAmount),
							'CustomerId' => $billing_list[0]->CustomerId,
							'CustomerName' => $billing_list[0]->CustomerName,
							'UnitId' => $billing_list[0]->UnitId,
							'Unit' => $billing_list[0]->Unit,
							'Block' => $billing_list[0]->Block,
							'Cluster' => $billing_list[0]->Cluster,
							'Tariff' => $billing_list[0]->Tariff,
							'PeriodStart' => date_format(date_create($details[0]['Period']), 'M Y'),
							'PeriodEnd' => date_format(date_create($details[sizeof($details)-1]['Period']), 'M Y'),
							'DetailAmount' => $details
						);
			return $header;
		}

		public function get_customer_pam_bill_details($id) {
			$header = array();
			$details = array();
			$query = "SELECT pay.CodeValue AS PaymentCode,pay.BillAmount, pay.Discount, pay.TotalAmount,
						bill.CodeValue AS BillingCode, DATE_FORMAT(pay.CreatedOn, '%d %M %Y') AS PaymentDate,
						cust.Id AS CustomerId, CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName, pay.PAMReactiveCost, unit.NameValue AS Unit, unit.Id AS UnitId,
						blk.NameValue AS Block, clus.NameValue AS Cluster,
						pam.NameValue AS Tariff, bill.PamLastMonth, bill.PamThisMonth,
                        bill.PamCubic, pam.AmountPerCubic AS TariffAmount, pam.SubsAmount,
						CASE WHEN pen.Amount IS NULL THEN 0.00 ELSE pen.Amount END AS PenaltyAmount,
						TRUNCATE((bill.TotalAmount * (100 + bill.Tax) / 100) + (IFNULL(pen.Amount, 0.00)), 2) AS TotalBillPerMonth,
						DATE_FORMAT(bill.DueDate, '%d-%m-%Y') AS DueDate,
						DATE_FORMAT(bill.Period, '%d-%m-%Y') AS Period
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
						INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
						INNER JOIN TrxPayment pay ON bill.PaymentId = pay.Id
						LEFT JOIN TrxPenaltyDetails pen ON bill.Id = pen.BillingId
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
						AND bill.IsSettle = 1 AND pay.RecordStatus = 1 AND bill.RecordStatus = 1 AND pam.ModuleId = bill.ModuleId
						AND pay.Id = ".$id."
						ORDER BY bill.Period";
			$billing_list = $this->db->query($query)->result();
			foreach ($billing_list as $row) {
				$item = array('BillingCode' => $row->BillingCode,
								'TariffAmount' => number_format($row->TariffAmount),
								'Abonemen' => number_format($row->SubsAmount),
								'PamLastMonth' => $row->PamLastMonth,
								'PamThisMonth' => $row->PamThisMonth,
								'PamCubic' => $row->PamCubic,
							 	'PenaltyAmount' => number_format($row->PenaltyAmount),
								'TotalBillPerMonth' => number_format($row->TotalBillPerMonth),
								'Period' => $row->Period,
								'DueDate' => $row->DueDate
							);
				array_push($details, $item);
			}
			$header = array('PaymentCode' => $billing_list[0]->PaymentCode,
							'PaymentDate' => $billing_list[0]->PaymentDate,
							'BillAmount' => number_format($billing_list[0]->BillAmount),
							'Discount' => $billing_list[0]->Discount,
							'TotalAmount' => number_format($billing_list[0]->TotalAmount),
							'CustomerId' => $billing_list[0]->CustomerId,
							'CustomerName' => $billing_list[0]->CustomerName,
							'UnitId' => $billing_list[0]->UnitId,
							'Unit' => $billing_list[0]->Unit,
							'Block' => $billing_list[0]->Block,
							'Cluster' => $billing_list[0]->Cluster,
							'Tariff' => $billing_list[0]->Tariff,
							'PeriodStart' => date_format(date_create($details[0]['Period']), 'M Y'),
							'PeriodEnd' => date_format(date_create($details[sizeof($details)-1]['Period']), 'M Y'),
							'PAMReactiveCost' => number_format($billing_list[0]->PAMReactiveCost),
							'DetailAmount' => $details
						);
			return $header;
		}

		public function generate_billing_report($param) {
			

			$header = array();
			$details = array();
			$grand = 0;
			$query = "SELECT CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
						unit.NameValue AS Unit,
						blk.NameValue AS Block, clus.NameValue AS Cluster,
						modul.NameValue AS Module,
						DATE_FORMAT(bill.Period, '%d %M %Y') AS Period,
						DATE_FORMAT(pay.CreatedOn, '%d %M %Y %H:%i:%s') AS PaymentDate,
						met.NameValue AS PaymentMethod,
						pay.TotalAmount
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
						INNER JOIN RefModule modul ON bill.ModuleId = modul.Id
						INNER JOIN TrxPayment pay ON bill.PaymentId = pay.Id
						INNER JOIN RefPaymentMethod met ON pay.PaymentMethodId = met.Id
						WHERE bill.RecordStatus = 1 AND pay.RecordStatus = 1
						AND modul.NameValue IN (".$param['Module'].")
						AND met.Id IN (".$param['PaymentMethodId'].")
						AND DATE(pay.CreatedOn) BETWEEN '".$param['StartDate']."' AND '".$param['EndDate']."'
						GROUP BY pay.Id ORDER BY pay.Id";
			$billing_list = $this->db->query($query)->result();
			foreach ($billing_list as $row) {
				$grand += $row->TotalAmount;
				$item = array('CustomerName' => $row->CustomerName,
								'Unit' => $row->Unit,
								'Block' => $row->Block,
								'Cluster' => $row->Cluster,
								'Module' => $row->Module,
								'Period' => $row->Period,
							 	'PaymentDate' => $row->PaymentDate,
								'PaymentMethod' => $row->PaymentMethod,
								'TotalAmount' => number_format($row->TotalAmount)
							);
				array_push($details, $item);
			}
			$header = array('GrandTotalAmount' => number_format($grand),
							'StartDate' => date_format(date_create($param['StartDate']), 'd F Y'),
							'EndDate' => date_format(date_create($param['EndDate']), 'd F Y'),
							'Details' => $details
						);
			return $header;
		}
	}
?>