<?php

class Pam extends MX_Controller {
	private $paramdata;

	function __construct() {
		$this->load->helper('form');
		$this->load->model('PamModel', 'pam');
		$this->load->model('base/Base_model','base');
		parent::__construct();
	}

	//start list_tagihan_pam
	public function index() {
		//auto_create_bill_on_this_month
		$this->_auto_generate_pam_bill();
		$header['page_title'] = 'Tagihan PAM';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$data['cluster_filter'] = $this->base->create_ddl_cluster();
		$data['PaymentMethodId'] = $this->base->create_ddl_paymethod();
		$this->load->view('index', $data);
		$this->load->view('shared/footer');
	}

	private function _auto_generate_pam_bill() {
		$param = array('period' => date('Y-m') . '-' . $this->config->item('bill_open_day'),
						'duedate' => date('Y-m') . '-' . $this->config->item('bill_due_day'),
						'user' => $this->session->userdata('user_session')->Username
					);
		$this->pam->generate_billing_customer($param);
	}

	public function index_reactivate_pam() {
		$header['page_title'] = 'Daftar PAM Non Aktif';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('index_reactivate_pam');
		$this->load->view('shared/footer');
	}

	public function add_screening_pam() {
		$header['page_title'] = 'Add Tagihan PAM';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('add_screening_pam');
		$this->load->view('shared/footer');
	}

	public function get_ddl_unit() {
		echo json_encode($this->pam->get_ddl_unit($this->input->post('ClusterId')));
	}

	public function list_tagihan_pam() {
		$list = $this->pam->get_list_tagihan_pam();
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
			$row[] = $item->Amount;
			$row[] = $item->Tax;
			$row[] = NULL;
			$row[] = $item->TotalAmount;
			$row[] = NULL;
			$row[] = $item->IsSettle;
			$row[] = $item->TotalPenaltyAmount;
			if($item->IsSettle == 0) {
				if ($this->session->userdata('user_session')->Role == 'CASHIER') {
					if ($item->PAMCubic == 0) {
						$row[] = '<a class="btn btn-sm btn-warning" href="javascript:void(0)" title="Pending"><i></i> Pending</a>';
					} else {
						$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Proses" onclick="edit_record('."'".$item->Id."'".', '."'".$item->CustId."'".', '."'".$item->UnitId."'".')"><i></i> Proses</a>';
					}
				} else {
					$url = site_url("pam/add_screening_pam") . '?id=' . $item->Id;
					if ($item->PAMCubic == 0) {
						$row[] = '<a class="btn btn-sm btn-primary" href="javascript:void(0)" title="Proses" onclick="window.location='."'".$url."'".'"><i></i> Proses</a>';
					} else {
						$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" title="Edit" onclick="window.location='."'".$url."'".'"><i></i> Edit</a>';
					}
				}
			} else {
				$row[] = '<a class="btn btn-sm btn-success" href="javascript:void(0)" onclick="print_invoice('."'".$item->PaymentId."'".')" title="Print"><i></i> Print</a>';
			}
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->pam->count_all_list_tagihan_pam(),
						"recordsFiltered" => $this->pam->count_filtered_list_tagihan_pam(),
						"data" => $data,
				);
		// var_dump($output);die();
		echo json_encode($output);
	}

	public function list_reactivate_pam() {
		$list = $this->pam->get_list_reactivate_pam($this->config->item('pam_max_penalty_month'));
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
			$row[] = $item->TotalUnsettleBill;
			$row[] = $item->StartPeriod;
			$row[] = $item->EndPeriod;
			$row[] = $item->Module;
			$row[] = $item->PaymentMethod;
			$row[] = $item->Amount;
			$row[] = $item->PAMCubic;
			$row[] = $item->TotalAmount;
			$row[] = '';
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->pam->count_all_list_reactivate_pam($this->config->item('pam_max_penalty_month')),
						"recordsFiltered" => $this->pam->count_filtered_list_reactivate_pam($this->config->item('pam_max_penalty_month')),
						"data" => $data,
				);
		// var_dump($output);die();
		echo json_encode($output);
	}

	public function ajax_add() {
		$this->_validate_screening_pam();
		$insert = $this->pam->save($this->input->post());
		if ($insert) {
			echo json_encode(array("status" => TRUE));
		} else {
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['inputerror'][] = 'Period';
			$data['error_string'][] = 'Billing sudah terbentuk pada Periode tersebut';
			$data['status'] = FALSE;
			echo json_encode($data);
			exit();
		}
	}

	private function _validate_screening_pam() {
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
				$data['error_string'][] = 'Customer is required';
				$data['status'] = FALSE;
			}

			if($param['Period'] == '') {
				$data['inputerror'][] = 'Period';
				$data['error_string'][] = 'Period is required';
				$data['status'] = FALSE;
			}

			if($param['PamThisMonth'] == '') {
				$data['inputerror'][] = 'PamThisMonth';
				$data['error_string'][] = 'Meteran Akhir is required';
				$data['status'] = FALSE;
			}

			if($this->base->toNum($param['PamThisMonth']) <= $this->base->toNum($param['PamLastMonth'])) {
				$data['inputerror'][] = 'PamThisMonth';
				$data['error_string'][] = 'Meteran Akhir harus lebih besar dari ' . $param['PamLastMonth'];
				$data['status'] = FALSE;
			}

			if($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
	}

	private function _validate_tagihan_pam() {
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

	public function get_total_billing_pam() {
		$res = $this->base->get_total_billing_pam($this->input->post('custid'), $this->input->post('unitid'));
		echo json_encode($res);
	}

	public function list_tagihan_pam_update() {
			$this->_validate_tagihan_pam();
			$paramdata = $this->input->post();
			// var_dump($paramdata);die();
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
			$this->pam->update_list_tagihan_pam($data);
			echo json_encode(array("status" => TRUE));
	}

	public function pam_screening_edit() {
			$data = $this->pam->get_screening_pam_by_id($this->input->post('Id'));
			echo json_encode($data);
	}

	public function ajax_update() {
		$this->_validate_screening_pam();
		$insert = $this->pam->update($this->input->post());
		echo json_encode(array("status" => TRUE));
	}

	//for_user_customer
	public function customer_pam_bill() {
		$header['page_title'] = 'Tagihan PAM';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('list_customer_pam_bill');
		$this->load->view('shared/footer');
	}

	public function customer_pam_list() {
		$user_name = $this->session->userdata('user_session')->Username;
		$list = $this->pam->get_billing_customer($user_name);
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
			$row[] = $item->PAMCubic;
			$row[] = $item->TotalAmount;
			$row[] = $item->Notes;
			$row[] = $item->Module;
			$row[] = $item->UnitStatus;
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->pam->count_all_billing_customer($user_name),
						"recordsFiltered" => $this->pam->count_filter_billing_customer($user_name),
						"data" => $data,
				);
		// var_dump($output);die();
		echo json_encode($output);
	}

	public function get_customer_bill_details() {
		$res = $this->pam->get_customer_bill_details($this->session->userdata('user_session')->Username);
		echo json_encode($res);
	}

	//for_user_ext
	public function customer_pam_bill_all() {
		$header['page_title'] = 'Tagihan PAM';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('list_customer_pam_bill_all');
		$this->load->view('shared/footer');
	}

	public function customer_pam_list_all() {
		$user_name = $this->session->userdata('user_session')->Username;
		$list = $this->pam->get_billing_customer_all();
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
			$row[] = $item->PAMCubic;
			$row[] = $item->TotalAmount;
			$row[] = $item->Notes;
			$row[] = $item->Module;
			$row[] = $item->UnitStatus;
			$data[] = $row;
		}

		$output = array(
						"draw" => $_POST['draw'],
						"recordsTotal" => $this->pam->count_all_billing_customer_all(),
						"recordsFiltered" => $this->pam->count_filter_billing_customer_all(),
						"data" => $data,
				);
		// var_dump($output);die();
		echo json_encode($output);
	}

	public function pam_get_last_cubic() {
		$param = $this->input->post();
		$data = $this->pam->pam_get_last_cubic($param['custid'], $param['unitid'], $param['period']);
		// var_dump($data);die();
		echo json_encode($data);
	}
}

?>