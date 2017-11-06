<?php

class Shared extends CI_Controller {
	public function index() {
		echo "Ini Module Shared";
	}

	public function get_header() {
		$this->load->view('header');
	}

	public function get_footer() {
		$this->load->view('footer');
	}

	public function get_login() {
		$this->load->view('login');
	}
}

?>