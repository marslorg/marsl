<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/module.php");

class URLLoader implements Module {
	
	/*
	 * Shows the navigation in the admin backend.
	 */
	public function adminNavi() {
		$auth = new Authentication();
		$role = new Role();
		if ($auth->moduleWriteAllowed("urlloader", $role->getRole())) {
			$db = new DB();
			$result = $db->query("SELECT `id`, `name`, `type` FROM `navigation` WHERE `type`='0' OR `type`='1' ORDER BY `pos`");
			while ($row = mysql_fetch_array($result)) {
				
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
					$cat_id = htmlentities($row['id']);
					$cat_name = htmlentities($row['name']);
					$cat_type = htmlentities($row['type']);
					$cat_id = mysql_real_escape_string($cat_id);
					$result_links = $db->query("SELECT `id`, `name` FROM `navigation` WHERE `type`='2' AND `category`='$cat_id'");
					$links = array();
					while ($row_links = mysql_fetch_array($result_links)) {
						if ($auth->locationReadAllowed($row_links['id'], $role->getRole())) {
							array_push($links, array('id' => htmlentities($row_links['id']), 'name' => htmlentities($row_links['name'])));
						}
					}
					
					require("template/urlloader.navigation.tpl.php");
				}
			}
		}
	}
	
	/*
	 * The interface to change the standard page.
	 */
	public function admin() {
		if ($_GET['var']=="urlloader") {
			$this->contentAdmin();
		}
		else {
			$db = new DB();
			$user = new User();
			$auth = new Authentication();
			$role = new Role();
			if (($auth->moduleAdminAllowed("urlloader", $role->getRole()))&&($user->isHead())) {
				if (isset($_POST['action'])) {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$homepage = $_POST['homepage'];
						$homepage = mysql_real_escape_string($homepage);
						if ($db->isExisting("SELECT * FROM `homepage`")) {
							$db->query("UPDATE `homepage` SET `homepage`='$homepage'");
						}
						else {
							$db->query("INSERT INTO `homepage`(`homepage`) VALUES('$homepage')");
						}
					}
				}
				$homepage = "";
				$result = $db->query("SELECT * FROM `homepage`");
				while ($row = mysql_fetch_array($result)) {
					$homepage = $row['homepage'];
				}
				$locations = array();
				$result = $db->query("SELECT * FROM `navigation` WHERE `type`='1' OR `type`='2'");
				while ($row = mysql_fetch_array($result)) {
					$name = htmlentities($row['name']);
					array_push($locations,array('name'=>$name,'id'=>$row['id']));
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/urlloader.tpl.php");
			}
		}
	}
	
	/*
	 * Updates a location with the submitted content.
	 */
	private function updateLocation() {
		$basic = new Basic();
		$db = new DB();
		$id = mysql_real_escape_string($_GET['id']);
		$head = mysql_real_escape_string($basic->cleanHTML($_POST['head']));
		$module = mysql_real_escape_string($basic->cleanHTML($_POST['module']));
		$foot = mysql_real_escape_string($basic->cleanHTML($_POST['foot']));
		$db->query("UPDATE `navigation` SET `head`='$head', `module`='$module', `foot`='$foot' WHERE `id`='$id'");
		$db = new DB();
	}
	
	/*
	 * Interface to change the content of a location.
	 */
	private function contentAdmin() {
		$auth = new Authentication();
		$role = new Role();
		if ($auth->moduleWriteAllowed("urlloader", $role->getRole())) {
			if ($auth->locationAdminAllowed($_GET['id'], $role->getRole())) {
				if (isset($_POST['action'])) {
					if ($_POST['action']=="update") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$this->updateLocation();
						}
					}
				}
				$basic = new Basic();
				$modules = $basic->getModules();
				$db = new DB();
				$id = mysql_real_escape_string($_GET['id']);
				$head = "";
				$module = "";
				$foot = "";
				$result = $db->query("SELECT `head`,`module`,`foot` FROM `navigation` WHERE `id`='$id'");
				while ($row = mysql_fetch_array($result)) {
					$head = $row['head'];
					$proof = $row['module'];
					$foot = $row['foot'];
				}
				$navi = new Navigation();
				$name = htmlentities($navi->getNamebyID($_GET['id']));
				$id = htmlentities($_GET['id']);
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/urlloader.content.tpl.php");
			}
		}
	}
	
	/*
	 * Loads the content of a location into the frontend and starts the display-function of a module.
	 */
	public function display() {
		$auth = new Authentication();
		$db = new DB();
		$id = -1;
		$role = new Role();
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
		}
		else {
			$result = $db->query("SELECT `homepage` FROM homepage");
			while ($row = mysql_fetch_array($result)) {
				$id = $row['homepage'];
			}
		}
		if (isset($_GET['search'])) {
			$searchQuery = mysql_real_escape_string($_GET['search']);
			$type = "standard";
			if (isset($_GET['type'])) {
				$type = $_GET['type'];
			}
			if (isset($_GET['context'])) {
				$searchContext = $_GET['context'];
				if ($auth->moduleReadAllowed($searchContext, $role->getRole())) {
					include_once(dirname(__FILE__)."/".$searchContext.".php");
					$module = new $searchContext;
					if ($module->isSearchable()) {
						$module->search($searchQuery, $type);
					}
				}
			}
			else {
				//Implement a standard search, if possible over the standard search methods of each module.
			}
		}
		else {
			$id = mysql_real_escape_string($id);
			$result = $db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
			while ($row = mysql_fetch_array($result)) {
				$id = mysql_real_escape_string($row['maps_to']);
			}
			
			if ($auth->locationReadAllowed($id, $role->getRole())) {
				$result = $db->query("SELECT `head`, `foot`, `module` FROM `navigation` WHERE `id`='$id' AND (`type`='1' OR `type`='2')");
				while ($row = mysql_fetch_array($result)) {
					$head = $row['head'];
					$foot = $row['foot'];
					$module = mysql_real_escape_string($row['module']);
					$result2 = $db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
					echo $head;
					while ($row2 = mysql_fetch_array($result2)) {
						include_once(dirname(__FILE__)."/".$module.".php");
						$content = new $row2['class'];
						$content->display();
					}
					echo $foot;
				}
			}
		}
	}
	
	/*
	 * Interface method stub.
	*/
	public function isSearchable() {
		return false;
	}
	
	/*
	 * Interface method stub.
	*/
	public function getSearchList() {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function search($query, $type) {
		return null;
	}
}
?>