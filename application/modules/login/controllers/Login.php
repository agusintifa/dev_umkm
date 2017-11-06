<?php 
	class Login extends MX_Controller {

		public function index() {
			/*if ($this->session->userdata('user_session'))
				return redirect('Home');*/
			$this->load->helper('form');
			$header['page_title'] = 'Login';
			$header['user_data'] = $this->session->userdata('user_session');
			$this->load->view('login_form', $header);
		}

		public function login_process() {
			$this->load->library('form_validation');

			$this->form_validation->set_rules('username', 'User Name', 'required|max_length[100]|trim');
			$this->form_validation->set_rules('password', 'Password', 'required');
			$this->form_validation->set_error_delimiters("<p class='text-danger'>","</p>");

			if ($this->form_validation->run('login_process')) {
				//SUCCESS
				$username = $this->input->post('username');
				$password = $this->input->post('password');

				$this->load->model('LoginModel');
				$login_data = $this->LoginModel->login_valid($username, $password);
				if ($login_data) {
					//credentials valid, login user
					// $this->load->library('session'); //move to -> config->autoload.php
					$this->session->set_userdata('user_session', $login_data);
					if ($login_data->Role == 'CASHIER') {
						return redirect('ipl/index_price');
					} else if ($login_data->Role == 'ADMIN') {
						return redirect('ref/index_user');
					} else if ($login_data->Role == 'EXTENDED DEPARTMENT') {
						return redirect('ipl/index_extended');
					} else if ($login_data->Role == 'PAM OFFICER') {
						return redirect('pam/index');
					} else if ($login_data->Role == 'CUSTOMER') {
						return redirect('ipl/customer_ipl_bill');
					}
					return redirect('home');
				} else {
					//authentication failed
					$this->session->set_flashdata('login_failed', 'Invalid Username or Password.');
					return redirect('login');
				}
			} else {
				//FAILED
				$header['page_title'] = 'Login';
				$this->load->view('login_form', $header);
			}
		}

		public function logout() {
			$this->session->unset_userdata('user_session');
			return redirect('login');
		}
	}
?>