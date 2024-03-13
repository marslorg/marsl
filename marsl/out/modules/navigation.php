<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/../includes/slugify/vendor/autoload.php");

use Cocur\Slugify\Slugify;

class Navigation implements Module {
	
	private $db;
	private $auth;
	private $role;
	private $basic;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
		$this->basic = new Basic($db, $auth, $role);
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
				$result = $this->db->query("SELECT `id`, `name`, `pos`, `category`, `maps_to`, `type` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['id'], $curRole)) {
						if (empty($row['maps_to'])) {
							$roleEditor = $this->auth->locationAdminAllowed($row['id'], $curRole)&&$this->auth->locationExtendedAllowed($row['id'], $curRole)&&$this->auth->locationWriteAllowed($row['id'], $curRole)&&$this->auth->locationReadAllowed($row['id'], $curRole);
							$name = $this->basic->convertToHTMLEntities($row['name']);
							if ($row['type'] == 0) {
								array_push($categories, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'role' => $roleEditor));
							}
							else if ($row['type'] == 1) {
								array_push($catcontents, array('id' => $row['id'], 'name' => $name, 'pos' => $row['pos'], 'role' => $roleEditor));
							}
							else if ($row['type'] == 2) {
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
			$config = new Configuration();
			$categories = array();
			$links = array();
			$result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationReadAllowed($row['id'], $this->role->getRole())) {
					$id = $this->basic->convertToHTMLEntities($row['id']);
					$name = $this->basic->convertToHTMLEntities($row['name']);

					$link = "";

					if ($config->getEnableOldURIs()) {
						$link = "index.php?id=".$row['id'];
					}
					else {
						$link = $this->generateRestfulURI($row['id'], $row['name']);
					}
					
					if ($row['type'] == 0 || $row['type'] == 1) {
						array_push($categories, array('id' => $id, 'name' => $name, 'link' => $link, 'type' => $row['type']));
					}
					else if ($row['type'] == 2) {
						if (!array_key_exists($row['category'], $links)) {
							$links[$row['category']] = array();
						}
						array_push($links[$row['category']], array('id' => $id, 'name' => $name, 'link' => $link));
					}
				}
			}

			require("template/navigation.tpl.php");
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
					$name = $this->basic->convertToHTMLEntities($this->getNamebyID($id));
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
							if ($this->db->isExisting("SELECT `role` FROM `rights` WHERE `role`='$roleID' AND `location`='$id' LIMIT 1")) {
								$result = $this->db->query("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights` WHERE `role`='$roleID' AND `location`='$id'");
								while ($row = $this->db->fetchArray($result)) {
									$roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($row['role']));
									array_push($rights,array('name'=>$roleName,'role'=>$this->basic->convertToHTMLEntities($row['role']),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
								}
							}
							else {
								$roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($roleID));
								array_push($rights,array('name'=>$roleName,'role'=>$this->basic->convertToHTMLEntities($roleID),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
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
	
	public function displayTag() {
	}
	
	public function getImage() {
		return null;
	}
	
	public function getTitle() {
		return null;
	}

	public function getRestfulURIPartFromOldURL() {
		return null;
	}

	public function getOldURIPartFromRestfulURL() {
		return null;
	}

	public function generateRestfulURIByID($id) {
		list($title, $module) = $this->getModuleAndTitleByID($id);
		$uri = $this->generateRestfulURI($id, $title);
		return $uri;
	}

	public function generateRestfulURI($id, $title) {
		$slugify = new Slugify();
		return  $slugify->slugify($title)."-".$id;
	}

	public function getModuleAndTitleByID($id) {
		$id = $this->db->escapeString($id);
        $result = $this->db->query("SELECT `module`, `name` FROM `navigation` WHERE `id`='$id' AND `type` IN ('1','2')");
        while ($row = $this->db->fetchArray($result)) {
            $title = $row['name']." - ";
            $module = $this->db->escapeString($row['module']);
        }

        return array($title, $module);
    }

	public function getIDFromRestfulURI() {
		$id = -1;

		if (isset($_GET['request_uri']) && !empty($_GET['request_uri'])) {
			$requestURI = $_GET['request_uri'];
			$explodedRequestURI = explode('/', $requestURI);
			if (sizeof($explodedRequestURI) > 0) {
				$pagePart = $explodedRequestURI[0];
				$explodedPagePart = explode('-', $pagePart);
				$explodedPagePartSize = sizeof($explodedPagePart);
				if ($explodedPagePartSize > 0) {
					$id = $explodedPagePart[$explodedPagePartSize-1];
				}
			}
		}

		return $id;
	}

	public function getPageID() {
		$config = new Configuration();

		$id = -1;
		if (!$config->getEnableOldURIs()) {
			$id = $this->getIDFromRestfulURI();
		}

		if ($id == -1) {
			if ($config->getEnableOldURIs() && isset($_GET['id'])) {
				$id = $_GET['id'];
			}
			else if ($config->getEnableOldURIs() && !isset($_GET['id']) && isset($_GET['tag'])) {
				$id = -1;
			}
			else {
				$result = $this->db->query("SELECT `homepage` FROM homepage");
				while ($row = $this->db->fetchArray($result)) {
					$id = $row['homepage'];
				}
			}
		}

		$id = $this->db->escapeString($id);

		return $id;
	}

	public function getRelativeURI($id, $title, $withParameters) {
		$config = new Configuration();
		$uri = "";

		if ($config->getEnableOldURIs()) {
			$uri = "index.php?id=".$id;
			if ($withParameters) {
				$uri = $uri."&";
			}
		}
		else {
			if (isset($title) && !empty($title)) {
				$uri = $this->generateRestfulURI($id, $title);
			}
			else {
				$uri = $this->generateRestfulURIByID($id);
			}

			if ($withParameters) {
				$uri = $uri."?";
			}
		}

		return $uri;
	}
}

?>