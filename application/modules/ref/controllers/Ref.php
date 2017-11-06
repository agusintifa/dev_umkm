<?php
	class Ref extends MX_Controller {
		private $paramdata;

		function __construct() {
			$this->load->helper('form');
			$this->load->model('RefModel','ref');
			$this->load->model('base/Base_model','base');
			parent::__construct();
		}

		// region: user - start
		public function index_user() {
			$header['page_title'] = 'User';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('shared/header', $header);
			$data['RoleId'] = $this->base->create_ddl_role();
			$this->load->view('index_user', $data);
			$this->load->view('shared/footer');
		}

		public function list_user() {
			$list = $this->ref->get_list_user();
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->UserName;
				$row[] = $item->RoleName;
				$row[] = $item->Password;
				$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_record('."'".$item->Id."'".')"><i></i> Edit</a> <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_record('."'".$item->Id."'".', '."'".$item->UserName."'".')"><i></i> Delete</a>';
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ref->count_all_list_user(),
							"recordsFiltered" => $this->ref->count_filtered_list_user(),
							"data" => $data,
					);
			// var_dump($output);die();
			echo json_encode($output);
		}

		public function get_user_by_id() {
			$item = $this->ref->get_user_by_id($this->input->post('Id'));
			// var_dump($item);die();
			echo json_encode($item);
		}

		public function add_user() {
			$paramdata = $this->input->post();
			$this->_validate_user($paramdata['UserName'], 0);
			// var_dump($paramdata);die();
			$res = $this->ref->add_user($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function edit_user() {
			$paramdata = $this->input->post();
			$this->_validate_user($paramdata['UserName'], $paramdata['Id']);
			// var_dump($paramdata);die();
			$res = $this->ref->edit_user($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function delete_user() {
			$res = $this->ref->delete_user($this->input->post('Id'));
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		private function _validate_user($user_name, $id) {
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$param = $this->input->post();

			if($param['UserName'] == '') {
				$data['inputerror'][] = 'UserName';
				$data['error_string'][] = 'User Name is required';
				$data['status'] = FALSE;
			} else {
				$data['inputerror'][] = 'UserName';
				$data['error_string'][] = 'User ' . $param['UserName'] . ' sudah digunakan';
				$data['status'] = $this->ref->is_valid_username($user_name, $id);
			}

			if($param['RoleId'] == '') {
				$data['inputerror'][] = 'RoleId';
				$data['error_string'][] = 'Role is required';
				$data['status'] = FALSE;
			}

			if($param['Password'] == '') {
				$data['inputerror'][] = 'Password';
				$data['error_string'][] = 'Password is required';
				$data['status'] = FALSE;
			}

			if($param['ConfirmPassword'] == '') {
				$data['inputerror'][] = 'ConfirmPassword';
				$data['error_string'][] = 'Confirm Password is required';
				$data['status'] = FALSE;
			}

			if($param['ConfirmPassword'] != $param['Password']) {
				$data['inputerror'][] = 'ConfirmPassword';
				$data['error_string'][] = 'Confirm Password tidak sesuai dengan Password';
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
		// region: user - end


		// region: tariff_ipl - start
		public function index_tariff_ipl() {
			$header['page_title'] = 'Tariff IPL';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('shared/header', $header);
			$data['PenaltyId'] = $this->base->create_ddl_penalty();
			$this->load->view('index_tariff_ipl', $data);
			$this->load->view('shared/footer');
		}

		public function list_tariff_ipl() {
			$list = $this->ref->get_list_tariff_ipl();
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->CodeValue;
				$row[] = $item->NameValue;
				$row[] = number_format($item->Amount);
				$row[] = number_format($item->Tax);
				$row[] = $item->PenaltyName;
				$row[] = number_format($item->PenaltyAmount);
				$row[] = $item->ModuleName;
				$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_record('."'".$item->Id."'".')"><i></i> Edit</a> <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_record('."'".$item->Id."'".', '."'".$item->CodeValue."'".')"><i></i> Delete</a>';
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ref->count_all_list_tariff_ipl(),
							"recordsFiltered" => $this->ref->count_filtered_list_tariff_ipl(),
							"data" => $data,
					);
			// var_dump($output);die();
			echo json_encode($output);
		}

		public function get_tariff_ipl_by_id() {
			$item = $this->ref->get_tariff_ipl_by_id($this->input->post('Id'));
			// var_dump($item);die();
			echo json_encode($item);
		}

		public function add_tariff_ipl() {
			$paramdata = $this->input->post();
			$this->_validate_tariff_ipl($paramdata['CodeValue'], 0);
			// var_dump($paramdata);die();
			$res = $this->ref->add_tariff_ipl($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function edit_tariff_ipl() {
			$paramdata = $this->input->post();
			$this->_validate_tariff_ipl($paramdata['CodeValue'], $paramdata['Id']);
			// var_dump($paramdata);die();
			$res = $this->ref->edit_tariff_ipl($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function delete_tariff_ipl() {
			$res = $this->ref->delete_tariff_ipl($this->input->post('Id'));
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		private function _validate_tariff_ipl($code, $id) {
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$param = $this->input->post();

			if($param['CodeValue'] == '') {
				$data['inputerror'][] = 'CodeValue';
				$data['error_string'][] = 'Code is required';
				$data['status'] = FALSE;
			} else if(!$this->ref->is_valid_tariffcode($code, $id)) {
				$data['inputerror'][] = 'CodeValue';
				$data['error_string'][] = 'Code ' . $param['CodeValue'] . ' sudah digunakan';
				$data['status'] = FALSE;
			}

			if($param['NameValue'] == '') {
				$data['inputerror'][] = 'NameValue';
				$data['error_string'][] = 'Name is required';
				$data['status'] = FALSE;
			}

			if($param['Amount'] == '') {
				$data['inputerror'][] = 'Amount';
				$data['error_string'][] = 'Amount is required';
				$data['status'] = FALSE;
			}

			if($param['Amount'] == '0') {
				$data['inputerror'][] = 'Amount';
				$data['error_string'][] = 'Amount harus lebih dari 0';
				$data['status'] = FALSE;
			}

			if($param['Tax'] == '') {
				$data['inputerror'][] = 'Tax';
				$data['error_string'][] = 'Tax is required';
				$data['status'] = FALSE;
			}

			if($param['PenaltyId'] == '') {
				$data['inputerror'][] = 'PenaltyId';
				$data['error_string'][] = 'Penalty is required';
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
		// region: tariff_ipl - end


		// region: tariff_pam - start
		public function index_tariff_pam() {
			$header['page_title'] = 'Tariff PAM';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('shared/header', $header);
			$data['PenaltyId'] = $this->base->create_ddl_penalty();
			$this->load->view('index_tariff_pam', $data);
			$this->load->view('shared/footer');
		}

		public function list_tariff_pam() {
			$list = $this->ref->get_list_tariff_pam();
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->CodeValue;
				$row[] = $item->NameValue;
				$row[] = number_format($item->AmountPerCubic);
				$row[] = number_format($item->Abonemen);
				$row[] = $item->PenaltyName;
				$row[] = number_format($item->PenaltyAmount);
				$row[] = $item->ModuleName;
				$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_record('."'".$item->Id."'".')"><i></i> Edit</a> <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_record('."'".$item->Id."'".', '."'".$item->CodeValue."'".')"><i></i> Delete</a>';
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ref->count_all_list_tariff_pam(),
							"recordsFiltered" => $this->ref->count_filtered_list_tariff_pam(),
							"data" => $data,
					);
			// var_dump($output);die();
			echo json_encode($output);
		}

		public function get_tariff_pam_by_id() {
			$item = $this->ref->get_tariff_pam_by_id($this->input->post('Id'));
			// var_dump($item);die();
			echo json_encode($item);
		}

		public function add_tariff_pam() {
			$paramdata = $this->input->post();
			$this->_validate_tariff_pam($paramdata['CodeValue'], 0);
			// var_dump($paramdata);die();
			$res = $this->ref->add_tariff_pam($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function edit_tariff_pam() {
			$paramdata = $this->input->post();
			$this->_validate_tariff_pam($paramdata['CodeValue'], $paramdata['Id']);
			// var_dump($paramdata);die();
			$res = $this->ref->edit_tariff_pam($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function delete_tariff_pam() {
			$res = $this->ref->delete_tariff_pam($this->input->post('Id'));
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		private function _validate_tariff_pam($code, $id) {
			// var_dump($this->ref->is_valid_tariffcode_pam($code, $id));die();
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$param = $this->input->post();

			if($param['CodeValue'] == '') {
				$data['inputerror'][] = 'CodeValue';
				$data['error_string'][] = 'Code is required';
				$data['status'] = FALSE;
			} else if (!$this->ref->is_valid_tariffcode_pam($code, $id)) {
				$data['inputerror'][] = 'CodeValue';
				$data['error_string'][] = 'Code ' . $param['CodeValue'] . ' sudah digunakan';
				$data['status'] = FALSE;
			}

			if($param['NameValue'] == '') {
				$data['inputerror'][] = 'NameValue';
				$data['error_string'][] = 'Name is required';
				$data['status'] = FALSE;
			}

			if($param['AmountPerCubic'] == '') {
				$data['inputerror'][] = 'AmountPerCubic';
				$data['error_string'][] = 'Amount Per Cubic is required';
				$data['status'] = FALSE;
			} else if ($param['AmountPerCubic'] == '0') {
				$data['inputerror'][] = 'AmountPerCubic';
				$data['error_string'][] = 'Amount Per Cubic harus lebih dari 0';
				$data['status'] = FALSE;
			}

			if($param['SubsAmount'] == '') {
				$data['inputerror'][] = 'SubsAmount';
				$data['error_string'][] = 'Abonemen is required';
				$data['status'] = FALSE;
			}

			if($param['SubsAmount'] == '0') {
				$data['inputerror'][] = 'SubsAmount';
				$data['error_string'][] = 'Abonemen harus lebih dari 0';
				$data['status'] = FALSE;
			}

			if($param['PenaltyId'] == '') {
				$data['inputerror'][] = 'PenaltyId';
				$data['error_string'][] = 'Penalty is required';
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
		// region: tariff_pam - end


		// region: tariff_denda - start
		public function index_tariff_denda() {
			$header['page_title'] = 'Tariff Denda';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('shared/header', $header);
			$this->load->view('index_tariff_denda');
			$this->load->view('shared/footer');
		}

		public function list_tariff_denda() {
			$list = $this->ref->get_list_tariff_denda();
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->CodeValue;
				$row[] = $item->NameValue;
				$row[] = number_format($item->Amount);
				$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Edit" onclick="edit_record('."'".$item->Id."'".')"><i></i> Edit</a> <a class="btn btn-sm btn-danger" href="javascript:void(0)" title="Delete" onclick="delete_record('."'".$item->Id."'".', '."'".$item->CodeValue."'".')"><i></i> Delete</a>';
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ref->count_all_list_tariff_denda(),
							"recordsFiltered" => $this->ref->count_filtered_list_tariff_denda(),
							"data" => $data,
					);
			// var_dump($output);die();
			echo json_encode($output);
		}

		public function get_tariff_denda_by_id() {
			$item = $this->ref->get_tariff_denda_by_id($this->input->post('Id'));
			// var_dump($item);die();
			echo json_encode($item);
		}

		public function add_tariff_denda() {
			$paramdata = $this->input->post();
			$this->_validate_tariff_denda($paramdata['CodeValue'], 0);
			// var_dump($paramdata);die();
			$res = $this->ref->add_tariff_denda($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function edit_tariff_denda() {
			$paramdata = $this->input->post();
			$this->_validate_tariff_denda($paramdata['CodeValue'], $paramdata['Id']);
			// var_dump($paramdata);die();
			$res = $this->ref->edit_tariff_denda($paramdata);
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		public function delete_tariff_denda() {
			$res = $this->ref->delete_tariff_denda($this->input->post('Id'));
			if ($res) {
				echo json_encode(array("status" => TRUE));
			} else {
				echo json_encode(array("status" => FALSE));
			}
		}

		private function _validate_tariff_denda($code, $id) {
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$param = $this->input->post();

			if($param['CodeValue'] == '') {
				$data['inputerror'][] = 'CodeValue';
				$data['error_string'][] = 'Code is required';
				$data['status'] = FALSE;
			} else if (!$this->ref->is_valid_tariffcode_denda($code, $id)) {
				$data['inputerror'][] = 'CodeValue';
				$data['error_string'][] = 'Code ' . $param['CodeValue'] . ' sudah digunakan';
				$data['status'] = FALSE;
			}

			if($param['NameValue'] == '') {
				$data['inputerror'][] = 'NameValue';
				$data['error_string'][] = 'Name is required';
				$data['status'] = FALSE;
			}

			if($param['Amount'] == '') {
				$data['inputerror'][] = 'Amount';
				$data['error_string'][] = 'Amount is required';
				$data['status'] = FALSE;
			} else if ($param['Amount'] == '0') {
				$data['inputerror'][] = 'Amount';
				$data['error_string'][] = 'Amount harus lebih dari 0';
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
		// region: tariff_denda - end
	}
?>