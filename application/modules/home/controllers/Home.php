<?php

class Home extends MX_Controller {
	public function index() {
		$header['page_title'] = 'Halaman Utama';
		$header['user_data'] = $this->session->userdata('user_session');
		$this->load->view('shared/header', $header);
		$this->load->view('main');
		$this->load->view('shared/footer');
	}

	public function main() {
		$this->load->view('main');
	}
}

?>