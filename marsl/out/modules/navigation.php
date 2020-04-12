<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/module.php");

class Navigation implements Module {
	
	private $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	/*
	 * Displays the admin interface for the navigation.
	 */
	public function admin() {
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		if ($auth->moduleAdminAllowed("navigation", $role->getRole())) {
			$action = "";
			if (isset($_GET['action'])) {
				$action = $_GET['action'];
			}
			$this->evalAction($action);
			
			if ($action!="role") {
				$categories = array();			
				$result = $this->db->query("SELECT `id`, `name`, `pos`, `maps_to` FROM `navigation` WHERE `type`='0' ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
						if (empty($row['maps_to'])) {
							$roleEditor = $auth->locationAdminAllowed($row['id'], $role->getRole())&&$auth->locationExtendedAllowed($row['id'], $role->getRole())&&$auth->locationWriteAllowed($row['id'], $role->getRole())&&$auth->locationReadAllowed($row['id'], $role->getRole());
							$name = htmlentities($row['name'], null, "ISO-8859-1");
							array_push($categories, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'role' => $roleEditor));
						}
					}
				}
				
				$catcontents = array();
				$result = $this->db->query("SELECT `id`, `name`, `pos`, `maps_to` FROM `navigation` WHERE `type`='1' ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
						if (empty($row['maps_to'])) {
							$roleEditor = $auth->locationAdminAllowed($row['id'], $role->getRole())&&$auth->locationExtendedAllowed($row['id'], $role->getRole())&&$auth->locationWriteAllowed($row['id'], $role->getRole())&&$auth->locationReadAllowed($row['id'], $role->getRole());
							$name = htmlentities($row['name'], null, "ISO-8859-1");
							array_push($catcontents, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'role' => $roleEditor));
						}
					}
				}
				
				$links = array();
				$result = $this->db->query("SELECT `id`, `name`, `pos`, `category`, `maps_to` FROM `navigation` WHERE `type`='2' ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
						if (empty($row['maps_to'])) {
							$roleEditor = $auth->locationAdminAllowed($row['id'], $role->getRole())&&$auth->locationExtendedAllowed($row['id'], $role->getRole())&&$auth->locationWriteAllowed($row['id'], $role->getRole())&&$auth->locationReadAllowed($row['id'], $role->getRole());
							$name = htmlentities($row['name'], null, "ISO-8859-1");
							array_push($links, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'category' => $row['category'], 'role' => $roleEditor));
						}
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/navigation.tpl.php");
			}
		}
	}
	
	/*
	 * Displays the navigation.
	 */
	public function display() {
		
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		if ($auth->moduleReadAllowed("navigation", $role->getRole())) {
			$result = $this->db->query("SELECT `id`, `name`, `type` FROM `navigation` WHERE `type`='0' OR `type`='1' ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				
				if ($auth->locationReadAllowed($row['id'], $role->getRole())) {
					$cat_id = htmlentities($row['id'], null, "ISO-8859-1");
					$cat_name = htmlentities($row['name'], null, "ISO-8859-1");
					$cat_type = htmlentities($row['type'], null, "ISO-8859-1");
					
					$result_links = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `type`='2' AND `category`='$cat_id' ORDER BY `pos`");
					$links = array();
					while ($row_links = $this->db->fetchArray($result_links)) {
						if ($auth->locationReadAllowed($row_links['id'], $role->getRole())) {
							array_push($links, array('id' => htmlentities($row_links['id'], null, "ISO-8859-1"), 'name' => htmlentities($row_links['name'], null, "ISO-8859-1")));
						}
					}
					
					require("template/navigation.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Executes the given action in the admin interface.
	 */
	private function evalAction($action) {
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		if ($auth->moduleAdminAllowed("navigation", $role->getRole())) {
			$roleID = $role->getRole();
			if ($action=="addcat") {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','0','0')");
					$location = $this->db->lastInsertedID();
					$role->setRights($roleID, $location, '1', '1', '1', '1');
				}
			}
			else if ($action=="addcatcontent") {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','1','0')");
					$location = $this->db->lastInsertedID();
					$role->setRights($roleID, $location, '1', '1', '1', '1');
				}
			}
			else if ($action=="addlink") {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','2','0')");
					$location = $this->db->lastInsertedID();
					$role->setRights($roleID, $location, '1', '1', '1', '1');
				}
			}
			else if ($action=="change") {
				if (isset($_POST['id'])) {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$name = $this->db->escapeString($_POST['name']);
						$pos = $this->db->escapeString($_POST['pos']);
						$id = $this->db->escapeString($_POST['id']);
						if ($auth->locationAdminAllowed($id, $role->getRole())) {
							if ($_GET['type']==0||$_GET['type']==1) {
								$this->db->query("UPDATE `navigation` SET `name`='$name', `pos`='$pos' WHERE `id`='$id'");
							}
							elseif ($_GET['type']==2) {
								$catbelong = $this->db->escapeString($_POST['catbelong']);
								$this->db->query("UPDATE `navigation` SET `name`='$name', `pos`='$pos', `category`='$catbelong' WHERE `id`='$id'");
							}
						}
					}
				}
			}
			else if ($action=="del") {
				$id = $this->db->escapeString($_GET['id']);
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					if ($auth->locationAdminAllowed($id, $role->getRole())&&$auth->locationExtendedAllowed($id, $role->getRole())&&$auth->locationWriteAllowed($id, $role->getRole())&&$auth->locationReadAllowed($id, $role->getRole())) {
						$this->db->query("UPDATE `navigation` SET `type`='3' WHERE `id`='$id'");
					}
				}
			}
			
			else if ($action=="role") {
				$id = $this->db->escapeString($_GET['id']);
				if ($auth->locationAdminAllowed($id, $role->getRole())&&$auth->locationExtendedAllowed($id, $role->getRole())&&$auth->locationWriteAllowed($id, $role->getRole())&&$auth->locationReadAllowed($id, $role->getRole())) {
					$name = htmlentities($this->getNamebyID($id), null, "ISO-8859-1");
					$roles = $role->getPossibleRoles($role->getRole());
					if (isset($_POST['change'])) {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							foreach ($roles as $roleID) {
								if ($roleID!=$role->getRole()) {
									$read = isset($_POST[$roleID.'_read']);
									$write = isset($_POST[$roleID.'_write']);
									$extended = isset($_POST[$roleID.'_extended']);
									$admin = isset($_POST[$roleID.'_admin']);
									$role->setRights($roleID, $id, $read, $write, $extended, $admin);
								}
							}
						}
					}
					$rights = array();
					foreach ($roles as $roleID) {
						if ($roleID!=$role->getRole()) {
							$roleID = $this->db->escapeString($roleID);
							if ($this->db->isExisting("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$id'")) {
								$result = $this->db->query("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$id'");
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
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					require_once("template/navigation.role.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Gets the name of a link by the given ID.
	 */
	public function getNamebyID($id) {
		$id = $this->db->escapeString($id);
		$name = "";
		$result = $this->db->query("SELECT `name` FROM `navigation` WHERE `id`='$id'");
		while ($row = $this->db->fetchArray($result)) {
			$name = $row['name'];
		}
		return $name;
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
		return null;
	}
	
	public function getTitle() {
		return null;
	}
}

?>