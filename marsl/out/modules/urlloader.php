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
		$basic = new Basic($this->db, $this->auth, $this->role);
		$curRole = $this->role->getRole();
		if ($this->auth->moduleWriteAllowed("urlloader", $curRole)) {
			$categories = array();
			$categoryLinks = array();
			$result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {

				if ($row['type'] == '2' || (($row['type'] == '0' || $row['type'] == '1') && $this->auth->locationAdminAllowed($row['id'], $curRole))) {
					if ($row['type'] == '2' && $this->auth->locationReadAllowed($row['id'], $curRole)) {
						$catID = $row['category'];
						$linkID = $basic->convertToHTMLEntities($row['id']);
						$linkName = $basic->convertToHTMLEntities($row['name']);
						if (!array_key_exists($catID, $categoryLinks)) {
							$categoryLinks[$catID] = array();
						}
						array_push($categoryLinks[$catID], array('id' => $linkID, 'name' => $linkName));
					}
					else {
						$catID = $basic->convertToHTMLEntities($row['id']);
						$catName = $basic->convertToHTMLEntities($row['name']);
						$catType = $basic->convertToHTMLEntities($row['type']);
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
		$basic = new Basic($this->db, $this->auth, $this->role);
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
						if ($this->db->isExisting("SELECT `homepage` FROM `homepage` LIMIT 1")) {
							$this->db->query("UPDATE `homepage` SET `homepage`='$homepage'");
						}
						else {
							$this->db->query("INSERT INTO `homepage`(`homepage`) VALUES('$homepage')");
						}
					}
				}
				$homepage = "";
				$result = $this->db->query("SELECT `homepage` FROM `homepage`");
				while ($row = $this->db->fetchArray($result)) {
					$homepage = $row['homepage'];
				}
				$locations = array();
				$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `type` IN ('1','2')");
				while ($row = $this->db->fetchArray($result)) {
					$name = $basic->convertToHTMLEntities($row['name']);
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
				$name = $basic->convertToHTMLEntities($navi->getNamebyID($_GET['id']));
				$id = $basic->convertToHTMLEntities($_GET['id']);
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
		$basic = new Basic($this->db, $this->auth, $this->role);

		$navi = new Navigation($this->db, $this->auth, $this->role);

		$id = $navi->getPageID();

		if ((isset($_GET['search']))) {
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
		else if ($this->isTagURL() && ($id == -1 || $id == "tag")) {
			$scope = $this->getScope();
			if (isset($scope)) {
				$tagScope = explode("_", $scope);
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
						$module->displayTag();
					}
				}
			}
		}
		else {
			$id = $this->db->escapeString($id);
			$id = $this->getNavigationMappingByID($id);
			
			if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
				$result = $this->db->query("SELECT `head`, `foot`, `module` FROM `navigation` WHERE `id`='$id' AND `type` IN ('1','2')");
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

	private function isTagURL() {
		$isRestfulTag = false;
		if (isset($_GET['request_uri']) && !empty($_GET['request_uri'])) {
			$requestURI = $_GET['request_uri'];
			$explodedRequestURI = explode('/', $requestURI);
			if (sizeof($explodedRequestURI) > 0) {
				$uriFirstPart = $explodedRequestURI[0];
				$isRestfulTag = $uriFirstPart == "tag";
			}
		}
		return isset($_GET['tag']) || $isRestfulTag;
	}

	private function getScope() {
		$scope = null;
		if (isset($_GET['scope'])) {
			$scope = $_GET['scope'];
		}
		else if (isset($_GET['request_uri']) && !empty($_GET['request_uri'])) {
			$requestURI = $_GET['request_uri'];
			$explodedRequestURI = explode('/', $requestURI);
			if (sizeof($explodedRequestURI) > 1) {
				$scope = $explodedRequestURI[1];
			}
		}
		return $scope;
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
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$id = $navi->getPageID();
		$id = $this->db->escapeString($id);
		$id = $this->getNavigationMappingByID($id);
			
		if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
			$result = $this->db->query("SELECT `module` FROM `navigation` WHERE `id`='$id' AND `type` IN ('1','2')");
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
		$basic = new Basic($this->db, $this->auth, $this->role);
		$title = "";
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$id = $navi->getPageID();
		$id = $this->db->escapeString($id);
		if ($id==$basic->getHomeLocation()) {
			return null;
		}
		else {
   			$id = $this->getNavigationMappingByID($id);
				
			if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
    				list($title, $module) = $navi->getModuleAndTitleByID($id);
					$result = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
					while ($row = $this->db->fetchArray($result)) {
						include_once(dirname(__FILE__)."/".$module.".php");
						$content = new $row['class']($this->db, $this->auth, $this->role);
						$newTitle = $content->getTitle();
						if ($newTitle!=null) {
							$title = $newTitle." - ";
						}
					}
			}
			return $title;
		}
	}

	public function getRestfulURIPartFromOldURL() {
		return null;
	}

	public function getOldURIPartFromRestfulURL() {
		return null;
	}

    private function getNavigationMappingByID($id) {
        $newID = $this->db->escapeString($id);
		$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
        while ($row = $this->db->fetchArray($result)) {
        	$newID = $this->db->escapeString($row['maps_to']);
        }

        return $newID;
    }

	public function getRedirectURI() {
		$config = new Configuration();
		$uri = "";
		if ($config->getEnableOldURIs()) {
		   $uri = $this->getOldURI();
		}
		else {
			$uri = $this->getRestfulURI();
		}
		return $uri;
	}

	private function getRestfulURI() {
		$uri = "";
		if (isset($_GET['id'])) {
   			$uri = $this->getRestfulURIForStandardPages();
		}
		else if (isset($_GET['tag'])) {
			$uri = $this->getRestfulURIForTagPage();
		}
		return $uri;
	}

	private function getRestfulURIForTagPage() {
		$uri = "";
		if (isset($_GET['scope'])) {
			$explodedScope = explode("_", $_GET['scope']);
			$module = $explodedScope[0];
			include_once(dirname(__FILE__)."/".$module.".php");
			$moduleClass = new $module($this->db, $this->auth, $this->role);
			$uri = "tag".$moduleClass->getRestfulURIPartFromOldURL();
		}
		return $uri;
	}

    private function getRestfulURIForStandardPages() {
		$uri = "";
        $id = $_GET['id'];
        $id = $this->db->escapeString($id);
        $id = $this->getNavigationMappingByID($id);
        if ($this->auth->locationReadAllowed($id, $this->role->getRole())) {
        	$navi = new Navigation($this->db, $this->auth, $this->role);
        	$uri = $navi->generateRestfulURIByID($id);
        	list($title, $module) = $navi->getModuleAndTitleByID($id);
        	if (isset($module) && !empty($module)) {
        		$result = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
        		while ($row = $this->db->fetchArray($result)) {
        			include_once(dirname(__FILE__)."/".$module.".php");
        			$moduleClass = new $row['class']($this->db, $this->auth, $this->role);
        			$uri = $uri.$moduleClass->getRestfulURIPartFromOldURL();
        		}
        	}
        }

        return $uri;
    }

    private function getOldURI() {
		$uri = "";

		$firstURIPart = "";
		$requestUri = $_GET['request_uri'];
        $explodedRequestUri = explode('/', $requestUri);
		if (sizeof($explodedRequestUri) > 1) {
			$firstURIPart = $explodedRequestUri[0];
		}
		
		if ($firstURIPart == "tag") {
			$uri = $this->getOldURIForTagPage();
		}
		else {
   			$uri = $this->getOldURIForStandardPages();
		}

        return $uri;
    }

	private function getOldURIForTagPage() {
		$uri = "";
		$requestUri = $_GET['request_uri'];
		$explodedRequestURI = explode('/', $requestUri);
		if (sizeof($explodedRequestURI) > 2) {
			$scope = $explodedRequestURI[1];
			$explodedScope = explode("_", $scope);
			$module = $explodedScope[0];
			include_once(dirname(__FILE__)."/".$module.".php");
			$moduleClass = new $module($this->db, $this->auth, $this->role);
			$uri = $moduleClass->getOldURIPartFromRestfulURL();
		}
		return $uri;
	}

    private function getOldURIForStandardPages() {
		$uri = "";
		$navi = new Navigation($this->db, $this->auth, $this->role);
        $id = $navi->getIDFromRestfulURI();
        $uri = "index.php?id=".$id;
		list($title, $module) = $navi->getModuleAndTitleByID($id);
		if (isset($module) && !empty($module)) {
			$result = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
			while ($row = $this->db->fetchArray($result)) {
				include_once(dirname(__FILE__)."/".$module.".php");
				$moduleClass = new $row['class']($this->db, $this->auth, $this->role);
				$uri = $uri.$moduleClass->getOldURIPartFromRestfulURL();
			}
		}

        return $uri;
    }

	public function redirect() {
		header("HTTP/1.1 301 Moved Permanently");
		$config = new Configuration();
		$redirectURL = $config->getDomain().$config->getBasePath()."/".$this->getRedirectURI();
		header("Location: ".$redirectURL);
		exit();
	}

	public function shouldRedirect() {
		$result = false;
		if ((isset($_GET['request_uri']) && !empty($_GET['request_uri'])) || isset($_GET['id']) || isset($_GET['tag'])) {
			$config = new Configuration();
			if ($config->getEnableOldURIs()) {
				if (isset($_GET['request_uri'])
				&& !empty($_GET['request_uri'])
				&& (!isset($_GET['id']) || !isset($_GET['tag']))) {
					$result = true;
				}
			}
			else {
				if ((!isset($_GET['request_uri'])
				|| (isset($_GET['request_uri'])
				&& $_GET['request_uri'] == "index.php"))
				&& (isset($_GET['id']) || isset($_GET['tag']))) {
					$result = true;
				}
			}
		}

		return $result;
	}
}
?>