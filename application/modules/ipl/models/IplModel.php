<?php
	class IplModel extends CI_Model {
		private $sql = "SELECT CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
						CONCAT(unit.NameValue, ' - ', clus.NameValue) AS Unit,
						ipl.Id, ipl.CodeValue, ipl.NameValue, ipl.Amount, ipl.Tax,
						pen.Id AS PenaltyId, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount,
						mdl.Id AS ModuleId, mdl.NameValue AS ModuleName,
						CASE WHEN  ipl.RecordStatus = 1 THEN 'Aktif' ELSE 'Non-Aktif' END AS RecordStatus
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
						INNER JOIN RefModule mdl ON ipl.ModuleId = mdl.Id
						WHERE ipl.RecordStatus = 1";
		private $sql_counter = "SELECT COUNT(1) AS TotalRow
								FROM RelCustomerUnit rel
								INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
								INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
								INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
								INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
								INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
								INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
								INNER JOIN RefModule mdl ON ipl.ModuleId = mdl.Id
								WHERE ipl.RecordStatus = 1";

		public function __construct() {
			$this->load->model('base/Base_model','base');
			parent::__construct();
		}

		private function _get_datatables_query() {
			$column_order = $this->base->get_column_order(array('1' => 'cust.FirstName',
																'2' => 'unit.NameValue',
																'3' => 'ipl.CodeValue',
																'4' => 'ipl.NameValue',
																'5' => 'ipl.Amount',
																'6' => 'ipl.Tax',
																'7' => 'pen.NameValue',
																'8' => 'pen.Amount'),11);
			$column_search = array('cust.FirstName','cust.LastName','unit.NameValue','clus.NameValue','ipl.NameValue');
			$orderby = array('cust.FirstName,cust.LastName' => 'ASC');

			$query = $this->sql;
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

		function get_datatables() {
			$query =  $this->_get_datatables_query();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered() {
			$query =  $this->_get_datatables_query();
			return $this->db->query($query)->num_rows();
		}

		public function count_all() {
			return $this->db->query($this->sql_counter)->row()->TotalRow;
		}

		public function get_by_id($id) {
			return $this->db->query($this->sql . " WHERE Id = " . $id)->row();
		}

		public function save($tablename, $data) {
			$this->db->insert($tablename, $data);
			return $this->db->insert_id();
		}

		public function update($tablename, $data, $where) {
			$this->db->update($tablename, $data, $where);
			return $this->db->affected_rows();
		}

		//list_tagihan_ipl start
		private $list_tagihan_ipl_query = "SELECT bill.Id, cust.Id AS CustId, payment.Id AS PaymentId,
											CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
											unit.Id AS UnitId, unit.NameValue AS Unit,
											blk.NameValue AS Blok, clus.NameValue AS Cluster,
											ipl.NameValue AS Tariff, modul.NameValue AS Module,
											stat.NameValue AS UnitStatus,
											bill.CodeValue, bill.Period, bill.DueDate, pay.NameValue AS PaymentMethod,
											bill.Amount, bill.Tax, bill.TotalAmount, bill.IsSettle,

											CASE WHEN bill.Id IS NOT NULL THEN
											IFNULL((SELECT SUM(Amount) FROM TrxPenaltyDetails WHERE BillingId = bill.Id GROUP BY BillingId LIMIT 1),0.00)
											ELSE 0.00 END AS TotalPenaltyAmount

											FROM RelCustomerUnit rel
											INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
											INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
											INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
											INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
											INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
											INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
											INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
											INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
											LEFT JOIN TrxPayment payment ON bill.PaymentId = payment.Id
											LEFT JOIN RefPaymentMethod pay ON payment.PaymentMethodId = pay.Id
											WHERE rel.RecordStatus = 1 AND (bill.RecordStatus IS NULL OR bill.RecordStatus = 1)
											AND stat.NameValue = 'BERPENGHUNI' AND ipl.ModuleId = bill.ModuleId";
		private $list_tagihan_ipl_query_counter = "SELECT COUNT(1) AS TotalRow FROM RelCustomerUnit rel
													INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
													INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
													INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
													INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
													INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
													INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
													INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
													INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId
													LEFT JOIN TrxPayment payment ON bill.PaymentId = payment.Id
													LEFT JOIN RefPaymentMethod pay ON payment.PaymentMethodId = pay.Id
													WHERE rel.RecordStatus = 1 AND (bill.RecordStatus IS NULL OR bill.RecordStatus = 1)
													AND stat.NameValue = 'BERPENGHUNI' AND ipl.ModuleId = bill.ModuleId LIMIT 1";
		private function _get_list_tagihan_ipl() {
			$column_order = $this->base->get_column_order(array('1' => 'cust.FirstName,cust.LastName',
													'2' => 'unit.NameValue',
													'9' => 'bill.Period'),20);
			$column_search = array('cust.FirstName','cust.LastName', 'unit.NameValue','bill.Period');
			$orderby = array('bill.Id' => 'DESC');

			$query = $this->list_tagihan_ipl_query;
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

		function get_list_tagihan_ipl() {
			$query =  $this->_get_list_tagihan_ipl();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_tagihan_ipl() {
			$query =  $this->_get_list_tagihan_ipl();
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_tagihan_ipl() {
			return $this->db->query($this->list_tagihan_ipl_query_counter)->row()->TotalRow;
		}

		public function get_by_id_list_tagihan_ipl($id) {
			return $this->db->query($this->list_tagihan_ipl_query . " AND bill.Id = " . $id)->row();
		}

		public function save_list_tagihan_ipl($tablename, $data) {
			$this->db->insert($tablename, $data);
			return $this->db->insert_id();
		}

		public function update_list_tagihan_ipl($data) {
			$billing = $this->base->get_total_billing($data['CustomerId'], $data['UnitId']);
			// var_dump($billing);die();

			//1. insert_payment
			$this->db->insert('TrxPayment',
								array('CodeValue' => $this->base->generate_code_table('TrxPayment'),
								'BillAmount' => $billing['TotalBilling'],
								'Discount' => $data['Discount'],
								'PaymentMethodId' => $data['PaymentMethodId'],
								'TotalAmount' => ($billing['TotalBilling'] * (100 - $data['Discount']) / 100),
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

		public function get_ddl_active_customer() {
			$query = "SELECT DISTINCT cust.Id AS 'value', CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS 'label'
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
						ORDER BY cust.FirstName, cust.LastName";
			return $this->db->query($query)->result();
		}

		public function get_ddl_unit($clusterid) {
			$query = "SELECT DISTINCT unit.Id AS 'value',
						unit.NameValue AS 'label', cust.Id AS CustomerId,
						CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
                        TRUNCATE((ipl.Amount * (100 + ipl.Tax) / 100), 2) AS BillPerMonth
						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
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
						ipl.NameValue AS Tariff,
						ipl.Amount AS TariffAmount,
						ipl.Tax AS Tax,
						TRUNCATE((ipl.Amount * (100 + ipl.Tax) / 100), 2) AS BillPerMonth,
						modul.Id AS ModuleId, modul.NameValue AS Module,
						stat.NameValue AS UnitStatus

						FROM RelCustomerUnit rel
						INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
						INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
						INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
						INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						LEFT JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND bill.Period = '".$param['period']."' AND ipl.ModuleId = bill.ModuleId
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
							'Amount' => $row->TariffAmount,
							'Tax' => $row->Tax,
							'TotalAmount' => $row->BillPerMonth,
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
			return $this->db->insert_id();
		}

		public function multiple_ipl_bill($param) {
			//create_bill_for_previous_month:
			$billing = $this->base->get_total_multiple_billing($param['CustomerId'], $param['UnitId']);
			// var_dump($billing);die();

			//1. insert_payment
			$combine_bill_amount;
			//free_one_month
			if ($param['totalMonth'] == 12) {
				$combine_bill_amount = (($billing['MultiBillDetails'][0]['TotalAmount'] * ($param['totalMonth'] - 1)) + $billing['TotalBilling']);
			} else {
				$combine_bill_amount = (($billing['MultiBillDetails'][0]['TotalAmount'] * $param['totalMonth']) + $billing['TotalBilling']);
			}
			$this->db->insert('TrxPayment',
								array('CodeValue' => $this->base->generate_code_table('TrxPayment'),
								'BillAmount' => $combine_bill_amount,
								'Discount' => $param['Discount'],
								'PaymentMethodId' => $param['PaymentMethodId'],
								'TotalAmount' => ($combine_bill_amount * (100 - $param['Discount']) / 100),
								'Paid' => $this->base->removeAllComma($param['Paid']),
								'ChangeAmount' => $this->base->removeAllComma($param['ChangeDue']),
								'Notes' => $param['Notes'],
								'CreatedBy' => $this->session->userdata('user_session')->Username,
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
								'Notes' => 'multiple_bill_current_period',
								'SettledBy' => $this->session->userdata('user_session')->Username,
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

			//create_bill_for_based_how_many_chosen_next_month:
			$date = date_create(date('Y-m-d'));
			for ($i = 0; $i < $param['totalMonth']; $i++) {
				date_add($date, date_interval_create_from_date_string('1 months'));

				$bill_obj = array('CodeValue' => $this->base->generate_code_table('TrxBilling'),
							'CustomerId' => $billing['CustomerId'],
							'UnitId' => $billing['UnitId'],
							'ModuleId' => $billing['MultiBillDetails'][0]['ModuleId'],
							'Period' => date_format($date, 'Y-m-') . $this->config->item('bill_open_day'),
							'DueDate' => date_format($date, 'Y-m-') . $this->config->item('bill_due_day'),
							'Amount' => $billing['MultiBillDetails'][0]['Amount'],
							'Tax' => $billing['MultiBillDetails'][0]['Tax'],
							'TotalAmount' => $billing['MultiBillDetails'][0]['TotalAmount'],
							'PaymentId' => $paymentid,
							'Notes' => 'multiple_bill_next_period',
							'CreatedBy' => $this->session->userdata('user_session')->Username,
							'CreatedOn' => date('Y-m-d H:i:s'),
							'IsSettle' => 1,
							'SettledBy' => $this->session->userdata('user_session')->Username,
							'SettlementDateTime' => date('Y-m-d H:i:s'),
							'RecordStatus' => 1
							);
				// var_dump($billing);die();
				//if_total_month_selected_is_one_year (12 month) -> give_free_1_month
				if ($i == 11) {
					$bill_obj['Amount'] = 0.00;
					$bill_obj['Tax'] = 0.00;
					$bill_obj['TotalAmount'] = 0.00;
					$bill_obj['Notes'] = 'multiple_bill_next_period (free 1 month)';
				}
				$this->db->insert('TrxBilling', $bill_obj);
			}
			return $this->db->affected_rows();
		}
		//list_tagihan_ipl end


		//customer_only_start
		private function _get_billing_customer($user_name) {
			$column_order = $this->base->get_column_order(array('2' => 'bill.Period',
																'3' => 'unit.NameValue',
																'5' => 'clus.NameValue'),9);
			$column_search = array('bill.period', 'unit.NameValue', 'clus.NameValue');
			$orderby = array('bill.Period' => 'DESC');

			$query = "SELECT cust.Id AS CustId,
							CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
							bill.Period, unit.Id AS UnitId, unit.NameValue AS Unit,
							blk.NameValue AS Blok, clus.NameValue AS Cluster,
							TRUNCATE((ipl.Amount * (100 + ipl.Tax) / 100), 2) AS BillPerMonth,
							modul.NameValue AS Module, stat.NameValue AS UnitStatus
							FROM RelCustomerUnit rel
							INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
							INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
							INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
							INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
							INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
							INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
							INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
							INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND ipl.ModuleId = bill.ModuleId
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
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND ipl.ModuleId = bill.ModuleId
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
					INNER JOIN SysUser usr ON cust.UserId = usr.Id
					WHERE rel.RecordStatus = 1 AND stat.NameValue = 'BERPENGHUNI'
					AND bill.IsSettle = 0 AND bill.RecordStatus = 1 AND ipl.ModuleId = bill.ModuleId
					AND usr.UserName = '".$user_name."'
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
		//customer_only_end


		//ext_only_start
		private function _get_billing_customer_all() {
			$column_order = $this->base->get_column_order(array('2' => 'bill.Period',
																'3' => 'unit.NameValue',
																'5' => 'clus.NameValue'),9);
			$column_search = array('bill.period', 'unit.NameValue', 'clus.NameValue');
			$orderby = array('bill.Period' => 'DESC');

			$query = "SELECT cust.Id AS CustId,
							CONCAT(cust.FirstName, (CASE WHEN cust.LastName IS NULL THEN '' ELSE CONCAT(' ', cust.LastName) END)) AS CustomerName,
							bill.Period, unit.Id AS UnitId, unit.NameValue AS Unit,
							blk.NameValue AS Blok, clus.NameValue AS Cluster,
							TRUNCATE((ipl.Amount * (100 + ipl.Tax) / 100), 2) AS BillPerMonth,
							modul.NameValue AS Module, stat.NameValue AS UnitStatus
							FROM RelCustomerUnit rel
							INNER JOIN MsCustomer cust ON rel.CustomerId = cust.Id
							INNER JOIN RefUnit unit ON rel.UnitId = unit.Id
							INNER JOIN RefBlock blk ON unit.BlockId = blk.Id
							INNER JOIN RefCluster clus ON blk.ClusterId = clus.Id
							INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
							INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
							INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
							INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND ipl.ModuleId = bill.ModuleId
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
						INNER JOIN IPLPriceLists ipl ON unit.IPLPriceListId = ipl.Id
						INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
						INNER JOIN RefUnitStatus stat ON rel.UnitStatusId = stat.Id
						INNER JOIN TrxBilling bill ON cust.Id = bill.CustomerId AND unit.Id = bill.UnitId AND ipl.ModuleId = bill.ModuleId
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