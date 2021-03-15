<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../user/role.php");

class Administration {

	/*
	 * Loads the main admin page.
	 */
	public function admin() {
		$db = new DB();
		$db->connect();
		$role = new Role($db);
		$user = new User($db, $role);
		if ($user->isAdmin()) {
			require_once("template/main.tpl.php");
		}
	}
	
}

?>