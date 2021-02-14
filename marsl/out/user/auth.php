<?php
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/role.php");
include_once(dirname(__FILE__)."/user.php");

class Authentication {
	
	private $db;
	private $locationRights;
	private $moduleRights;

	public function __construct($db) {
		$this->db = $db;

		$curLocationRights = array();
		$result = $this->db->query("SELECT * FROM `rights` WHERE `read`='1' OR `write`='1' OR `extended`='1' OR `admin`='1'");
		while ($row = $this->db->fetchArray($result)) {
			$role = $row['role'];
			$location = $row['location'];
			$read = $row['read'] == 1;
			$write = $row['write'] == 1;
			$extended = $row['extended'] == 1;
			$admin = $row['admin'] == 1;
			if (array_key_exists($role, $curLocationRights) && array_key_exists($location, $curLocationRights[$role])) {
				$curLocationRights[$role][$location]['read'] = $curLocationRights[$role][$location]['read'] || $read;
				$curLocationRights[$role][$location]['write'] = $curLocationRights[$role][$location]['write'] || $write;
				$curLocationRights[$role][$location]['extended'] = $curLocationRights[$role][$location]['extended'] || $extended;
				$curLocationRights[$role][$location]['admin'] = $curLocationRights[$role][$location]['admin'] || $admin;
			}
			else {
				$curLocationRights[$role][$location]['read'] = $read;
				$curLocationRights[$role][$location]['write'] = $write;
				$curLocationRights[$role][$location]['extended'] = $extended;
				$curLocationRights[$role][$location]['admin'] = $admin;
			}
		}
		$this->locationRights = $curLocationRights;

		$curModuleRights = array();
		$moduleResult = $this->db->query("SELECT * FROM `rights_module` WHERE `read`='1' OR `write`='1' OR `extended`='1' OR `admin`='1'");
		while ($moduleRow = $this->db->fetchArray($moduleResult)) {
			$role = $moduleRow['role'];
			$module = $moduleRow['module'];
			$read = $moduleRow['read'] == 1;
			$write = $moduleRow['write'] == 1;
			$extended = $moduleRow['extended'] == 1;
			$admin = $moduleRow['admin'] == 1;
			if (array_key_exists($role, $curModuleRights) && array_key_exists($module, $curModuleRights[$role])) {
				$curModuleRights[$role][$module]['read'] = $curModuleRights[$role][$module]['read'] || $read;
				$curModuleRights[$role][$module]['write'] = $curModuleRights[$role][$module]['write'] || $write;
				$curModuleRights[$role][$module]['extended'] = $curModuleRights[$role][$module]['extended'] || $extended;
				$curModuleRights[$role][$module]['admin'] = $curModuleRights[$role][$module]['admin'] || $admin;
			}
			else {
				$curModuleRights[$role][$module]['read'] = $read;
				$curModuleRights[$role][$module]['write'] = $write;
				$curModuleRights[$role][$module]['extended'] = $extended;
				$curModuleRights[$role][$module]['admin'] = $admin;
			}
		}
		$this->moduleRights = $curModuleRights;
	}
	
	/*
	 * Gets the rights matrix for a given module and role.
	 */
	private function moduleRight($module, $roleID) {
		$role = new Role($this->db);
		$module = $this->db->escapeString($module);
		$rights['read'] = 0;
		$rights['write'] = 0;
		$rights['extended'] = 0;
		$rights['admin'] = 0;
		$roles = $role->getPossibleRoles($roleID);
		foreach ($roles as $roleID) {
			if (array_key_exists($roleID, $this->moduleRights) && array_key_exists($module, $this->moduleRights[$roleID])) {
				$rightsArray = $this->moduleRights[$roleID][$module];
                if ($rightsArray['read']) {
                	$rights['read'] = true;
				}
				if ($rightsArray['write']) {
                	$rights['write'] = true;
				}
				if ($rightsArray['extended']) {
                	$rights['extended'] = true;
				}
				if ($rightsArray['admin']) {
                	$rights['admin'] = true;
				}
			}
		}
		return $rights;
	}
	
	/*
	 * Evaluates the rights matrix.
	 */
	private function evalModuleRights($right, $module, $roleID) {
		$role = new Role($this->db);
		$hasRight = false;
		$roles = $role->getPossibleRoles($roleID);
		$rolesLength = sizeof($roles);
		for ($roleIdx = 0; !$hasRight && $roleIdx < $rolesLength; $roleIdx++) {
			$curRoleID = $roles[$roleIdx];
			if (array_key_exists($roleID, $this->moduleRights) && array_key_exists($module, $this->moduleRights[$curRoleID]) && array_key_exists($right, $this->moduleRights[$curRoleID][$module])) {
				$hasRight = $this->moduleRights[$curRoleID][$module][$right];
			}
		}
		return $hasRight;
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
	 * Evaluates if the role has right for the given location.
	 */
	private function evalLocationRights($right, $location, $roleID) {
		$role = new Role($this->db);
		$hasRight = false;
		$roles = $role->getPossibleRoles($roleID);
		$rolesLength = sizeof($roles);
		for ($roleIdx = 0; !$hasRight && $roleIdx < $rolesLength; $roleIdx++) {
			$curRoleID = $roles[$roleIdx];
			if (array_key_exists($roleID, $this->locationRights) && array_key_exists($location, $this->locationRights[$curRoleID]) && array_key_exists($right, $this->locationRights[$curRoleID][$location])) {
				$hasRight = $this->locationRights[$curRoleID][$location][$right];
			}
		}
		return $hasRight;
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

    public function isAppAllowed() {
		$appIsAuthenticated = false;

		if (isset($_SERVER['PHP_AUTH_USER'])) {
        	if (isset($_SERVER['PHP_AUTH_PW'])) {
        		$appKey = $this->db->escapeString($_SERVER['PHP_AUTH_USER']);
        		$appSecret = "";
        		$result = $this->db->query("SELECT `secret` FROM `app` WHERE `key`='$appKey'");
        		while ($row = $this->db->fetchArray($result)) {
        			$appSecret = $row['secret'];
        		}
				
				$message = $_SERVER['REQUEST_METHOD'].$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				if ($_SERVER['REQUEST_METHOD'] == "POST" || $_SERVER['REQUEST_METHOD'] == "PUT") {
					$rawData = file_get_contents("php://input");
					$message = $message.$rawData;
				}

				$hash = hash_hmac("sha512", $message, $appSecret);

				if ($hash == $_SERVER['PHP_AUTH_PW']) {
					$appIsAuthenticated = true;
				}
        	}
        }

        return $appIsAuthenticated;
    }
	
	/*
	 * Get a token to prevent CSRF attacks.
	 */
	public function getToken($time) {
		$user = new User($this->db);
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
		$user = new User($this->db);
		$session = $user->getSession();
		$userID = $user->getID();
		$password = $user->getPassbyID($userID);
		$proof = md5($session.$userID.$password.$time);
		return ($token==$proof);
	}
	
}
?>