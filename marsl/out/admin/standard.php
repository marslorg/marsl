<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class Standard {
	
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	/*
	 * Set the standard roles for guest users and newly registered users in the guest registration dialog.
	 */
	public function admin() {
		$user = new User($this->db);
		$auth = new Authentication($this->db);
		
		if ($user->isAdmin()) {
			if ($user->isHead()) {
				$role = new Role($this->db);
				$possibleRoles = $role->getPossibleRoles($role->getRole());
				if (isset($_POST['action'])) {
					if ($_POST['action']=="change") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$stdUser = $this->db->escapeString($_POST['user']);
							$guest = $this->db->escapeString($_POST['guest']);
							if (($role->getRole()!=$stdUser)&&($role->getRole()!=$guest)) {
								if (in_array($stdUser,$possibleRoles)&&in_array($guest,$possibleRoles)) {
									if ($this->db->isExisting("SELECT * FROM `stdroles`")) {
										$this->db->query("UPDATE `stdroles` SET `guest`='$guest', `user`='$stdUser'");
									}
									else {
										$this->db->query("INSERT INTO `stdroles`(`guest`,`user`) VALUES('$guest','$stdUser')");
									}
								}
							}
						}
					}
				}
				$stdUser = "";
				$guest = "";
				$result = $this->db->query("SELECT * FROM `stdroles`");
				while ($row = $this->db->fetchArray($result)) {
					$stdUser = $row['user'];
					$guest = $row['guest'];
				}
				$roles = array();
				foreach ($possibleRoles as $possibleRole) {
					$possibleRole = $this->db->escapeString($possibleRole);
					$result = $this->db->query("SELECT * FROM `role` WHERE `role`='$possibleRole'");
					while ($row = $this->db->fetchArray($result)) {
						if ($role->getRole()!=$row['role']) {
							array_push($roles,array('role' => htmlentities($row['role'], null, "ISO-8859-1"), 'name' => htmlentities($row['name'], null, "ISO-8859-1")));
						}
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/standard.tpl.php");
			}
		}
	}
}
?>