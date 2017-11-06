<?php
	class PamModel extends CI_Model {
		public function __construct() {
			$this->load->model('base/Base_model','base');
			parent::__construct();
		}

		//list_tagihan_pam start
		private $list_tagihan_pam_query = "SELECT bill.Id, cust.Id AS CustId, payment.Id AS PaymentId, 
											CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
											unit.Id AS UnitId, unit.NameValue AS Unit, unit.Id AS UnitId,
											blk.NameValue AS Blok, clus.NameValue AS Cluster,
											pam.NameValue AS Tariff, modul.NameValue AS Module,
											stat.NameValue AS UnitStatus,
											bill.CodeValue, bill.Period, bill.DueDate, pay.NameValue AS PaymentMethod,
											bill.Amount, bill.Tax, bill.PAMCubic, bill.TotalAmount, bill.IsSettle,
											CASE WHEN bill.Id IS NOT NULL THEN
											IFNULL((SELECT SUM(Amount) FROM TrxPenaltyDetails WHERE BillingId = bill.Id GROUP BY BillingId LIMIT 1),0.00)
											ELSE 0.00 END AS TotalPenaltyAmount
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
											WHERE rel.RecordStatus = 1 AND (bill.RecordStatus IS NULL OR bill.RecordStatus = 1)
											AND stat.NameValue = 'BERPENGHUNI' AND pam.MOduleId = bill.ModuleId";
		private $list_tagihan_pam_query_counter = "SELECT COUNT(1) AS TotalRow FROM RelCustomerUnit rel
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
											WHERE rel.RecordStatus = 1 AND (bill.RecordStatus IS NULL OR bill.RecordStatus = 1)
											AND stat.NameValue = 'BERPENGHUNI' AND pam.MOduleId = bill.ModuleId LIMIT 1";
		private function _get_list_tagihan_pam() {
			$column_order = $this->base->get_column_order(array('1' => 'cust.FirstName,cust.LastName',
													'2' => 'unit.NameValue',
													'9' => 'bill.Period'),20);
			$column_search = array('cust.FirstName','cust.LastName', 'unit.NameValue','bill.Period');
			$orderby = array('bill.Id' => 'DESC');

			$query = $this->list_tagihan_pam_query;
			$i = 0;

			//custom_filter
			if ($this->input->post('cluster_filter') != "") {
				$query .= " AND clus.Id = " . $this->input->post('cluster_filter');
			}

			if (!empty($_POST)) {
				foreach ($column_search as $item) {
					if($_POST['search']['value']) {
						if($i===0) {
							$query .= " AND (";
							$query .= " " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						} else {
							$query .= " OR " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						}

						if(count($column_search) - 1 == $i)
							$query .= ')';
					}
					$i++;
				}
		
				if(isset($_POST['order'])) {
					$query .= " ORDER BY " . $column_order[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'];
				} else if(isset($orderby)) {
					$order = $orderby;
					$query .= " ORDER BY " . key($order) . " " . $order[key($order)];
				}
			}
			return $query;
		}

		function get_list_tagihan_pam() {
			$query =  $this->_get_list_tagihan_pam();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_tagihan_pam() {
			$query =  $this->_get_list_tagihan_pam();
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_tagihan_pam() {
			return $this->db->query($this->list_tagihan_pam_query_counter)->row()->TotalRow;
		}

		public function get_by_id_list_tagihan_pam($id) {
			return $this->db->query($this->list_tagihan_pam_query . " AND bill.Id = " . $id)->row();
		}

		public function save($param) {
			$customer_pam_infor = $this->db->query("SELECT unit.Id AS UnitId, pam.NameValue AS Tariff, pam.AmountPerCubic,
									pam.Tax, pam.SubsAmount AS Abonemen, modul.Id AS ModuleId,
									IFNULL((SELECT b.PamThisMonth FROM TrxBilling b WHERE b.CustomerId = ".$param['CustomerId']." AND b.UnitId = ".$param['UnitId']." AND b.RecordStatus = 1 AND b.ModuleId = modul.Id
									AND SUBSTRING(b.Period, 1, 7) = SUBSTRING(DATE_SUB('".$param['Period']."', INTERVAL 1 MONTH), 1, 7)), 0) AS PamLastMonth
									FROM RelCustomerUnit rel
									INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
									INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
									INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
									INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
									INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
									INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
									INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
									WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
									AND NOT EXISTS(SELECT bill.Id FROM TrxBilling bill WHERE
									cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND pam.ModuleId = bill.ModuleId
									AND SUBSTRING(bill.Period, 1, 7) = SUBSTRING('".$param['Period']."', 1, 7))
									AND cust.Id = ".$param['CustomerId']." AND unit.Id = ".$param['UnitId']." LIMIT 1")->row();
			if ($customer_pam_infor == null) {
				return false;
			}
			$data = array(
						'CodeValue' => $this->base->generate_code_table('TrxBilling'),
						'CustomerId' => $param['CustomerId'],
						'UnitId' => $param['UnitId'],
						'ModuleId' => $customer_pam_infor->ModuleId,
						'Period' => $param['Period'],
						'DueDate' => substr($param['Period'], 0, -2) . $this->config->item('bill_due_day'),
						'PamLastMonth' => $customer_pam_infor->PamLastMonth,
						'PamThisMonth' => $param['PamThisMonth'],
						'PamCubic' => $param['PamCubic'],
						'Amount' => ($param['PamCubic'] * $customer_pam_infor->AmountPerCubic),
						'Tax' => $customer_pam_infor->Tax,
						'TotalAmount' => $this->base->calculate_pam_billing($param['PamCubic'], $customer_pam_infor->AmountPerCubic, $customer_pam_infor->Abonemen),
						'PaymentId' => NULL,
						'Notes' => $param['Notes'],
						'CreatedBy' => $this->session->userdata('user_session')->Username,
						'CreatedOn' => date('Y-m-d H:i:s'),
						'IsSettle' => 0,
						'SettledBy' => NULL,
						'SettlementDateTime' => NULL,
						'RecordStatus' => 1
					);
			$this->db->insert('TrxBilling', $data);
			return $this->db->insert_id();
		}

		public function update_list_tagihan_pam($data) {
			$billing = $this->base->get_total_billing_pam($data['CustomerId'], $data['UnitId']);
			// var_dump($billing['PamReactivateCost']);die();
			//1. insert_payment
			$this->db->insert('TrxPayment',
								array('CodeValue' => $this->base->generate_code_table('TrxPayment'),
								'BillAmount' => $billing['TotalBilling'],
								'PAMReactiveCost' => $billing['PamReactivateCost'],
								'Discount' => $data['Discount'],
								'PaymentMethodId' => $data['PaymentMethodId'],
								'TotalAmount' => (($billing['TotalBilling'] + $billing['PamReactivateCost']) * (100 - $data['Discount']) / 100),
								'Paid' => $data['Paid'],
								'ChangeAmount' => $data['ChangeDue'],
								'Notes' => $data['Notes'],
								'CreatedBy' => $data['Username'],
								'CreatedOn' => date('Y-m-d H:i:s'),
								'RecordStatus' => 1
								)
							);
			$paymentid = $this->db->insert_id();
			//2. update_billing
			foreach ($billing['DetailAmount'] as $row) {
				$this->db->update('TrxBilling',
							array('PaymentId' => $paymentid,
								'IsSettle' => 1,
								'SettledBy' => $data['Username'],
								'SettlementDateTime' => date('Y-m-d H:i:s')),
							array('Id' => $row['BillId'])
							);
				//3. insert_penalty_details
				if ($row['PenaltyAmount'] > 0) {
					$this->db->insert('TrxPenaltyDetails',
									array('BillingId' => $row['BillId'],
									'PenaltyName' => $row['PenaltyName'],
									'Amount' => $row['PenaltyAmount']
									)
								);
				}
				
			}
			return $this->db->affected_rows();
		}

		public function get_ddl_unit($clusterid) {
			$query = "SELECT DISTINCT unit.Id AS 'value',
						unit.NameValue AS 'label', cust.Id AS CustomerId,
						CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
						INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI' AND clus.Id = ".$clusterid."
						ORDER BY unit.NameValue";
			return $this->db->query($query)->result();
		}

		public function generate_billing_customer($param) {
			$query = "SELECT rel.Id as RelId, cust.Id AS CustId,
						CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
						unit.Id AS UnitId, unit.NameValue AS Unit,
						blk.NameValue AS Blok, clus.NameValue AS Cluster,
						pam.NameValue AS Tariff,
						pam.SubsAmount AS TariffAmount,
						pam.Tax AS Tax,
						modul.Id AS ModuleId, modul.NameValue AS Module,
						stat.NameValue AS UnitStatus
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
						INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						LEFT JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND bill.Period = '".$param['period']."' AND pam.ModuleId = bill.ModuleId
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI' AND bill.Id IS NULL
						ORDER BY cust.FirstName,cust.LastName DESC";
			$list_billing_customer = $this->db->query($query)->result();
			foreach ($list_billing_customer as $row) {
				$billing = array('CodeValue' => $this->base->generate_code_table('TrxBilling'),
							'CustomerId' => $row->CustId,
							'UnitId' => $row->UnitId,
							'ModuleId' => $row->ModuleId,
							'Period' => $param['period'],
							'DueDate' => $param['duedate'],
							'PamCubic' => 0,
							'Amount' => $row->TariffAmount,
							'Tax' => $row->Tax,
							'TotalAmount' => $row->TariffAmount,
							'PaymentId' => NULL,
							'CreatedBy' => $param['user'],
							'CreatedOn' => date('Y-m-d H:i:s'),
							'IsSettle' => 0,
							'SettledBy' => NULL,
							'SettlementDateTime' => NULL,
							'RecordStatus' => 1
							);
				// var_dump($billing);die();
				$this->db->insert('TrxBilling', $billing);
			}
		}

		public function get_screening_pam_by_id($id) {
			$query = "SELECT bill.Id, cust.Id AS CustomerId, CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
					bill.UnitId, unit.NameValue AS Unit, bill.Period, bill.PamCubic, bill.Notes, clus.Id AS ClusterId,
					IFNULL(bill.PamLastMonth,
					(SELECT PamThisMonth FROM TrxBilling WHERE CustomerId = bill.CustomerId AND UnitId = bill.UnitId AND RecordStatus = 1
					AND ModuleId = bill.ModuleId
					AND SUBSTRING(Period, 1, 7) = SUBSTRING(DATE_SUB(bill.Period, INTERVAL 1 MONTH), 1, 7))
					) AS PamLastMonth,
					IFNULL(bill.PamThisMonth, 0) AS PamThisMonth
					FROM TrxBilling bill
					INNER JOIN MsCustomer cust ON bill.CustomerId = cust.Id
					INNER JOIN RefUnit unit ON bill.UnitId = unit.Id
					INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
					INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
					WHERE bill.RecordStatus = 1 AND bill.Id = ".$id." LIMIT 1";
			return $this->db->query($query)->row();
		}

		public function pam_get_last_cubic($custid, $unitid, $period) {
			$query = "SELECT bill.PamThisMonth FROM TrxBilling bill INNER JOIN RefModule modul ON bill.ModuleId = modul.Id
			WHERE bill.CustomerId = ".$custid." AND bill.UnitId = ".$unitid." AND bill.RecordStatus = 1
					AND SUBSTRING(bill.Period, 1, 7) = SUBSTRING(DATE_SUB('".$period."', INTERVAL 1 MONTH), 1, 7) AND modul.NameValue = 'PAM'";
			$res = $this->db->query($query)->row();
			if ($res == null)
				return 0;
			return $res->PamThisMonth;
		}

		public function update($param) {
			$customer_pam_infor = $this->db->query("SELECT unit.Id AS UnitId, pam.NameValue AS Tariff, pam.AmountPerCubic,
													pam.Tax, pam.SubsAmount AS Abonemen, modul.Id AS ModuleId
													FROM RelCustomerUnit rel
													INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
													INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
													INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
													INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
													INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
													INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
													INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
													WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
													AND cust.Id = ".$param['CustomerId']." AND unit.Id = ".$param['UnitId']." LIMIT 1")->row();
			$data = array(
						'CustomerId' => $param['CustomerId'],
						'UnitId' => $param['UnitId'],
						'Period' => $param['Period'],
						'PamLastMonth' => $param['PamLastMonth'],
						'PamThisMonth' => $param['PamThisMonth'],
						'PamCubic' => $param['PamCubic'],
						'Amount' => ($param['PamCubic'] * $customer_pam_infor->AmountPerCubic),
						'TotalAmount' => $this->base->calculate_pam_billing($param['PamCubic'], $customer_pam_infor->AmountPerCubic, $customer_pam_infor->Abonemen),
						'Notes' => $param['Notes'],
						'ModifiedBy' => $this->session->userdata('user_session')->Username,
						'ModifiedOn' => date('Y-m-d H:i:s')
					);
			$this->db->update('TrxBilling', $data, array('Id' => $param['Id']));
			return $this->db->affected_rows();
		}
		//list_tagihan_pam end


		//list_reactivate_pam start
		private $list_reactivate_pam_query = "SELECT COUNT(cust.Id) AS TotalUnsettleBill, cust.Id, MIN(bill.Period) StartPeriod, MAX(bill.Period) EndPeriod,
											CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
											unit.NameValue AS Unit,
											blk.NameValue AS Blok, clus.NameValue AS Cluster,
											pam.NameValue AS Tariff, modul.NameValue AS Module,
											pay.NameValue AS PaymentMethod,
											SUM(bill.Amount) AS Amount, SUM(bill.PAMCubic) AS PAMCubic, SUM(bill.TotalAmount) AS TotalAmount
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
											GROUP BY cust.Id, cust.FirstName, cust.LastName, unit.Id
											HAVING COUNT(cust.Id) >= ";
		private $list_reactivate_pam_query_counter = "SELECT COUNT(1) AS TotalRow FROM
													(SELECT 1 FROM RelCustomerUnit rel
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
													AND stat.NameValue = 'BERPENGHUNI' AND pam.MOduleId = bill.ModuleId
													GROUP BY cust.Id, cust.FirstName, cust.LastName, unit.Id
													HAVING COUNT(cust.Id) >= ";
		private function _get_list_reactivate_pam($threshold) {
			$column_order = $this->base->get_column_order(array('1' => 'cust.FirstName,cust.LastName',
													'2' => 'unit.NameValue',
													'7' => 'bill.Period',
													'8' => 'bill.Period'
													),15);
			$column_search = array('cust.FirstName','cust.LastName', 'unit.NameValue');
			$orderby = array('bill.Id' => 'DESC');

			$query = $this->list_reactivate_pam_query . $threshold;
			$i = 0;

			if (!empty($_POST)) {
				foreach ($column_search as $item) {
					if($_POST['search']['value']) {
						if($i===0) {
							$query .= " AND (";
							$query .= " " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						} else {
							$query .= " OR " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						}

						if(count($column_search) - 1 == $i)
							$query .= ')';
					}
					$i++;
				}
		
				if(isset($_POST['order'])) {
					$query .= " ORDER BY " . $column_order[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'];
				} else if(isset($orderby)) {
					$order = $orderby;
					$query .= " ORDER BY " . key($order) . " " . $order[key($order)];
				}
			}
			return $query;
		}

		function get_list_reactivate_pam($threshold) {
			$query =  $this->_get_list_reactivate_pam($threshold);
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_reactivate_pam($threshold) {
			$query =  $this->_get_list_reactivate_pam($threshold);
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_reactivate_pam($threshold) {
			$query = $this->list_reactivate_pam_query_counter . $threshold . ') AS MyTab LIMIT 1';
			return $this->db->query($query)->row()->TotalRow;
		}
		//list_reactivate_pam end


		//customer_only_start
		private function _get_billing_customer($user_name) {
			$column_order = $this->base->get_column_order(array('2' => 'bill.Period',
																'3' => 'unit.NameValue',
																'5' => 'clus.NameValue'),11);
			$column_search = array('bill.period', 'unit.NameValue', 'clus.NameValue');
			$orderby = array('bill.Period' => 'DESC');

			$query = "SELECT cust.Id AS CustId,
					CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
					bill.Period, unit.Id AS UnitId, unit.NameValue AS Unit,
					blk.NameValue AS Blok, clus.NameValue AS Cluster,
					bill.PAMCubic, bill.TotalAmount,
					bill.Notes,
					modul.NameValue AS Module, stat.NameValue AS UnitStatus
					FROM RelCustomerUnit rel
					INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
					INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
					INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
					INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
					INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
					INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
					INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
					INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND pam.ModuleId = bill.ModuleId
					INNER JOIN SysUser usr ON cust.UserId = usr.Id
					WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
					AND bill.RecordStatus = 1 AND bill.IsSettle = 0
					AND usr.UserName = '".$user_name."'";
			$i = 0;

			if (!empty($_POST)) {
				foreach ($column_search as $item) {
					if($_POST['search']['value']) {
						if($i===0) {
							$query .= " AND (";
							$query .= " " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						} else {
							$query .= " OR " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						}

						if(count($column_search) - 1 == $i)
							$query .= ')';
					}
					$i++;
				}
		
				if(isset($_POST['order'])) {
					$query .= " ORDER BY " . $column_order[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'];
				} else if(isset($orderby)) {
					$order = $orderby;
					$query .= " ORDER BY " . key($order) . " " . $order[key($order)];
				}
			}
			return $query;
		}


		function get_billing_customer($user_name) {
			$query =  $this->_get_billing_customer($user_name);
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		public function count_all_billing_customer($user_name) {
			$query = "SELECT COUNT(1) AS TotalRow
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
						INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND pam.ModuleId = bill.ModuleId
						INNER JOIN SysUser usr ON cust.UserId = usr.Id
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
						AND bill.RecordStatus = 1 AND bill.IsSettle = 0
						AND usr.UserName = '".$user_name."'";
			return $this->db->query($query)->row()->TotalRow;
		}

		function count_filter_billing_customer($user_name) {
			$query =  $this->_get_billing_customer($user_name);
			return $this->db->query($query)->num_rows();
		}

		public function get_customer_bill_details($user_name) {
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
					INNER JOIN SysUser usr ON cust.UserId = usr.Id
					WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
					AND bill.IsSettle = 0 AND bill.RecordStatus = 1 AND pam.ModuleId = bill.ModuleId
					AND usr.UserName = '".$user_name."'
					ORDER BY bill.Period";
			$penalty_list = $this->db->query($query)->result();
			// var_dump($penalty_list);die();
			$counter = 0;
			foreach ($penalty_list as $row) {
				$penalty_amt = $row->Today > $row->DueDate && $counter < $this->config->item('pam_max_penalty_month') ? $row->PenaltyAmount : 0.00;
				$item = array('Period' => $row->Period,
								'DueDate' => $row->DueDate,
							 	'BillAmount' => $row->TotalAmount,
								'PAMCubic' => $row->PAMCubic,
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
		//customer_only_end


		//ext_only_start
		private function _get_billing_customer_all() {
			$column_order = $this->base->get_column_order(array('2' => 'bill.Period',
																'3' => 'unit.NameValue',
																'5' => 'clus.NameValue'),11);
			$column_search = array('bill.period', 'unit.NameValue', 'clus.NameValue');
			$orderby = array('bill.Period' => 'DESC');

			$query = "SELECT cust.Id AS CustId,
					CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
					bill.Period, unit.Id AS UnitId, unit.NameValue AS Unit,
					blk.NameValue AS Blok, clus.NameValue AS Cluster,
					bill.PAMCubic, bill.TotalAmount,
					bill.Notes,
					modul.NameValue AS Module, stat.NameValue AS UnitStatus
					FROM RelCustomerUnit rel
					INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
					INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
					INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
					INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
					INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
					INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
					INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
					INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND pam.ModuleId = bill.ModuleId
					WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
					AND bill.RecordStatus = 1 AND bill.IsSettle = 0";
			$i = 0;

			if (!empty($_POST)) {
				foreach ($column_search as $item) {
					if($_POST['search']['value']) {
						if($i===0) {
							$query .= " AND (";
							$query .= " " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						} else {
							$query .= " OR " . $item . " LIKE '%". $_POST['search']['value'] ."%'";
						}

						if(count($column_search) - 1 == $i)
							$query .= ')';
					}
					$i++;
				}
		
				if(isset($_POST['order'])) {
					$query .= " ORDER BY " . $column_order[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'];
				} else if(isset($orderby)) {
					$order = $orderby;
					$query .= " ORDER BY " . key($order) . " " . $order[key($order)];
				}
			}
			return $query;
		}


		function get_billing_customer_all() {
			$query =  $this->_get_billing_customer_all();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		public function count_all_billing_customer_all() {
			$query = "SELECT COUNT(1) AS TotalRow
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN PAMPriceLists pam ON unit.PAMPriceListId = pam.Id
						INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND pam.ModuleId = bill.ModuleId
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
						AND bill.RecordStatus = 1 AND bill.IsSettle = 0";
			return $this->db->query($query)->row()->TotalRow;
		}

		function count_filter_billing_customer_all() {
			$query =  $this->_get_billing_customer_all();
			return $this->db->query($query)->num_rows();
		}

	}
?>