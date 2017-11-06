<?php 
	class RefModel extends CI_Model {

		public function __construct() {
			$this->load->model('base/Base_model','base');
			parent::__construct();
		}

		// region: user - start
		private function _get_query_list_user() {
			$column_order = $this->base->get_column_order(array('1' => 'usr.UserName',
																'2' => 'role.NameValue'),5);
			$column_search = array('usr.UserName','role.NameValue');
			$orderby = array('role.NameValue' => 'ASC');

			$query = "SELECT usr.Id, usr.UserName, role.NameValue AS RoleName, usr.Password
						FROM SysUser usr
						INNER JOIN SysRole role ON usr.RoleId = role.Id
						WHERE usr.RecordStatus = 1";
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

		function get_list_user() {
			$query =  $this->_get_query_list_user();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_user() {
			$query =  $this->_get_query_list_user();
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_user() {
			$query = "SELECT COUNT(1) AS TotalRow
						FROM SysUser usr
						INNER JOIN SysRole role ON usr.RoleId = role.Id
						WHERE usr.RecordStatus = 1 ORDER BY role.NameValue";
			return $this->db->query($query)->row()->TotalRow;
		}

		public function get_user_by_id($id) {
			$query = "SELECT usr.Id, usr.UserName, role.Id AS RoleId, role.NameValue AS RoleName, usr.Password
						FROM SysUser usr
						INNER JOIN SysRole role ON usr.RoleId = role.Id
						WHERE usr.RecordStatus = 1 AND usr.Id = " . $id;
			return $this->db->query($query)->row();
		}

		public function add_user($param) {
			$this->db->insert('SysUser',
							array('UserName' => $param['UserName'],
								'RoleId' => (int)$param['RoleId'],
								'Password' => $param['Password'],
								'CreatedBy' => $this->session->userdata('user_session')->Username,
								'CreatedOn' => date('Y-m-d H:i:s'),
								'RecordStatus' => 1)
							);
			return $this->db->insert_id();
		}

		public function edit_user($param) {
			$this->db->update('SysUser',
							array('UserName' => $param['UserName'],
								'RoleId' => (int)$param['RoleId'],
								'Password' => $param['Password'],
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $param['Id'])
							);
			return $this->db->affected_rows();
		}

		public function delete_user($id) {
			$this->db->update('SysUser',
							array('RecordStatus' => 0,
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $id)
							);
			return $this->db->affected_rows();
		}

		public function is_valid_username($user_name, $id) {
			$res = $this->db->query("SELECT Id FROM SysUser WHERE UserName = '".$user_name."' AND Id <> ".$id." LIMIT 1")->row();
			if ($res == null)
				return true;
			return false;
		}
		// region: user - end


		// region: tariff_ipl - start
		private function _get_query_list_tariff_ipl() {
			$column_order = $this->base->get_column_order(array('1' => 'ipl.CodeValue',
																'2' => 'ipl.NameValue',
																'3' => 'ipl.Amount',
																'4' => 'ipl.Tax',
																'5' => 'pen.NameValue'),9);
			$column_search = array('ipl.CodeValue','ipl.NameValue','pen.NameValue');
			$orderby = array('ipl.NameValue' => 'ASC');

			$query = "SELECT ipl.Id, ipl.CodeValue, ipl.NameValue, ipl.Amount,
					ipl.Tax, pen.Id AS PenaltyId, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, modul.NameValue AS ModuleName
					FROM IPLPriceLists ipl
					INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
					INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
					WHERE ipl.RecordStatus = 1 AND modul.NameValue = 'IPL'";
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

		function get_list_tariff_ipl() {
			$query =  $this->_get_query_list_tariff_ipl();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_tariff_ipl() {
			$query =  $this->_get_query_list_tariff_ipl();
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_tariff_ipl() {
			$query = "SELECT COUNT(1) AS TotalRow
					FROM IPLPriceLists ipl
					INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
					INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
					WHERE ipl.RecordStatus = 1 AND modul.NameValue = 'IPL'";
			return $this->db->query($query)->row()->TotalRow;
		}

		public function get_tariff_ipl_by_id($id) {
			$query = "SELECT ipl.Id, ipl.CodeValue, ipl.NameValue, ipl.Amount,
					ipl.Tax, pen.Id AS PenaltyId, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, modul.NameValue AS ModuleName
					FROM IPLPriceLists ipl
					INNER JOIN RefPenalty pen ON ipl.PenaltyId = pen.Id
					INNER JOIN RefModule modul ON ipl.ModuleId = modul.Id
					WHERE ipl.RecordStatus = 1 AND modul.NameValue = 'IPL' AND ipl.Id = " . $id;
			return $this->db->query($query)->row();
		}

		public function add_tariff_ipl($param) {
			$module_id = $this->db->query("SELECT Id FROM RefModule WHERE NameValue = 'IPL' LIMIT 1")->row()->Id;
			$this->db->insert('IPLPriceLists',
							array('CodeValue' => $param['CodeValue'],
								'PenaltyId' => (int)$param['PenaltyId'],
								'NameValue' => $param['NameValue'],
								'Amount' => str_replace(',', '', $param['Amount']),
								'Tax' => $param['Tax'],
								'ModuleId' => $module_id,
								'CreatedBy' => $this->session->userdata('user_session')->Username,
								'CreatedOn' => date('Y-m-d H:i:s'),
								'RecordStatus' => 1)
							);
			return $this->db->insert_id();
		}

		public function edit_tariff_ipl($param) {
			$this->db->update('IPLPriceLists',
							array('CodeValue' => $param['CodeValue'],
								'PenaltyId' => (int)$param['PenaltyId'],
								'NameValue' => $param['NameValue'],
								'Amount' => str_replace(',', '', $param['Amount']),
								'Tax' => $param['Tax'],
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $param['Id'])
							);
			return $this->db->affected_rows();
		}

		public function delete_tariff_ipl($id) {
			$this->db->update('IPLPriceLists',
							array('RecordStatus' => 0,
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $id)
							);
			return $this->db->affected_rows();
		}

		public function is_valid_tariffcode($code, $id) {
			$res = $this->db->query("SELECT Id FROM IPLPriceLists WHERE CodeValue = '".$code."' AND Id <> ".$id." LIMIT 1")->row();
			if ($res == null)
				return true;
			return false;
		}
		// region: tariff_ipl - end


		// region: tariff_pam - start
		private function _get_query_list_tariff_pam() {
			$column_order = $this->base->get_column_order(array('1' => 'pam.CodeValue',
																'2' => 'pam.NameValue',
																'3' => 'pam.AmountPerCubic',
																'4' => 'pam.SubsAmount',
																'5' => 'pen.NameValue'),9);
			$column_search = array('pam.CodeValue','pam.NameValue','pen.NameValue');
			$orderby = array('pam.NameValue' => 'ASC');

			$query = "SELECT pam.Id, pam.CodeValue, pam.NameValue, pam.AmountPerCubic, pam.SubsAmount AS Abonemen,
					pen.Id AS PenaltyId, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, modul.NameValue AS ModuleName
					FROM PAMPriceLists pam
					INNER JOIN RefPenalty pen ON pam.PenaltyId = pen.Id
					INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
					WHERE pam.RecordStatus = 1 AND modul.NameValue = 'PAM'";
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

		function get_list_tariff_pam() {
			$query =  $this->_get_query_list_tariff_pam();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_tariff_pam() {
			$query =  $this->_get_query_list_tariff_pam();
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_tariff_pam() {
			$query = "SELECT COUNT(1) AS TotalRow
					FROM PAMPriceLists pam
					INNER JOIN RefPenalty pen ON pam.PenaltyId = pen.Id
					INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
					WHERE pam.RecordStatus = 1 AND modul.NameValue = 'PAM'";
			return $this->db->query($query)->row()->TotalRow;
		}

		public function get_tariff_pam_by_id($id) {
			$query = "SELECT pam.Id, pam.CodeValue, pam.NameValue, pam.AmountPerCubic, pam.SubsAmount AS Abonemen,
					pen.Id AS PenaltyId, pen.NameValue AS PenaltyName, pen.Amount AS PenaltyAmount, modul.NameValue AS ModuleName
					FROM PAMPriceLists pam
					INNER JOIN RefPenalty pen ON pam.PenaltyId = pen.Id
					INNER JOIN RefModule modul ON pam.ModuleId = modul.Id
					WHERE pam.RecordStatus = 1 AND modul.NameValue = 'PAM' AND pam.Id = " . $id;
			return $this->db->query($query)->row();
		}

		public function add_tariff_pam($param) {
			$module_id = $this->db->query("SELECT Id FROM RefModule WHERE NameValue = 'PAM' LIMIT 1")->row()->Id;
			$this->db->insert('PAMPriceLists',
							array('CodeValue' => $param['CodeValue'],
								'PenaltyId' => (int)$param['PenaltyId'],
								'NameValue' => $param['NameValue'],
								'AmountPerCubic' => str_replace(',', '', $param['AmountPerCubic']),
								'SubsAmount' => str_replace(',', '', $param['SubsAmount']),
								'Tax' => 0.00,
								'ModuleId' => $module_id,
								'CreatedBy' => $this->session->userdata('user_session')->Username,
								'CreatedOn' => date('Y-m-d H:i:s'),
								'RecordStatus' => 1)
							);
			return $this->db->insert_id();
		}

		public function edit_tariff_pam($param) {
			$this->db->update('PAMPriceLists',
							array('CodeValue' => $param['CodeValue'],
								'PenaltyId' => (int)$param['PenaltyId'],
								'NameValue' => $param['NameValue'],
								'AmountPerCubic' => str_replace(',', '', $param['AmountPerCubic']),
								'SubsAmount' => str_replace(',', '', $param['SubsAmount']),
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $param['Id'])
							);
			return $this->db->affected_rows();
		}

		public function delete_tariff_pam($id) {
			$this->db->update('PAMPriceLists',
							array('RecordStatus' => 0,
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $id)
							);
			return $this->db->affected_rows();
		}

		public function is_valid_tariffcode_pam($code, $id) {
			$res = $this->db->query("SELECT Id FROM PAMPriceLists WHERE CodeValue = '".$code."' AND Id <> ".$id." LIMIT 1")->row();
			if ($res == null)
				return true;
			return false;
		}
		// region: tariff_pam - end


		// region: tariff_denda - start
		private function _get_query_list_tariff_denda() {
			$column_order = $this->base->get_column_order(array('1' => 'CodeValue',
																'2' => 'NameValue',
																'3' => 'Amount'),5);
			$column_search = array('CodeValue','NameValue','Amount');
			$orderby = array('NameValue' => 'ASC');

			$query = "SELECT Id, CodeValue, NameValue, Amount FROM RefPenalty WHERE RecordStatus = 1";
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

		function get_list_tariff_denda() {
			$query =  $this->_get_query_list_tariff_denda();
			if(!empty($_POST) && $_POST['length'] != -1)
				$query .= " LIMIT " . $_POST['start'] . "," . $_POST['length'];
			return $this->db->query($query)->result();
		}

		function count_filtered_list_tariff_denda() {
			$query =  $this->_get_query_list_tariff_denda();
			return $this->db->query($query)->num_rows();
		}

		public function count_all_list_tariff_denda() {
			$query = "SELECT COUNT(1) AS TotalRow FROM RefPenalty WHERE RecordStatus = 1";
			return $this->db->query($query)->row()->TotalRow;
		}

		public function get_tariff_denda_by_id($id) {
			$query = "SELECT Id, CodeValue, NameValue, Amount FROM RefPenalty WHERE RecordStatus = 1 AND Id = " . $id;
			return $this->db->query($query)->row();
		}

		public function add_tariff_denda($param) {
			$this->db->insert('RefPenalty',
							array('CodeValue' => $param['CodeValue'],
								'NameValue' => $param['NameValue'],
								'Amount' => str_replace(',', '', $param['Amount']),
								'CreatedBy' => $this->session->userdata('user_session')->Username,
								'CreatedOn' => date('Y-m-d H:i:s'),
								'RecordStatus' => 1)
							);
			return $this->db->insert_id();
		}

		public function edit_tariff_denda($param) {
			$this->db->update('RefPenalty',
							array('CodeValue' => $param['CodeValue'],
								'NameValue' => $param['NameValue'],
								'Amount' => str_replace(',', '', $param['Amount']),
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $param['Id'])
							);
			return $this->db->affected_rows();
		}

		public function delete_tariff_denda($id) {
			$this->db->update('RefPenalty',
							array('RecordStatus' => 0,
								'ModifiedBy' => $this->session->userdata('user_session')->Username,
								'ModifiedOn' => date('Y-m-d H:i:s')),
							array('Id' => $id)
							);
			return $this->db->affected_rows();
		}

		public function is_valid_tariffcode_denda($code, $id) {
			$res = $this->db->query("SELECT Id FROM RefPenalty WHERE CodeValue = '".$code."' AND Id <> ".$id." LIMIT 1")->row();
			if ($res == null)
				return true;
			return false;
		}
		// region: tariff_denda - end
	}
?>