<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");

class Administration {

	/*
	 * Loads the main admin page.
	 */
	public function admin() {
		$user = new User();
		if ($user->isAdmin()) {
			require_once("template/main.tpl.php");
		}
	}
	
}

?>