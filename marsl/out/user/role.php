<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/user.php");

class Role {

	private $db;
	private $possibleRoles;
	private $currentRole;
	private $guestRole;
	private $rolesByUser;
	private $standardUserRole;
	private $nameOfRoles;
	private $idOfRoles;
	private $roles;
	private $adminRoles;

	public function __construct($db) {
		$this->db = $db;
		$this->possibleRoles = array();
		$this->rolesByUser = array();
		$this->nameOfRoles = array();
		$this->idOfRoles = array();
		$this->roles = array();
		$this->adminRoles = array();
	}
	
	/*
	 * Get the current user role.
	 */
	public function getRole() {
        if ($this->currentRole == null) {
            $user = new User($this->db, null);
            if ($user->isGuest()) {
                $this->currentRole =  $this->getGuestRole();
            } else {
                $session = $this->db->escapeString($user->getSession());
                $result = $this->db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `sessionid`='$session' AND `deleted`='0'");
                $role = "";
                while ($row = $this->db->fetchArray($result)) {
                    $role = $row['role'];
                }
                $this->currentRole = $role;
            }
        }
		return $this->currentRole;
	}
	
	/*
	 * Get the standard guest role.
	 */
	public function getGuestRole() {
        if ($this->guestRole == null) {
            $result = $this->db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`guest`");
            $role = "";
            while ($row = $this->db->fetchArray($result)) {
                $role = $row['role'];
            }
			$this->guestRole = $role;
        }
		return $this->guestRole;
	}
	
	/*
	 * Get a role of a user.
	 */
	public function getRolebyUser($user) {
		$role = "";
        if (!array_key_exists($user, $this->rolesByUser)) {
            $user = $this->db->escapeString($user);
            $result = $this->db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `user`='$user' AND `deleted`='0'");
            while ($row = $this->db->fetchArray($result)) {
                $role = $row['role'];
            }
			$this->rolesByUser[$user] = $role;
        }
		else {
			$role = $this->rolesByUser[$user];
		}
		return $role;
	}
	
	/*
	 * Get the standard user role.
	 */
	public function getUserRole() {
        if ($this->standardUserRole == null) {
            $result = $this->db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`user`");
            $role = "";
            while ($row = $this->db->fetchArray($result)) {
                $role = $row['role'];
            }
			$this->standardUserRole = $role;
        }
		return $this->standardUserRole;
	}
	
	/*
	 * Get the name of a role.
	 */
	public function getNamebyID($id) {
		$name = "";
        if (!array_key_exists($id, $this->nameOfRoles)) {
            $id = $this->db->escapeString($id);
            $result = $this->db->query("SELECT `name` FROM `role` WHERE `role`='$id'");
            while ($row = $this->db->fetchArray($result)) {
                $name = $row['name'];
            }
			$this->nameOfRoles[$id] = $name;
        }
		else {
			$name = $this->nameOfRoles[$id];
		}
		return $name;
	}
	
	/*
	 * Get the ID of a role by a given name.
	 */
	public function getIDbyName($name) {
		$role = "";
        if (!array_key_exists($name, $this->idOfRoles)) {
            $name = $this->db->escapeString($name);
            $result = $this->db->query("SELECT `role` FROM `role` WHERE `name`='$name'");
            while ($row = $this->db->fetchArray($result)) {
                $role = $row['role'];
            }
			$this->idOfRoles[$name] = $role;
        }
		else {
			$role = $this->idOfRoles[$name];
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
		if ($this->db->isExisting("SELECT `role` FROM `rights_module` WHERE `role`= '$role' AND `module`='$module' LIMIT 1")) {
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
		if ($this->db->isExisting("SELECT `role` FROM `rights` WHERE `role`='$role' AND `location`='$location' LIMIT 1")) {
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
		if (!$this->db->isExisting("SELECT `role` FROM `role` WHERE `name`='$name' LIMIT 1")) {
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
		if (array_key_exists($role, $this->possibleRoles)) {
			$roles = $this->possibleRoles[$role];
		}
		else {
            $role = $this->db->escapeString($role);
            $result = $this->db->query("SELECT `slave` FROM `role_editor` WHERE `master`='$role'");
            while ($row = $this->db->fetchArray($result)) {
                $slaves = $this->getPossibleRoles($row['slave']);
                foreach ($slaves as $slave) {
                    array_push($roles, $slave);
                }
            }
            array_push($roles, $role);
			$this->possibleRoles[$role] = $roles;
        }
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
        if (count($this->roles) == 0) {
            $result = $this->db->query("SELECT `role`, `name` FROM `role`");
            while ($row = $this->db->fetchArray($result)) {
                array_push($this->roles, $row);
            }
        }
		return $this->roles;
	}
	
	/*
	 * Get all administrative roles.
	 */
	public function getAdminRoles() {
        if (count($this->adminRoles) == 0) {
			$allRoles = $this->getRoles();
            foreach ($allRoles as $role) {
                $roleID = $this->db->escapeString($role['role']);
                $location = $this->db->isExisting("SELECT `role` FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
                $module = $this->db->isExisting("SELECT `role` FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
                $master = $this->db->isExisting("SELECT `master` FROM `role_editor` WHERE `master` = '$roleID' LIMIT 1");
                if ($location || $module || $master) {
                    array_push($roles, $role);
                }
            }
        }
		return $this->adminRoles;
	}
}
?>