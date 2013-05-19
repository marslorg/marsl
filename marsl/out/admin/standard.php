<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class Standard {
	
	/*
	 * Set the standard roles for guest users and newly registered users in the guest registration dialog.
	 */
	public function admin() {
		$db = new DB();
		
		$user = new User();
		$auth = new Authentication();
		
		if ($user->isAdmin()) {
			if ($user->isHead()) {
				$role = new Role();
				$possibleRoles = $role->getPossibleRoles($role->getRole());
				if (isset($_POST['action'])) {
					if ($_POST['action']=="change") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$stdUser = mysql_real_escape_string($_POST['user']);
							$guest = mysql_real_escape_string($_POST['guest']);
							if (($role->getRole()!=$stdUser)&&($role->getRole()!=$guest)) {
								if (in_array($stdUser,$possibleRoles)&&in_array($guest,$possibleRoles)) {
									if ($db->isExisting("SELECT * FROM `stdroles`")) {
										$db->query("UPDATE `stdroles` SET `guest`='$guest', `user`='$stdUser'");
									}
									else {
										$db->query("INSERT INTO `stdroles`(`guest`,`user`) VALUES('$guest','$stdUser')");
									}
								}
							}
						}
					}
				}
				$stdUser = "";
				$guest = "";
				$result = $db->query("SELECT * FROM `stdroles`");
				while ($row = mysql_fetch_array($result)) {
					$stdUser = $row['user'];
					$guest = $row['guest'];
				}
				$roles = array();
				foreach ($possibleRoles as $possibleRole) {
					$possibleRole = mysql_real_escape_string($possibleRole);
					$result = $db->query("SELECT * FROM `role` WHERE `role`='$possibleRole'");
					while ($row = mysql_fetch_array($result)) {
						if ($role->getRole()!=$row['role']) {
							array_push($roles,array('role' => htmlentities($row['role'], ENT_HTML5, "ISO-8859-1"), 'name' => htmlentities($row['name'], ENT_HTML5, "ISO-8859-1")));
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