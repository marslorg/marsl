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

	public function __construct($db) {
		$this->db = $db;
	}

	/*
	 * The dialog to set up and change the module rights.
	 */
	public function admin() {
		$user = new User($this->db);
		$basic = new Basic($this->db);
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		if ($user->isAdmin()) {
			if (!isset($_GET['action'])) {
				$modules = array();
				$result = $this->db->query("SELECT * FROM `module`");
				while ($row = $this->db->fetchArray($result)) {
					if ($auth->moduleAdminAllowed($row['file'], $role->getRole())&&$auth->moduleExtendedAllowed($row['file'], $role->getRole())
					&&$auth->moduleWriteAllowed($row['file'], $role->getRole())&&$auth->moduleReadAllowed($row['file'], $role->getRole())) {
						array_push($modules,array('file'=>$row['file'],'name'=>$row['name']));
					}
				}
				require_once("template/modulerights.tpl.php");
			}
			else if ($_GET['action']=="role") {
				$module = $basic->getModule($_GET['module']);
				$moduleID = $this->db->escapeString($module['file']);
				$name = htmlentities($module['name'], null, "ISO-8859-1");
				if ($auth->moduleAdminAllowed($moduleID, $role->getRole())&&$auth->moduleExtendedAllowed($moduleID, $role->getRole())
				&&$auth->moduleWriteAllowed($moduleID, $role->getRole())&&$auth->moduleReadAllowed($moduleID, $role->getRole())) {
					$roles = $role->getPossibleRoles($role->getRole());
					if (isset($_POST['change'])) {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							foreach($roles as $roleID) {
								if ($roleID!=$role->getRole()) {
									$read = isset($_POST[$roleID.'_read']);
									$write = isset($_POST[$roleID.'_write']);
									$extended = isset($_POST[$roleID.'_extended']);
									$admin = isset($_POST[$roleID.'_admin']);
									$role->setModuleRights($roleID,$moduleID,$read,$write,$extended,$admin);
								}
							}
						}
					}
					$rights = array();
					foreach ($roles as $roleID) {
						if ($roleID!=$role->getRole()) {
							$roleID = $this->db->escapeString($roleID);
							if ($this->db->isExisting("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID'")) {
								$result = $this->db->query("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID'");
								while ($row = $this->db->fetchArray($result)) {
									$roleName = htmlentities($role->getNamebyID($row['role']), null, "ISO-8859-1");
									array_push($rights,array('name'=>$roleName,'role'=>htmlentities($row['role'], null, "ISO-8859-1"),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
								}
							}
							else {
								$roleName = htmlentities($role->getNamebyID($roleID), null, "ISO-8859-1");
								array_push($rights,array('name'=>$roleName,'role'=>htmlentities($roleID, null, "ISO-8859-1"),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
							}
						}
					}
					
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/modulerights.role.tpl.php");
			}
		}
	}
}
?>