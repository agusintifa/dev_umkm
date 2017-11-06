<?php
	class LoginModel extends CI_Model {

		public function login_valid($username, $password)
		{
			$user = $this->db->query("SELECT CONCAT(role.CodeValue, '-', usr.Id) AS UserId, usr.Username, role.NameValue AS Role
									FROM SysUser usr
									INNER JOIN SysRole role ON usr.RoleId = role.Id
									WHERE usr.UserName = '".$username."' AND usr.Password = '".$password."' AND usr.RecordStatus = 1")->row();
			if ($user != null) {
				return $user;
			} else {
				return false;
			}
		}
	}
?>