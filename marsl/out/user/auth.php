<?php
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/role.php");
include_once(dirname(__FILE__)."/user.php");

class Authentication {
	
	/*
	 * Gets the rights matrix for a given module and role.
	 */
	private function moduleRight($module, $roleID) {
		$role = new Role();
		$db = new DB();
		$module = mysql_real_escape_string($module);
		$rights['read'] = 0;
		$rights['write'] = 0;
		$rights['extended'] = 0;
		$rights['admin'] = 0;
		$roles = $role->getPossibleRoles($roleID);
		foreach ($roles as $roleID) {
			$roleID = mysql_real_escape_string($roleID);
			if ($db->isExisting("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$module' AND `read`='1'")) {
				$rights['read'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$module' AND `write`='1'")) {
				$rights['write'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$module' AND `extended`='1'")) {
				$rights['extended'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$module' AND `admin`='1'")) {
				$rights['admin'] = 1;
			}
		}
		return $rights;
	}
	
	/*
	 * Evaluates the rights matrix.
	 */
	private function evalModuleRights($right, $module, $roleID) {
		$rights = $this->moduleRight($module, $roleID);
		if (empty($rights)) {
			return 0;
		}
		else {
			return $rights[$right];
		}
	}
	
	/*
	 * Returns whether the role has read rights on a module.
	 */
	public function moduleReadAllowed($module, $roleID) {
		return $this->evalModuleRights("read", $module, $roleID);
	}
	
	/*
	 * Returns whether the role has write rights on a module.
	*/
	public function moduleWriteAllowed($module, $roleID) {
		return $this->evalModuleRights("write", $module, $roleID);
	}
	
	/*
	 * Returns whether the role has extended rights on a module.
	*/
	public function moduleExtendedAllowed($module, $roleID) {
		return $this->evalModuleRights("extended", $module, $roleID);
	}
	
	/*
	 * Returns whether the role has administrative rights on a module.
	*/
	public function moduleAdminAllowed($module, $roleID) {
		return $this->evalModuleRights("admin", $module, $roleID);
	}
	
	/*
	 * Gets the rights matrix for a given location and role.
	 */
	private function locationRight($location, $roleID) {
		$role = new Role();
		$location = mysql_real_escape_string($location);
		$db = new DB();
		$rights['read'] = 0;
		$rights['write'] = 0;
		$rights['extended'] = 0;
		$rights['admin'] = 0;
		$roles = $role->getPossibleRoles($roleID);
		foreach ($roles as $roleID) {
			$roleID = mysql_real_escape_string($roleID);
			if ($db->isExisting("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$location' AND `read`='1'")) {
				$rights['read'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$location' AND `write`='1'")) {
				$rights['write'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$location' AND `extended`='1'")) {
				$rights['extended'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$location' AND `admin`='1'")) {
				$rights['admin'] = 1;
			}
		}
		return $rights;
	}
	
	/*
	 * Evaluates the rights matrix.
	 */
	private function evalLocationRights($right, $location, $roleID) {
		$rights = $this->locationRight($location, $roleID);
		if (empty($rights)) {
			return 0;
		}
		else {
			return $rights[$right];
		}
	}
	
	/*
	 * Returns whether the role has read rights on a location.
	*/
	public function locationReadAllowed($location, $roleID) {
		return $this->evalLocationRights("read", $location, $roleID);
	}
	
	/*
	 * Returns whether the role has write rights on a location.
	*/
	public function locationWriteAllowed($location, $roleID) {
		return $this->evalLocationRights("write", $location, $roleID);
	}
	
	/*
	 * Returns whether the role has extended rights on a location.
	*/
	public function locationExtendedAllowed($location, $roleID) {
		return $this->evalLocationRights("extended", $location, $roleID);
	}
	
	/*
	 * Returns whether the role has administrative rights on a location.
	*/
	public function locationAdminAllowed($location, $roleID) {
		return $this->evalLocationRights("admin", $location, $roleID);
	}
	
	/*
	 * Get a token to prevent CSRF attacks.
	 */
	public function getToken($time) {
		$user = new User();
		$session = $user->getSession();
		$userID = $user->getID();
		$password = $user->getPassbyID($userID);
		$token = md5($session.$userID.$password.$time);
		return $token;
	}
	
	/*
	 * Check a token to prevent CSRF attacks.
	 */
	public function checkToken($time, $token) {
		$user = new User();
		$session = $user->getSession();
		$userID = $user->getID();
		$password = $user->getPassbyID($userID);
		$proof = md5($session.$userID.$password.$time);
		return ($token==$proof);
	}
	
}
?>