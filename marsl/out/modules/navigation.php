<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/module.php");

class Navigation implements Module {
	
	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}
	
	/*
	 * Displays the admin interface for the navigation.
	 */
	public function admin() {
		$curRole = $this->role->getRole();
		if ($this->auth->moduleAdminAllowed("navigation", $curRole)) {
			$action = "";
			if (isset($_GET['action'])) {
				$action = $_GET['action'];
			}
			$this->evalAction($action);
			
			if ($action!="role") {
				$categories = array();
				$catcontents = array();	
				$links = array();	
				$result = $this->db->query("SELECT `id`, `name`, `pos`, `category`, `maps_to`, `type` FROM `navigation` WHERE `type`='0' OR `type`='1' OR `type`='2' ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['id'], $curRole)) {
						if (empty($row['maps_to'])) {
							$roleEditor = $this->auth->locationAdminAllowed($row['id'], $curRole)&&$this->auth->locationExtendedAllowed($row['id'], $curRole)&&$this->auth->locationWriteAllowed($row['id'], $curRole)&&$this->auth->locationReadAllowed($row['id'], $curRole);
							$name = htmlentities($row['name'], null, "ISO-8859-1");
							if ($row['type'] == 0) {
								array_push($categories, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'role' => $roleEditor));
							}
							if ($row['type'] == 1) {
								array_push($catcontents, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'role' => $roleEditor));
							}
							if ($row['type'] == 2) {
								array_push($links, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'category' => $row['category'], 'role' => $roleEditor));
							}
						}
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/navigation.tpl.php");
			}
		}
	}
	
	/*
	 * Displays the navigation.
	 */
	public function display() {
		if ($this->auth->moduleReadAllowed("navigation", $this->role->getRole())) {
			$result = $this->db->query("SELECT `id`, `name`, `type` FROM `navigation` WHERE `type`='0' OR `type`='1' ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				
				if ($this->auth->locationReadAllowed($row['id'], $this->role->getRole())) {
					$cat_id = htmlentities($row['id'], null, "ISO-8859-1");
					$cat_name = htmlentities($row['name'], null, "ISO-8859-1");
					$cat_type = htmlentities($row['type'], null, "ISO-8859-1");
					
					$result_links = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `type`='2' AND `category`='$cat_id' ORDER BY `pos`");
					$links = array();
					while ($row_links = $this->db->fetchArray($result_links)) {
						if ($this->auth->locationReadAllowed($row_links['id'], $this->role->getRole())) {
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
		if ($this->auth->moduleAdminAllowed("navigation", $this->role->getRole())) {
			$roleID = $this->role->getRole();
			if ($action=="addcat") {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','0','0')");
					$location = $this->db->lastInsertedID();
					$this->role->setRights($roleID, $location, '1', '1', '1', '1');
				}
			}
			else if ($action=="addcatcontent") {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','1','0')");
					$location = $this->db->lastInsertedID();
					$this->role->setRights($roleID, $location, '1', '1', '1', '1');
				}
			}
			else if ($action=="addlink") {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','2','0')");
					$location = $this->db->lastInsertedID();
					$this->role->setRights($roleID, $location, '1', '1', '1', '1');
				}
			}
			else if ($action=="change") {
				if (isset($_POST['id'])) {
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$name = $this->db->escapeString($_POST['name']);
						$pos = $this->db->escapeString($_POST['pos']);
						$id = $this->db->escapeString($_POST['id']);
						if ($this->auth->locationAdminAllowed($id, $this->role->getRole())) {
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
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					if ($this->auth->locationAdminAllowed($id, $this->role->getRole())&&$this->auth->locationExtendedAllowed($id, $this->role->getRole())&&$this->auth->locationWriteAllowed($id, $this->role->getRole())&&$this->auth->locationReadAllowed($id, $this->role->getRole())) {
						$this->db->query("UPDATE `navigation` SET `type`='3' WHERE `id`='$id'");
					}
				}
			}
			
			else if ($action=="role") {
				$id = $this->db->escapeString($_GET['id']);
				if ($this->auth->locationAdminAllowed($id, $this->role->getRole())&&$this->auth->locationExtendedAllowed($id, $this->role->getRole())&&$this->auth->locationWriteAllowed($id, $this->role->getRole())&&$this->auth->locationReadAllowed($id, $this->role->getRole())) {
					$name = htmlentities($this->getNamebyID($id), null, "ISO-8859-1");
					$roles = $this->role->getPossibleRoles($this->role->getRole());
					if (isset($_POST['change'])) {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							foreach ($roles as $roleID) {
								if ($roleID!=$this->role->getRole()) {
									$read = isset($_POST[$roleID.'_read']);
									$write = isset($_POST[$roleID.'_write']);
									$extended = isset($_POST[$roleID.'_extended']);
									$admin = isset($_POST[$roleID.'_admin']);
									$this->role->setRights($roleID, $id, $read, $write, $extended, $admin);
								}
							}
						}
					}
					$rights = array();
					foreach ($roles as $roleID) {
						if ($roleID!=$this->role->getRole()) {
							$roleID = $this->db->escapeString($roleID);
							if ($this->db->isExisting("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$id' LIMIT 1")) {
								$result = $this->db->query("SELECT * FROM `rights` WHERE `role`='$roleID' AND `location`='$id'");
								while ($row = $this->db->fetchArray($result)) {
									$roleName = htmlentities($this->role->getNamebyID($row['role']), null, "ISO-8859-1");
									array_push($rights,array('name'=>$roleName,'role'=>htmlentities($row['role'], null, "ISO-8859-1"),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
								}
							}
							else {
								$roleName = htmlentities($this->role->getNamebyID($roleID), null, "ISO-8859-1");
								array_push($rights,array('name'=>$roleName,'role'=>htmlentities($roleID, null, "ISO-8859-1"),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
							}
						}
					}
					$authTime = time();
					$authToken = $this->auth->getToken($authTime);
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