<?php

class Laporan extends MX_Controller {
	function __construct() {
		$this->load->helper('form');
		$this->load->model('LaporanModel', 'lap');
		parent::__construct();
	}

	public function index() {
		$this->load->view('main');
	}

	public function main() {
		$this->load->view('main');
	}

	public function ipl_invoice() {
		define('FPDF_FONTPATH',$this->config->item('fonts_path'));
		$data['hasil'] = $this->lap->get_customer_ipl_bill_details($_GET['Id']);
		// var_dump($data['hasil']);die();
		$this->load->view('ipl_invoice', $data);
	}

	public function pam_invoice() {
		define('FPDF_FONTPATH',$this->config->item('fonts_path'));
		$data['hasil'] = $this->lap->get_customer_pam_bill_details($_GET['Id']);
		// var_dump($data['hasil']);die();
		$this->load->view('pam_invoice', $data);
	}

	public function billing_report() {
		$header['page_title'] = 'Laporan';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$data['PaymentMethodId'] = $this->base->create_ddl_paymethod2();
		$this->load->view('billing_report', $data);
		$this->load->view('shared/footer');
	}

	public function generate_billing_report() {
		define('FPDF_FONTPATH',$this->config->item('fonts_path'));
		/*array(4) {
		["Module"]=>
		string(3) "IPL"
		["PaymentMethodId"]=>
		string(1) "2"
		["StartDate"]=>
		string(10) "2017-05-26"
		["EndDate"]=>
		string(10) "2017-06-03"
		}*/

		$param = array('Module' => $_GET['Module'],
						'PaymentMethodId' => $_GET['PaymentMethodId'],
						'StartDate' => $_GET['StartDate'],
						'EndDate' => $_GET['EndDate'],
					);
		// var_dump($param);die();
		$data['hasil'] = $this->lap->generate_billing_report($param);
		// var_dump($data['hasil']);die();
		$this->load->view('generate_billing_report', $data);
	}
}

?>