<?php
	class SharedModel extends CI_Model {
		public function __construct() {
			parent::__construct();
			if (!$this->session->userdata('user_id'))
				return redirect('login');
		}

		public function billing_list() {
			$user_id = $this->session->userdata('user_id');
			$query = $this->db
							 ->select('Title')
							 ->from('articles')
							 ->where('User_Id', $user_id)
							 ->get();
			return $query->result();
		}
	}
?>