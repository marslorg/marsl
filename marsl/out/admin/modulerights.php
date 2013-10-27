<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class ModuleRights {
	
	/*
	 * The dialog to set up and change the module rights.
	 */
	public function admin() {
		$user = new User();
		$basic = new Basic();
		$role = new Role();
		$auth = new Authentication();
		if ($user->isAdmin()) {
			$db = new DB();
			$auth = new Authentication();
			if (!isset($_GET['action'])) {
				$modules = array();
				$result = $db->query("SELECT * FROM `module`");
				while ($row = mysql_fetch_array($result)) {
					if ($auth->moduleAdminAllowed($row['file'], $role->getRole())&&$auth->moduleExtendedAllowed($row['file'], $role->getRole())
					&&$auth->moduleWriteAllowed($row['file'], $role->getRole())&&$auth->moduleReadAllowed($row['file'], $role->getRole())) {
						array_push($modules,array('file'=>$row['file'],'name'=>$row['name']));
					}
				}
				require_once("template/modulerights.tpl.php");
			}
			else if ($_GET['action']=="role") {
				$module = $basic->getModule($_GET['module']);
				$moduleID = mysql_real_escape_string($module['file']);
				$name = htmlentities($module['name']);
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
							$roleID = mysql_real_escape_string($roleID);
							if ($db->isExisting("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID'")) {
								$result = $db->query("SELECT * FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID'");
								while ($row = mysql_fetch_array($result)) {
									$roleName = htmlentities($role->getNamebyID($row['role']));
									array_push($rights,array('name'=>$roleName,'role'=>htmlentities($row['role']),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
								}
							}
							else {
								$roleName = htmlentities($role->getNamebyID($roleID));
								array_push($rights,array('name'=>$roleName,'role'=>htmlentities($roleID),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
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