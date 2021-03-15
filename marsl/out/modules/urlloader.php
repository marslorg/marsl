<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/module.php");

class URLLoader implements Module {
	
	private $db;
	private $auth;
	private $role;
	
	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}
	
	/*
	 * Shows the navigation in the admin backend.
	 */
	public function adminNavi() {
		$curRole = $this->role->getRole();
		if ($this->auth->moduleWriteAllowed("urlloader", $curRole)) {
			$categories = array();
			$categoryLinks = array();
			$result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type`='0' OR `type`='1' OR `type`='2' ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {

				if ($row['type'] == '2' || (($row['type'] == '0' || $row['type'] == '1') && $this->auth->locationAdminAllowed($row['id'], $curRole))) {
					if ($row['type'] == '2' && $this->auth->locationReadAllowed($row['id'], $curRole)) {
						$catID = $row['category'];
						$linkID = htmlentities($row['id'], null, "ISO-8859-1");
						$linkName = htmlentities($row['name'], null, "ISO-8859-1");
						if (!array_key_exists($catID, $categoryLinks)) {
							$categoryLinks[$catID] = array();
						}
						array_push($categoryLinks[$catID], array('id' => $linkID, 'name' => $linkName));
					}
					else {
						$catID = htmlentities($row['id'], null, "ISO-8859-1");
						$catName = htmlentities($row['name'], null, "ISO-8859-1");
						$catType = htmlentities($row['type'], null, "ISO-8859-1");
						array_push($categories, array('id' => $catID, 'name' => $catName, 'type' => $catType));
					}
				}
			}

			require("template/urlloader.navigation.tpl.php");
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
			$user = new User($this->db, $this->role);
			if (($this->auth->moduleAdminAllowed("urlloader", $this->role->getRole()))&&($user->isHead())) {
				if (isset($_POST['action'])) {
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$homepage = $_POST['homepage'];
						$homepage = $this->db->escapeString($homepage);
						if ($this->db->isExisting("SELECT * FROM `homepage` LIMIT 1")) {
							$this->db->query("UPDATE `homepage` SET `homepage`='$homepage'");
						}
						else {
							$this->db->query("INSERT INTO `homepage`(`homepage`) VALUES('$homepage')");
						}
					}
				}
				$homepage = "";
				$result = $this->db->query("SELECT * FROM `homepage`");
				while ($row = $this->db->fetchArray($result)) {
					$homepage = $row['homepage'];
				}
				$locations = array();
				$result = $this->db->query("SELECT * FROM `navigation` WHERE `type`='1' OR `type`='2'");
				while ($row = $this->db->fetchArray($result)) {
					$name = htmlentities($row['name'], null, "ISO-8859-1");
					array_push($locations,array('name'=>$name,'id'=>$row['id']));
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/urlloader.tpl.php");
			}
		}
	}
	
	/*
	 * Updates a location with the submitted content.
	 */
	private function updateLocation() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$id = $this->db->escapeString($_GET['id']);
		$head = $this->db->escapeString($basic->cleanHTML($_POST['head']));
		$module = $this->db->escapeString($basic->cleanHTML($_POST['module']));
		$foot = $this->db->escapeString($basic->cleanHTML($_POST['foot']));
		$this->db->query("UPDATE `navigation` SET `head`='$head', `module`='$module', `foot`='$foot' WHERE `id`='$id'");
	}
	
	/*
	 * Interface to change the content of a location.
	 */
	private function contentAdmin() {
		if ($this->auth->moduleWriteAllowed("urlloader", $this->role->getRole())) {
			if ($this->auth->locationAdminAllowed($_GET['id'], $this->role->getRole())) {
				if (isset($_POST['action'])) {
					if ($_POST['action']=="update") {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$this->updateLocation();
						}
					}
				}
				$basic = new Basic($this->db, $this->auth, $this->role);
				$modules = $basic->getModules();
				$id = $this->db->escapeString($_GET['id']);
				$head = "";
				$module = "";
				$foot = "";
				$result = $this->db->query("SELECT `head`,`module`,`foot` FROM `navigation` WHERE `id`='$id'");
				while ($row = $this->db->fetchArray($result)) {
					$head = $row['head'];
					$proof = $row['module'];
					$foot = $row['foot'];
				}
				$navi = new Navigation($this->db, $this->auth, $this->role);
				$name = htmlentities($navi->getNamebyID($_GET['id']), null, "ISO-8859-1");
				$id = htmlentities($_GET['id'], null, "UTF-8");
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/urlloader.content.tpl.php");
			}
		}
	}
	
	/*
	 * Loads the content of a location into the frontend and starts the display-function of a module.
	 */
	public function display() {
		$id = -1;
		$basic = new Basic($this->db, $this->auth, $this->role);
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
		}
		else {
			$result = $this->db->query("SELECT `homepage` FROM homepage");
			while ($row = $this->db->fetchArray($result)) {
				$id = $row['homepage'];
			}
		}
		if ((isset($_GET['search']))&&(!isset($_GET['id']))) {
			$searchQuery = $this->db->escapeString($_GET['search']);
			$type = "standard";
			if (isset($_GET['scope'])) {
				$searchScope = explode("_",$_GET['scope']);
				$searchContext = $searchScope[0];
				$type = $searchScope[1];
				if ($this->auth->moduleReadAllowed($searchContext, $this->role->getRole())) {
					include_once(dirname(__FILE__)."/".$searchContext.".php");
					$moduleInfo = $basic->getModule($searchContext);
					$module = new $moduleInfo['class']($this->db, $this->auth, $this->role);
					if ($module->isSearchable()) {
						$module->search($searchQuery, $type);
					}
				}
			}
			else {
				//Implement a standard search, if possible over the standard search methods of each module.
			}
		}
		else if ((isset($_GET['tag']))&&(!isset($_GET['id']))) {
			$tagID = $this->db->escapeString($_GET['tag']);
			if (isset($_GET['scope'])) {
				$tagScope = explode("_", $_GET['scope']);
				$tagContext = $tagScope[0];
				if ($tagContext == "general") {
					$tagContext = "news";
				}
				$type = $tagScope[1];
				if ($this->auth->moduleReadAllowed($tagContext, $this->role->getRole())) {
					include_once(dirname(__FILE__)."/".$tagContext.".php");
					$moduleInfo = $basic->getModule($tagContext);
					$module = new $moduleInfo['class']($this->db, $this->auth, $this->role);
					if ($module->isTaggable()) {
						$module->displayTag($tagID, $type);
					}
				}
			}
		}
		else {
			$id = $this->db->escapeString($id);
			$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
			while ($row = $this->db->fetchArray($result)) {
				$id = $this->db->escapeString($row['maps_to']);
			}
			
			if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
				$result = $this->db->query("SELECT `head`, `foot`, `module` FROM `navigation` WHERE `id`='$id' AND (`type`='1' OR `type`='2')");
				while ($row = $this->db->fetchArray($result)) {
					$head = $row['head'];
					$foot = $row['foot'];
					$module = $this->db->escapeString($row['module']);
					$result2 = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
					echo $head;
					while ($row2 = $this->db->fetchArray($result2)) {
						include_once(dirname(__FILE__)."/".$module.".php");
						$content = new $row2['class']($this->db, $this->auth, $this->role);
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
		return array();
	}
	
	/*
	 * Interface method stub.
	*/
	public function search($query, $type) {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function isTaggable() {
		return false;
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagList() {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function addTags($tagString, $type, $news) {
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagString($type, $news) {
	}
	
	public function getTags($type, $news) {
		return null;
	}
	
	public function displayTag($tagID, $type) {
	}
	
	public function getImage() {
		$id = -1;
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
		}
		else {
			$result = $this->db->query("SELECT `homepage` FROM homepage");
			while ($row = $this->db->fetchArray($result)) {
				$id = $row['homepage'];
			}
		}
		$id = $this->db->escapeString($id);
		$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
		while ($row = $this->db->fetchArray($result)) {
			$id = $this->db->escapeString($row['maps_to']);
		}
			
		if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
			$result = $this->db->query("SELECT `module` FROM `navigation` WHERE `id`='$id' AND (`type`='1' OR `type`='2')");
			while ($row = $this->db->fetchArray($result)) {
				$module = $this->db->escapeString($row['module']);
				$result2 = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
				while ($row2 = $this->db->fetchArray($result2)) {
					include_once(dirname(__FILE__)."/".$module.".php");
					$content = new $row2['class']($this->db, $this->auth, $this->role);
					return $content->getImage();
				}
			}
		}
		return null;
	}
	
	public function getTitle() {
		$id = -1;
		$basic = new Basic($this->db, $this->auth, $this->role);
		$title = "";
		if (isset($_GET['id'])) {
			$id = $_GET['id'];
		}
		else {
			$result = $this->db->query("SELECT `homepage` FROM homepage");
			while ($row = $this->db->fetchArray($result)) {
				$id = $row['homepage'];
			}
		}
		$id = $this->db->escapeString($id);
		if ($id==$basic->getHomeLocation()) {
			return null;
		}
		else {
			$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
			while ($row = $this->db->fetchArray($result)) {
				$id = $this->db->escapeString($row['maps_to']);
			}
				
			if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
				$result = $this->db->query("SELECT `module`, `name` FROM `navigation` WHERE `id`='$id' AND (`type`='1' OR `type`='2')");
				while ($row = $this->db->fetchArray($result)) {
					$title = $row['name']." - ";
					$module = $this->db->escapeString($row['module']);
					$result2 = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
					while ($row2 = $this->db->fetchArray($result2)) {
						include_once(dirname(__FILE__)."/".$module.".php");
						$content = new $row2['class']($this->db, $this->auth, $this->role);
						$newTitle = $content->getTitle();
						if ($newTitle!=null) {
							$title = $newTitle." - ";
						}
					}
				}
			}
			return $title;
		}
	}
}
?>