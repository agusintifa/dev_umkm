<?php

class Ipl extends MX_Controller {
	private $paramdata;

	function __construct() {
		$this->load->helper('form');
		$this->load->model('IplModel', 'ipl');
		$this->load->model('base/Base_model','base');
		parent::__construct();
	}

	//start list_tagihan_ipl
	public function index() {
		//auto_create_bill_on_this_month
		$this->_auto_generate_ipl_bill();
		$header['page_title'] = 'Tagihan IPL';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$data['cluster_filter'] = $this->base->create_ddl_cluster();
		$data['PaymentMethodId'] = $this->base->create_ddl_paymethod();
		$this->load->view('index', $data);
		$this->load->view('shared/footer');
	}

	public function index_extended() {
		$header['page_title'] = 'Tagihan IPL';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('index_extended');
		$this->load->view('shared/footer');
	}

	public function index_add() {
		$header['page_title'] = 'Add Tagihan IPL';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$data['PaymentMethodId'] = $this->base->create_ddl_paymethod();
		$this->load->view('add_tagihan', $data);
		$this->load->view('shared/footer');
	}

	public function get_ddl_active_customer() {
		echo json_encode($this->ipl->get_ddl_active_customer());
	}

	public function get_ddl_unit() {
		echo json_encode($this->ipl->get_ddl_unit($this->input->post('ClusterId')));
	}

	public function get_ddl_cluster() {
		echo json_encode($this->base->get_ddl_cluster());
	}

	public function list_tagihan_ipl() {
		$list = $this->ipl->get_list_tagihan_ipl();
		// var_dump($list);die();
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $item) {
			$no++;
			$row = array();
			$row[] = $no;
			$row[] = $item->CustomerName;
			$row[] = $item->Unit;
			$row[] = $item->Blok;
			$row[] = $item->Cluster;
			$row[] = $item->Tariff;
			$row[] = $item->Module;
			$row[] = $item->UnitStatus;
			$row[] = $item->CodeValue;
			$row[] = $item->Period;
			$row[] = $item->DueDate;
			$row[] = $item->PaymentMethod;
			$row[] = number_format($item->Amount);
			$row[] = $item->Tax;
			$row[] = NULL;
			$row[] = number_format($item->TotalAmount);
			$row[] = NULL;
			$row[] = $item->IsSettle;
			$row[] = number_format($item->TotalPenaltyAmount);
			if($item->IsSettle == 0) {
				if ($this->session->userdata('user_session')->Role == 'CASHIER') {
					$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Proses" onclick="edit_record('."'".$item->Id."'".', '."'".$item->CustId."'".', '."'".$item->UnitId."'".')"><i></i> Proses</a>';
				} else {
					$row[] = '';
				}
			} else {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" onclick="print_invoice('."'".$item->PaymentId."'".')" title="Print"><i></i> Print</a>';
			}
			/*$row[] = $item->Id;
			$row[] = $item->CustId;
			$row[] = $item->UnitId;*/
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->ipl->count_all_list_tagihan_ipl(),
						"recordsFiltered" => $this->ipl->count_filtered_list_tagihan_ipl(),
						"data" => $data,
				);
		// var_dump($output);die();
		echo json_encode($output);
	}

	public function list_tagihan_ipl_edit() {
			$data = $this->ipl->get_by_id_list_tagihan_ipl($this->input->post('Id'));
			echo json_encode($data);
	}

	public function list_tagihan_ipl_update() {
			$this->_validate_tagihan_ipl();
			$paramdata = $this->input->post();
			$data = array(
					'Discount' => $paramdata['Discount'],
					'Paid' => str_replace(',', '', $paramdata['Paid']),
					'ChangeDue' => str_replace(',', '', $paramdata['ChangeDue']),
					'Notes' => $paramdata['Notes'],
					'CustomerId' => $paramdata['CustomerId'],
					'PaymentMethodId' => $paramdata['PaymentMethodId'],
					'UnitId' => $paramdata['UnitId'],
					'Username'=> $this->session->userdata('user_session')->Username
				);
			$this->ipl->update_list_tagihan_ipl($data);
			echo json_encode(array("status" => TRUE));
	}

	private function _validate_tagihan_ipl() {
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$param = $this->input->post();

			if($param['PaymentMethodId'] == '') {
				$data['inputerror'][] = 'PaymentMethodId';
				$data['error_string'][] = 'Metode Pembayaran is required';
				$data['status'] = FALSE;
			}

			if($param['Discount'] == '') {
				$data['inputerror'][] = 'Discount';
				$data['error_string'][] = 'Diskon is required';
				$data['status'] = FALSE;
			}

			if($param['Paid'] == '') {
				$data['inputerror'][] = 'Paid';
				$data['error_string'][] = 'Total Bayar is required';
				$data['status'] = FALSE;
			}

			if($param['ChangeDue'] == '') {
				$data['inputerror'][] = 'ChangeDue';
				$data['error_string'][] = 'Total Kembali is required';
				$data['status'] = FALSE;
			}

			if($this->base->toNum($param['ChangeDue']) < 0) {
				$data['inputerror'][] = 'Paid';
				$data['error_string'][] = 'Total Bayar harus sama atau lebih besar dari Total Tagihan';
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
	}

	public function generate_billing_customer() {
		$param = array('period' => $this->input->post('period') . '-' . $this->config->item('bill_open_day'),
						'duedate' => $this->input->post('period') . '-' . $this->config->item('bill_due_day'),
						'user' => $this->session->userdata('user_session')->Username
					);
		$id = $this->ipl->generate_billing_customer($param);
		echo json_encode(array("status" => TRUE, "insertid" => $id));
	}

	private function _auto_generate_ipl_bill() {
		$param = array('period' => date('Y-m') . '-' . $this->config->item('bill_open_day'),
						'duedate' => date('Y-m') . '-' . $this->config->item('bill_due_day'),
						'user' => $this->session->userdata('user_session')->Username
					);
		$this->ipl->generate_billing_customer($param);
	}

	public function get_total_billing() {
		$has_unsettle_pam = $this->base->is_customer_has_unsettle_pam($this->input->post('custid'), $this->input->post('unitid'));
		if ($has_unsettle_pam['stat']) {
			echo json_encode($has_unsettle_pam);
		} else {
			$res = $this->base->get_total_billing($this->input->post('custid'), $this->input->post('unitid'));
			// var_dump($res);die();
			echo json_encode($res);
		}
	}

	//end list_tagihan_ipl

	public function index_price() {
		$header['page_title'] = 'Daftar Pelanggan';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('index_price');
		$this->load->view('shared/footer');
	}

	public function ajax_list() {
			$list = $this->ipl->get_datatables();
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->CustomerName;
				$row[] = $item->Unit;
				$row[] = $item->CodeValue;
				$row[] = $item->NameValue;
				$row[] = number_format($item->Amount);
				$row[] = $item->Tax;
				$row[] = $item->PenaltyName;
				$row[] = number_format($item->PenaltyAmount);
				$row[] = $item->ModuleName;
				$row[] = $item->RecordStatus;
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ipl->count_all(),
							"recordsFiltered" => $this->ipl->count_filtered(),
							"data" => $data,
					);
			// var_dump($output);die();
			//output to json format
			echo json_encode($output);
		}

		public function ajax_edit() {
			$data = $this->ipl->get_by_id($this->input->post('Id'));
			echo json_encode($data);
		}

		public function ajax_add() {
			$this->_validate();
			$paramdata = $this->input->post();
			// var_dump($paramdata);die();
			$insert = $this->ipl->multiple_ipl_bill($paramdata);
			echo json_encode(array("status" => TRUE));
		}

		public function ajax_update() {
			$this->_validate();
			$paramdata = $this->input->post();
			$data = array(
					'NameValue' => $paramdata['NameValue'],
					'ModifiedBy'=> $this->session->userdata('user_session')->Username,
					'ModifiedOn'=> date('Y-m-d H:i:s'),
				);
			$this->ipl->update($paramdata['TableName'], $data, array('Id' => $paramdata['Id']));
			echo json_encode(array("status" => TRUE));
		}

		public function ajax_delete() {
			$paramdata = $this->input->post();
			$data = array(
					'RecordStatus' => 0,
					'ModifiedBy'=> $this->session->userdata('user_session')->Username,
					'ModifiedOn'=> date('Y-m-d H:i:s'),
				);
			$this->ipl->update($paramdata['TableName'], $data, array('Id' => $paramdata['Id']));
			echo json_encode(array("status" => TRUE));
		}


		private function _validate() {
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$param = $this->input->post();
			
			if($param['Unit'] == '') {
				$data['inputerror'][] = 'Unit';
				$data['error_string'][] = 'Unit is required';
				$data['status'] = FALSE;
			}

			if($param['CustomerName'] == '') {
				$data['inputerror'][] = 'CustomerName';
				$data['error_string'][] = 'Customer Name is required';
				$data['status'] = FALSE;
			}

			if($param['Tariff'] == '') {
				$data['inputerror'][] = 'Tariff';
				$data['error_string'][] = 'Tariff is required';
				$data['status'] = FALSE;
			}

			if($param['StartPeriod'] == '') {
				$data['inputerror'][] = 'StartPeriod';
				$data['error_string'][] = 'Period is required';
				$data['status'] = FALSE;
			}

			if($param['PaymentMethodId'] == '') {
				$data['inputerror'][] = 'PaymentMethodId';
				$data['error_string'][] = 'Metode Pembayaran is required';
				$data['status'] = FALSE;
			}

			if($param['Discount'] == '') {
				$data['inputerror'][] = 'Discount';
				$data['error_string'][] = 'Diskon is required';
				$data['status'] = FALSE;
			}

			if($param['Paid'] == '') {
				$data['inputerror'][] = 'Paid';
				$data['error_string'][] = 'Total Bayar is required';
				$data['status'] = FALSE;
			}

			if($this->base->toNum($param['ChangeDue']) < 0) {
				$data['inputerror'][] = 'Paid';
				$data['error_string'][] = 'Total Bayar harus sama atau lebih besar dari Total Tagihan';
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}

		//for_user_customer
		public function customer_ipl_bill() {
			$header['page_title'] = 'Tagihan IPL';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('shared/header', $header);
			$this->load->view('list_customer_ipl_bill');
			$this->load->view('shared/footer');
		}

		public function customer_ipl_list() {
			/*list: get_list_tagihan_ipl
			recordsTotal: count_all_list_tagihan_ipl
			recordsFiltered: count_filtered_list_tagihan_ipl*/

			$user_name = $this->session->userdata('user_session')->Username;
			$list = $this->ipl->get_billing_customer($user_name);
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->CustomerName;
				$row[] = $item->Period;
				$row[] = $item->Unit;
				$row[] = $item->Blok;
				$row[] = $item->Cluster;
				$row[] = number_format($item->BillPerMonth);
				$row[] = $item->Module;
				$row[] = $item->UnitStatus;
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ipl->count_all_billing_customer($user_name),
							"recordsFiltered" => $this->ipl->count_filter_billing_customer($user_name),
							"data" => $data,
					);
			// var_dump($output);die();
			echo json_encode($output);
		}

		public function get_customer_bill_details() {
			$res = $this->ipl->get_customer_bill_details($this->session->userdata('user_session')->Username);
			echo json_encode($res);
		}

		//for_ext_dept
		public function customer_ipl_bill_all() {
			$header['page_title'] = 'Tagihan IPL';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('shared/header', $header);
			$this->load->view('list_customer_ipl_bill_all');
			$this->load->view('shared/footer');
		}

		public function customer_ipl_list_all() {
			/*list: get_list_tagihan_ipl
			recordsTotal: count_all_list_tagihan_ipl
			recordsFiltered: count_filtered_list_tagihan_ipl*/

			$list = $this->ipl->get_billing_customer_all();
			// var_dump($list);die();
			$data = array();
			$no = $_POST['start'];
			foreach ($list as $item) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $item->CustomerName;
				$row[] = $item->Period;
				$row[] = $item->Unit;
				$row[] = $item->Blok;
				$row[] = $item->Cluster;
				$row[] = number_format($item->BillPerMonth);
				$row[] = $item->Module;
				$row[] = $item->UnitStatus;
				$data[] = $row;
			}

			$output = array(
							"draw" => $_POST['draw'],
							"recordsTotal" => $this->ipl->count_all_billing_customer_all(),
							"recordsFiltered" => $this->ipl->count_filter_billing_customer_all(),
							"data" => $data,
					);
			// var_dump($output);die();
			echo json_encode($output);
		}
}

?>