<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");

class Band {
	
	public function display() {
		
	}
	
	public function admin() {
		$role = new Role();
		$auth = new Authentication();
		if ($auth->moduleAdminAllowed("cbe", $role->getRole())) {
			$db = new DB();
			$bands = array();
			$result = $db->query("SELECT `id`, `tag` FROM `band` ORDER BY `tag` ASC");
			while ($row = mysql_fetch_array($result)) {
				$id = $row['id'];
				$tag = htmlentities($row['tag']);
				array_push($bands, array('id'=>$id, 'tag'=>$tag));
			}
			require_once("template/cbe.bands.tpl.php");
		}
	}
	
	public function edit($id) {
		
	}
	
}
?>