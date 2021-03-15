<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class RegisterUser {
	
	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}

	/*
	 * Dialog for administrators to register a new user.
	 * No double-opt-in required.
	 */
	public function admin() {
		$user = new User($this->db, $this->role);
		$basic = new Basic($this->db, $this->auth, $this->role);
		if ($user->isAdmin()) {
			$possibleRoles = $this->role->getPossibleRoles($this->role->getRole());
			$userRole = true;
			if (!in_array($this->role->getUserRole(), $possibleRoles)) {
				$userRole = false;
			}
			$passwordProof = true;
			$emailProof = true;
			$registered = false;
			if (isset($_POST['action'])) {
				if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					if ($_POST['password']==$_POST['password2']) {
						if ($basic->checkMail($_POST['email'])) {
							if($user->register($_POST['nickname'], $_POST['password'], $_POST['email'], $this->auth)) {
								$email = $this->db->escapeString($_POST['email']);
								$this->db->query("UPDATE `email` SET `confirmed`='1' WHERE `email`='$email'");
								$registered = true;
								$userID = $user->getIDbyName($_POST['nickname']);
								if (!empty($_POST['role'])) {
									if(($_POST['role']!=$this->role->getRole())&&(in_array($_POST['role'],$possibleRoles))) {
										$user->changeRole($userID,$_POST['role']);									
									}
								}
							}
							else {
								$registered = false;
							}
						}
						else {
							$emailProof = false;
						}
					}
					else {
						$passwordProof = false;
					}
				}
			}
			$roles = array();
			foreach ($possibleRoles as $possibleRole) {
				if ($possibleRole!=$this->role->getRole()) {
					$possibleRole = $this->db->escapeString($possibleRole);
					$result = $this->db->query("SELECT * FROM `role` WHERE `role`='$possibleRole'");
					while ($row = $this->db->fetchArray($result)) {
						array_push($roles,array('role'=>$row['role'],'name'=>htmlentities($row['name'], null, "UTF-8")));
					}
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/register.tpl.php");
		}
	}
}
?>