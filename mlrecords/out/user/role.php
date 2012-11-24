<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/user.php");

class Role {
	
	/*
	 * Get the current user role.
	 */
	public function getRole() {
		$user = new User();
		if ($user->isGuest()) {
			return $this->getGuestRole();
		}
		else {
			$session = mysql_real_escape_string($user->getSession());
			$db = new DB();
			$result = $db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `sessionid`='$session' AND `deleted`='0'");
			$role = "";
			while ($row = mysql_fetch_array($result)) {
				$role = $row['role'];
			}
			return $role;
		}
	}
	
	/*
	 * Get the standard guest role.
	 */
	public function getGuestRole() {
		$db = new DB();
		$result = $db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`guest`");
		$role = "";
		while ($row = mysql_fetch_array($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Get a role of a user.
	 */
	public function getRolebyUser($user) {
		$user = mysql_real_escape_string($user);
		$db = new DB();
		$result = $db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `user`='$user' AND `deleted`='0'");
		$role = "";
		while ($row = mysql_fetch_array($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Get the standard user role.
	 */
	public function getUserRole() {
		$db = new DB();
		$result = $db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`user`");
		$role = "";
		while ($row = mysql_fetch_array($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Get the name of a role.
	 */
	public function getNamebyID($id) {
		$name = "";
		$id = mysql_real_escape_string($id);
		$db = new DB();
		$result = $db->query("SELECT `name` FROM `role` WHERE `role`='$id'");
		while ($row = mysql_fetch_array($result)) {
			$name = $row['name'];
		}
		return $name;
	}
	
	/*
	 * Get the ID of a role by a given name.
	 */
	public function getIDbyName($name) {
		$role = "";
		$name = mysql_real_escape_string($name);
		$db = new DB();
		$result = $db->query("SELECT `role` FROM `role` WHERE `name`='$name'");
		while ($row = mysql_fetch_array($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Set the rights for a module.
	 */
	public function setModuleRights($role, $module, $read, $write, $extended, $admin) {
		$db = new DB();
		$role = mysql_real_escape_string($role);
		$module = mysql_real_escape_string($module);
		$read = mysql_real_escape_string($read);
		$write = mysql_real_escape_string($write);
		$extended = mysql_real_escape_string($extended);
		$admin = mysql_real_escape_string($admin);
		if ($db->isExisting("SELECT * FROM `rights_module` WHERE `role`= '$role' AND `module`='$module'")) {
			$db->query("UPDATE `rights_module` SET `read` = '$read', `write` = '$write', `extended` = '$extended', `admin` = '$admin' WHERE `role` = '$role' AND `module` = '$module'");
		}
		else {
			$db->query("INSERT INTO `rights_module`(`role`,`module`,`read`,`write`,`extended`,`admin`) VALUES('$role','$module','$read','$write','$extended','$admin')");
		}
	}
	
	/*
	 * Set the rights for a location.
	 */
	public function setRights($role, $location, $read, $write, $extended, $admin) {
		$db = new DB();
		$role = mysql_real_escape_string($role);
		$location = mysql_real_escape_string($location);
		$read = mysql_real_escape_string($read);
		$write = mysql_real_escape_string($write);
		$extended = mysql_real_escape_string($extended);
		$admin = mysql_real_escape_string($admin);
		if ($db->isExisting("SELECT * FROM `rights` WHERE `role`='$role' AND `location`='$location'")) {
			$db->query("UPDATE `rights` SET `read` = '$read', `write` = '$write', `extended`='$extended', `admin` = '$admin' WHERE `role`='$role' AND `location`='$location'");
		}
		else {
			$db->query("INSERT INTO `rights`(`role`,`location`,`read`,`write`,`extended`,`admin`) VALUES('$role','$location','$read','$write','$extended','$admin')");
		}
	}
	
	/*
	 * Create a new role.
	 */
	public function createRole($name) {
		$name = mysql_real_escape_string($name);
		$db = new DB();
		if (!$db->isExisting("SELECT * FROM `role` WHERE `name`='$name'")) {
			$db->query("INSERT INTO `role`(`name`) VALUES('$name')");
			return true;
		}
		else {
			return false;
		}
	}
	
	/*
	 * Get the slave roles for which the given role is the root.
	 */
	public function getPossibleRoles($role) {
		$roles = array();
		$db = new DB();
		$role = mysql_real_escape_string($role);
		if ($db->isExisting("SELECT * FROM `role_editor` WHERE `master`='$role'")) {
			$result = $db->query("SELECT slave FROM `role_editor` WHERE `master`='$role'");
			while ($row = mysql_fetch_array($result)) {
				$slaves = $this->getPossibleRoles($row['slave']);
				foreach ($slaves as $slave) {
					array_push($roles,$slave);
				}
			}
		}
		array_push($roles,$role);
		return $roles;
	}
	
	/*
	 * Return whether a given role is a master of another given role.
	 */
	public function isMaster($master, $slave, $possibleRoles) {
		if ($master != $slave) {
			foreach ($possibleRoles as $possibleRole) {
				if ($slave == $possibleRole) {
					return true;
				}
			}
		}
		return false;
	}
	
	/*
	 * Get all roles.
	 */
	public function getRoles() {
		$roles = array();
		$db = new DB();
		$result = $db->query("SELECT * FROM `role`");
		while($row = mysql_fetch_array($result)) {
			array_push($roles, $row);
		}
		return $roles;
	}
	
	/*
	 * Get all administrative roles.
	 */
	public function getAdminRoles() {
		$allRoles = $this->getRoles();
		$roles = array();
		$db = new DB();
		foreach($allRoles as $role) {
			$roleID = mysql_real_escape_string($role['role']);
			$location = $db->isExisting("SELECT * FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1'");
			$module = $db->isExisting("SELECT * FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1'");
			$master = $db->isExisting("SELECT * FROM `role_editor` WHERE `master` = '$roleID'");
			if ($location || $module || $master) {
				array_push($roles, $role);
			}
		}
		return $roles;
	}
}
?>