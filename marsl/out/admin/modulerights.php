<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class ModuleRights {
	
	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}

	/*
	 * The dialog to set up and change the module rights.
	 */
	public function admin() {
		$user = new User($this->db, $this->role);
		$basic = new Basic($this->db, $this->auth, $this->role);
		if ($user->isAdmin()) {
			if (!isset($_GET['action'])) {
				$modules = array();
				$result = $this->db->query("SELECT `file`, `name` FROM `module`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->moduleAdminAllowed($row['file'], $this->role->getRole())&&$this->auth->moduleExtendedAllowed($row['file'], $this->role->getRole())
					&&$this->auth->moduleWriteAllowed($row['file'], $this->role->getRole())&&$this->auth->moduleReadAllowed($row['file'], $this->role->getRole())) {
						array_push($modules,array('file'=>$row['file'],'name'=>$row['name']));
					}
				}
				require_once("template/modulerights.tpl.php");
			}
			else if ($_GET['action']=="role") {
				$module = $basic->getModule($_GET['module']);
				$moduleID = $this->db->escapeString($module['file']);
				$name = $basic->convertToHTMLEntities($module['name']);
				if ($this->auth->moduleAdminAllowed($moduleID, $this->role->getRole())&&$this->auth->moduleExtendedAllowed($moduleID, $this->role->getRole())
				&&$this->auth->moduleWriteAllowed($moduleID, $this->role->getRole())&&$this->auth->moduleReadAllowed($moduleID, $this->role->getRole())) {
					$roles = $this->role->getPossibleRoles($this->role->getRole());
					if (isset($_POST['change'])) {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							foreach($roles as $roleID) {
								if ($roleID!=$this->role->getRole()) {
									$read = isset($_POST[$roleID.'_read']);
									$write = isset($_POST[$roleID.'_write']);
									$extended = isset($_POST[$roleID.'_extended']);
									$admin = isset($_POST[$roleID.'_admin']);
									$this->role->setModuleRights($roleID,$moduleID,$read,$write,$extended,$admin);
								}
							}
						}
					}
					$rights = array();
					foreach ($roles as $roleID) {
						if ($roleID!=$this->role->getRole()) {
							$roleID = $this->db->escapeString($roleID);
							if ($this->db->isExisting("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID' LIMIT 1")) {
								$result = $this->db->query("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID'");
								while ($row = $this->db->fetchArray($result)) {
									$roleName = $basic->convertToHTMLEntities($this->role->getNamebyID($row['role']));
									array_push($rights,array('name'=>$roleName,'role'=>$basic->convertToHTMLEntities($row['role']),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
								}
							}
							else {
								$roleName = $basic->convertToHTMLEntities($this->role->getNamebyID($roleID));
								array_push($rights,array('name'=>$roleName,'role'=>$basic->convertToHTMLEntities($roleID),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
							}
						}
					}
					
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/modulerights.role.tpl.php");
			}
		}
	}
}
?>