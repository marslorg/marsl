<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class Standard {
	
	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}

	/*
	 * Set the standard roles for guest users and newly registered users in the guest registration dialog.
	 */
	public function admin() {
		$user = new User($this->db, $this->role);
		
		if ($user->isAdmin()) {
			if ($user->isHead()) {
				$possibleRoles = $this->role->getPossibleRoles($this->role->getRole());
				if (isset($_POST['action'])) {
					if ($_POST['action']=="change") {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$stdUser = $this->db->escapeString($_POST['user']);
							$guest = $this->db->escapeString($_POST['guest']);
							if (($this->role->getRole()!=$stdUser)&&($this->role->getRole()!=$guest)) {
								if (in_array($stdUser,$possibleRoles)&&in_array($guest,$possibleRoles)) {
									if ($this->db->isExisting("SELECT `user` FROM `stdroles` LIMIT 1")) {
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
				$result = $this->db->query("SELECT `user`, `guest` FROM `stdroles`");
				while ($row = $this->db->fetchArray($result)) {
					$stdUser = $row['user'];
					$guest = $row['guest'];
				}
				$roles = array();
				foreach ($possibleRoles as $possibleRole) {
					$possibleRole = $this->db->escapeString($possibleRole);
					$result = $this->db->query("SELECT `role`, `name` FROM `role` WHERE `role`='$possibleRole'");
					while ($row = $this->db->fetchArray($result)) {
						if ($this->role->getRole()!=$row['role']) {
							array_push($roles,array('role' => htmlentities($row['role'], null, "UTF-8"), 'name' => htmlentities($row['name'], null, "UTF-8")));
						}
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/standard.tpl.php");
			}
		}
	}
}
?>