<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");

class RoleAdmin {
	
	private $db;
	private $auth;

	public function __construct($db, $auth) {
		$this->db = $db;
		$this->auth = $auth;
	}

	/*
	 * Set up and change roles.
	 */
	public function admin() {
		$user = new User($this->db);
		$role = new Role($this->db);
		if ($user->isAdmin()) {
			if (isset($_POST['action'])) {
				$action = $_POST['action'];
				if ($action=="new") {
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						if ($role->createRole($_POST['role'])) {
							$slave = $this->db->lastInsertedID();
							$master = $this->db->escapeString($role->getRole());
							$this->db->query("INSERT INTO `role_editor`(`master`,`slave`) VALUES('$master','$slave')");
						}
					}
				}
				else if ($action=="change") {
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$roleID = $_POST['role'];
						if ($roleID!=$role->getRole()) {
							if (in_array($roleID,$role->getPossibleRoles($role->getRole()))) {
								$roleID = $this->db->escapeString($roleID);
								$name = $this->db->escapeString($_POST['name']);
								$this->db->query("UPDATE `role` SET `name`='$name' WHERE `role` = '$roleID'");
							}
						}
					}
				}
			}
			else if (isset($_GET['action'])) {
				if ($_GET['action']=="del") {
					if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
						$roleID = $_GET['role'];
						if ($roleID!=$role->getRole()) {
							if (in_array($roleID,$role->getPossibleRoles($role->getRole()))) {
								$roleID = $this->db->escapeString($roleID);
								$ownRole = $this->db->escapeString($role->getRole());
								$this->db->query("UPDATE `user` SET `role`= (SELECT user FROM stdroles) WHERE `role`='$roleID'");
								$this->db->query("UPDATE `role_editor` SET `master`='$ownRole' WHERE `master`='$roleID'");
								$this->db->query("DELETE FROM `role_editor` WHERE `slave`='$roleID'");
								$this->db->query("DELETE FROM `role` WHERE `role`='$roleID'");
							}
						}
					}
				}
			}
			$possibleRoles = $role->getPossibleRoles($role->getRole());
			$roles = array();
			foreach ($possibleRoles as $possibleRole) {
				$possibleRole = $this->db->escapeString($possibleRole);
				$result = $this->db->query("SELECT * FROM `role` WHERE `role` = '$possibleRole'");
				while ($row = $this->db->fetchArray($result)) {
					if ($possibleRole!=$role->getRole()) {
						array_push($roles,array('role'=>htmlentities($row['role'], null, "ISO-8859-1"),'name'=>htmlentities($row['name'], null, "ISO-8859-1")));
					}
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/role.tpl.php");
		}
	}
}

?>