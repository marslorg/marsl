<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/user.php");

class Role {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	/*
	 * Get the current user role.
	 */
	public function getRole() {
		$user = new User($this->db);
		if ($user->isGuest()) {
			return $this->getGuestRole();
		}
		else {
			$session = $this->db->escapeString($user->getSession());
			$result = $this->db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `sessionid`='$session' AND `deleted`='0'");
			$role = "";
			while ($row = $this->db->fetchArray($result)) {
				$role = $row['role'];
			}
			return $role;
		}
	}
	
	/*
	 * Get the standard guest role.
	 */
	public function getGuestRole() {
		$result = $this->db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`guest`");
		$role = "";
		while ($row = $this->db->fetchArray($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Get a role of a user.
	 */
	public function getRolebyUser($user) {
		$user = $this->db->escapeString($user);
		$result = $this->db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `user`='$user' AND `deleted`='0'");
		$role = "";
		while ($row = $this->db->fetchArray($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Get the standard user role.
	 */
	public function getUserRole() {
		$result = $this->db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`user`");
		$role = "";
		while ($row = $this->db->fetchArray($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Get the name of a role.
	 */
	public function getNamebyID($id) {
		$name = "";
		$id = $this->db->escapeString($id);
		$result = $this->db->query("SELECT `name` FROM `role` WHERE `role`='$id'");
		while ($row = $this->db->fetchArray($result)) {
			$name = $row['name'];
		}
		return $name;
	}
	
	/*
	 * Get the ID of a role by a given name.
	 */
	public function getIDbyName($name) {
		$role = "";
		$name = $this->db->escapeString($name);
		$result = $this->db->query("SELECT `role` FROM `role` WHERE `name`='$name'");
		while ($row = $this->db->fetchArray($result)) {
			$role = $row['role'];
		}
		return $role;
	}
	
	/*
	 * Set the rights for a module.
	 */
	public function setModuleRights($role, $module, $read, $write, $extended, $admin) {
		$role = $this->db->escapeString($role);
		$module = $this->db->escapeString($module);
		$read = $this->db->escapeString($read);
		$write = $this->db->escapeString($write);
		$extended = $this->db->escapeString($extended);
		$admin = $this->db->escapeString($admin);
		if ($this->db->isExisting("SELECT * FROM `rights_module` WHERE `role`= '$role' AND `module`='$module'")) {
			$this->db->query("UPDATE `rights_module` SET `read` = '$read', `write` = '$write', `extended` = '$extended', `admin` = '$admin' WHERE `role` = '$role' AND `module` = '$module'");
		}
		else {
			$this->db->query("INSERT INTO `rights_module`(`role`,`module`,`read`,`write`,`extended`,`admin`) VALUES('$role','$module','$read','$write','$extended','$admin')");
		}
	}
	
	/*
	 * Set the rights for a location.
	 */
	public function setRights($role, $location, $read, $write, $extended, $admin) {
		$role = $this->db->escapeString($role);
		$location = $this->db->escapeString($location);
		$read = $this->db->escapeString($read);
		$write = $this->db->escapeString($write);
		$extended = $this->db->escapeString($extended);
		$admin = $this->db->escapeString($admin);
		if ($this->db->isExisting("SELECT * FROM `rights` WHERE `role`='$role' AND `location`='$location'")) {
			$this->db->query("UPDATE `rights` SET `read` = '$read', `write` = '$write', `extended`='$extended', `admin` = '$admin' WHERE `role`='$role' AND `location`='$location'");
		}
		else {
			$this->db->query("INSERT INTO `rights`(`role`,`location`,`read`,`write`,`extended`,`admin`) VALUES('$role','$location','$read','$write','$extended','$admin')");
		}
	}
	
	/*
	 * Create a new role.
	 */
	public function createRole($name) {
		$name = $this->db->escapeString($name);
		if (!$this->db->isExisting("SELECT * FROM `role` WHERE `name`='$name'")) {
			$this->db->query("INSERT INTO `role`(`name`) VALUES('$name')");
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
		$role = $this->db->escapeString($role);
		if ($this->db->isExisting("SELECT * FROM `role_editor` WHERE `master`='$role'")) {
			$result = $this->db->query("SELECT slave FROM `role_editor` WHERE `master`='$role'");
			while ($row = $this->db->fetchArray($result)) {
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
		$result = $this->db->query("SELECT * FROM `role`");
		while($row = $this->db->fetchArray($result)) {
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
		foreach($allRoles as $role) {
			$roleID = $this->db->escapeString($role['role']);
			$location = $this->db->isExisting("SELECT * FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1'");
			$module = $this->db->isExisting("SELECT * FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1'");
			$master = $this->db->isExisting("SELECT * FROM `role_editor` WHERE `master` = '$roleID'");
			if ($location || $module || $master) {
				array_push($roles, $role);
			}
		}
		return $roles;
	}
}
?>