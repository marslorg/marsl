<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/module.php");

class Gallery implements Module {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	/*
	 * Administrator interface for the gallery.
	 */
	public function admin() {
		$user = new User($this->db);
		$role = new Role($this->db);
		if ($user->isAdmin()) {
			$auth = new Authentication($this->db);
			$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
			$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
			if ($moduleAdmin) {
				require_once("template/gallery.navigation.tpl.php");
				if (isset($_GET['action'])) {
					if ($_GET['action']=="ftp") {
						if ($moduleExtended) {
							$this->ftp();
						}
					}
					if ($_GET['action']=="queue") {
						if ($moduleExtended) {
							$this->doThings();
							$this->queue();
						}
					}
					if ($_GET['action']=="edit") {
						if ($moduleExtended) {
							$this->edit();
						}
					}
					if ($_GET['action']=="details") {
						$this->details();
					}
					
					if ($_GET['action']=="albums") {
						if ($moduleExtended) {
							$this->doThings();
						}
						$this->albums();
					}
					
					if ($_GET['action']=="add") {
						if ($moduleExtended) {
							$this->addPhoto();
						}
					}
					
				}
				else {
					$this->upload();
				}
			}
		}
	}
	
	/*
	 * Shows the albums which are note deleted and released to the frontend.
	 * Will be called from the admin interface.
	 */
	private function albums() {
		$user = new User($this->db);
		$role = new Role($this->db);
		$navigation = new Navigation($this->db);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($user->isAdmin()) {
			$auth = new Authentication($this->db);
			$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
			$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
			if ($moduleAdmin) {
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$result = $this->db->query("SELECT COUNT(*) AS rowcount FROM `album` WHERE `visible`='1' AND `deleted`='0'");
				$pages = $this->db->getRowCount($result)/10;
				$start = $page*10-10;
				$end = 10;
				$galleries = array();
				$start = $this->db->escapeString($start);
				$result = $this->db->query("SELECT * FROM `album` WHERE `visible`='1' AND `deleted`='0' ORDER BY `album` DESC LIMIT $start,$end");
				while ($row = $this->db->fetchArray($result)) {
					$id = htmlentities($row['album'], null, "UTF-8");
					$author = $row['author'];
					$authorName = htmlentities($user->getAcronymbyID($author), null, "UTF-8");
					$photograph = htmlentities($row['photograph'], null, "UTF-8");
					$authorIP = htmlentities($row['author_ip'], null, "UTF-8");
					$location = htmlentities($navigation->getNamebyID($row['location']), null, "UTF-8");
					$locationAdmin = $auth->locationAdminAllowed($row['location'], $role->getRole());
					$editLink = ($moduleExtended&&$locationAdmin);
					$description = $row['description'];
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$dateTime->setTimestamp($row['postdate']);
					$postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
					array_push($galleries, array('photograph'=>$photograph, 'album'=>$id, 'authorIP'=>$authorIP, 'author'=>$authorName, 'location'=>$location, 'description'=>$description, 'date'=>$date, 'postdate'=>$postdate, 'editLink'=>$editLink));
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/gallery.tpl.php");
			}
		}
	}
	
	/*
	 * Allows and administrator to have a look at the album details and change them.
	 * E.g. deleting uploaded pictures.
	 */
	private function details() {
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$album = $this->db->escapeString($_GET['id']);
		$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
		$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
		$locationRead = false;
		$locationAdmin = false;
		$result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$album'");
		while ($row = $this->db->fetchArray($result)) {
			$locationRead = $auth->locationReadAllowed($row['location'], $role->getRole());
			$locationAdmin = $auth->locationAdminAllowed($row['location'], $role->getRole());
		}
		
		if (isset($_POST['action'])&&$auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
			if ($_POST['action']=="send") {
				if ($moduleExtended&&$moduleAdmin&&$locationAdmin) {
					$result = $this->db->query("SELECT * FROM `picture` WHERE `album`='$album' AND `deleted`='0'");
					while ($row = $this->db->fetchArray($result)) {
						$picture = $this->db->escapeString($row['picture']);
						if (isset($_POST[$picture.'_delete'])) {
							$this->db->query("UPDATE `picture` SET `deleted`='1' WHERE `picture`='$picture'");
						}
						if (isset($_POST[$picture.'_submit'])) {
							$this->db->query("UPDATE `picture` SET `visible`='1' WHERE `picture`='$picture'");
						}
					}
				}
			}
		}
		
		if ($locationRead) {
			$pictures = array();
			$result = $this->db->query("SELECT `picture`.`visible` AS `visibility`, `folder`, `picture`, `subtitle`, `filename` FROM `picture` JOIN `album` USING(`album`) WHERE `album`='$album' AND `picture`.`deleted`='0' ORDER BY `filename`");
			while ($row = $this->db->fetchArray($result)) {
				$picture = htmlentities($row['picture'], null, "UTF-8");
				$subtitle = htmlentities($row['subtitle'], null, "UTF-8");
				$filename = htmlentities($row['filename'], null, "UTF-8");
				$visible = $row['visibility'];
				$administrator = ($moduleExtended&&$moduleAdmin&&$locationAdmin);
				$folder = htmlentities($row['folder'], null, "UTF-8");
				
				$thumbPath = "../albums/".$folder."thumb_".$filename;
				$picPath = "../albums/".$folder.$filename;
				
				array_push($pictures, array('picture'=>$picture, 'subtitle'=>$subtitle, 'visible'=>$visible, 'thumbPath'=>$thumbPath, 'picPath'=>$picPath, 'administrator'=>$administrator));
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/gallery.thumbs.tpl.php");
		}
	}
	
	/*
	 * Shows the add photo dialog.
	 */
	private function addPhoto() {
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$album = $this->db->escapeString($_GET['id']);
		if ($auth->moduleExtendedAllowed("gallery", $role->getRole())&&$auth->moduleAdminAllowed("gallery", $role->getRole())) {
			$result = $this->db->query("SELECT * FROM `album` WHERE `album`='$album' AND `deleted`='0'");
			while ($row = $this->db->fetchArray($result)) {
				$location = $row['location'];
				if ($auth->locationAdminAllowed($location, $role->getRole())) {
					$album = htmlentities($row['album'], null, "UTF-8");
					require_once("template/gallery.addphoto.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Changes the meta-information of an album.
	 */
	private function edit() {
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$album = $this->db->escapeString($_GET['id']);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($auth->moduleExtendedAllowed("gallery", $role->getRole())) {
			if (isset($_POST['action'])) {
				if ($_POST['action']=="send") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$result = $this->db->query("SELECT * FROM `album` WHERE `album`='$album'");
						while ($row = $this->db->fetchArray($result)) {
							$category = $row['location'];
							if ($auth->locationAdminAllowed($category, $role->getRole())&&$auth->locationAdminAllowed($_POST['category'], $role->getRole())) {
								$photograph = $this->db->escapeString($_POST['photograph']);
								$category = $this->db->escapeString($_POST['category']);
								if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
									$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
								}
								else {
									$date = time();
								}
								$basic = new Basic($this->db);
								$description = $this->db->escapeString($basic->cleanHTML($_POST['description']));
								$this->db->query("UPDATE `album` SET `photograph`='$photograph', `location`='$category', `date`='$date', `description`='$description' WHERE `album`='$album'");
							}
						}
						
					}
				}
			}
			$locations = array();
			$result = $this->db->query("SELECT * FROM `navigation` WHERE `module`='gallery' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
					array_push($locations,array('location'=>htmlentities($row['id'], null, "UTF-8"),'name'=>htmlentities($row['name'], null, "UTF-8")));
				}
			}
			$result = $this->db->query("SELECT * FROM `album` WHERE `album`='$album'");
			while ($row = $this->db->fetchArray($result)) {
				$category = $row['location'];
				if ($auth->locationAdminAllowed($category, $role->getRole())) {
					$photograph = htmlentities($row['photograph'], null, "UTF-8");
					$category = htmlentities($category, null, "UTF-8");
					$dateTime->setTimestamp($row['date']);
					$day = $dateTime->format("d");
					$month = $dateTime->format("m");
					$year = $dateTime->format("Y");
					$description = $row['description'];
					$album = htmlentities($_GET['id'], null, "UTF-8");
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					require_once("template/gallery.edit.tpl.php");
				}
			}
		}
		
	}
	
	/*
	 * Some smaller functions which can be applied on an album. E.g. deleting an album or releasing it.
	 */
	private function doThings() {
		if (isset($_GET['do'])) {
			$user = new User($this->db);
			if ($user->isAdmin()) {
				$role = new Role($this->db);
				$auth = new Authentication($this->db);
				$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
				$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
				if ($_GET['do']=="submit") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						if ($moduleExtended&&$moduleAdmin) {
							$id = $this->db->escapeString($_GET['id']);
							$result = $this->db->query("SELECT * FROM `album` WHERE `album`='$id'");
							while ($row = $this->db->fetchArray($result)) {
								if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
									$admin = $this->db->escapeString($user->getID());
									$adminIP = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
									$this->db->query("UPDATE `album` SET `visible`='1', `admin`='$admin', `admin_ip`='$adminIP' WHERE `album`='$id'");
									$this->db->query("UPDATE `picture` SET `visible`='1' WHERE `album`='$id'");
								}
							}
						}
					}
				}
				if ($_GET['do']=="del") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						if ($moduleExtended&&$moduleAdmin) {
							$id = $this->db->escapeString($_GET['id']);
							$result = $this->db->query("SELECT * FROM `album` WHERE `album`='$id'");
							while ($row = $this->db->fetchArray($result)) {
								if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
									$this->db->query("UPDATE `album` SET `deleted`='1' WHERE `album`='$id'");
									$this->db->query("UPDATE `picture` SET `deleted`='1' WHERE `album`='$id'");
								}
							}
						}
					}
				}
			}
		}
	}
	
	/*
	 * Shows all unreleased albums.
	 */
	private function queue() {
		$user = new User($this->db);
		$role = new Role($this->db);
		$navigation = new Navigation($this->db);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($user->isAdmin()) {
			$auth = new Authentication($this->db);
			$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
			$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
			if ($moduleAdmin&&$moduleExtended) {
				$galleries = array();
				$result = $this->db->query("SELECT * FROM `album` WHERE `visible`='0' AND `deleted`='0'");
				while ($row = $this->db->fetchArray($result)) {
					if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
						$id = htmlentities($row['album'], null, "UTF-8");
						$author = $row['author'];
						$authorName = htmlentities($user->getAcronymbyID($author), null, "UTF-8");
						$authorIP = htmlentities($row['author_ip'], null, "UTF-8");
						$photograph = htmlentities($row['photograph'], null, "UTF-8");
						$location = htmlentities($navigation->getNamebyID($row['location']), null, "UTF-8");
						$description = $row['description'];
						$dateTime->setTimestamp($row['date']);
						$date = $dateTime->format("d\.m\.Y");
						$dateTime->setTimestamp($row['postdate']);
						$postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
						array_push($galleries, array('photograph'=>$photograph, 'album'=>$id, 'authorIP'=>$authorIP, 'author'=>$authorName, 'location'=>$location, 'description'=>$description, 'date'=>$date, 'postdate'=>$postdate));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/gallery.queue.tpl.php");
			}
		}
	}
	
	/*
	 * Shows the upload dialog.
	 */
	private function upload() {
		$success = false;
		if (isset($_GET['success'])) {
			$success = $_GET['success'];
		}
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$basic = new Basic($this->db);
		$user = new User($this->db);
		if ($auth->moduleAdminAllowed("gallery", $role->getRole())) {
			$locations = array();
			$result = $this->db->query("SELECT * FROM `navigation` WHERE `module`='gallery' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())||$auth->locationExtendedAllowed($row['id'], $role->getRole())) {
					array_push($locations,array('location'=>htmlentities($row['id'], null, "UTF-8"), 'name'=>htmlentities($row['name'], null, "UTF-8")));
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			if (isset($_GET['step'])) {
				if ($_GET['step']=="2") {
					$tmpDir = $_GET['dir'];
					require_once("template/gallery.upload.step2.tpl.php");
				}
				else {
					$tmpDir = $user->getID().$basic->randomHash();
					require_once("template/gallery.upload.tpl.php");
				}
			}
			else {
				$tmpDir = $user->getID().$basic->randomHash();
				require_once("template/gallery.upload.tpl.php");
			}
		}
	}
	
	/*
	 * Shows the FTP dialog.
	 */
	private function ftp() {
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		if ($auth->moduleExtendedAllowed("gallery", $role->getRole())) {
			$locations = array();
			$result = $this->db->query("SELECT * FROM `navigation` WHERE `module`='gallery' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
					array_push($locations,array('location'=>htmlentities($row['id'], null, "UTF-8"),'name'=>htmlentities($row['name'], null, "UTF-8")));
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/gallery.ftp.tpl.php");
		}
	}
	
	/*
	 * Shows the frontend of the gallery.
	 */
	public function display() {
		$auth = new Authentication($this->db);
		$basic = new Basic($this->db);
		$role = new Role($this->db);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($auth->moduleReadAllowed("gallery", $role->getRole())) {
			if (!isset($_GET['action'])) {
				$location = "";
				if (isset($_GET['id'])) {
					$location = $this->db->escapeString($_GET['id']);
				}
				else {
					$location = $this->db->escapeString($basic->getHomeLocation());
				}
				$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = $this->db->fetchArray($result)) {
					$location = $this->db->escapeString($row['maps_to']);
				}
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$location = $this->db->escapeString($location);
				$result = $this->db->query("SELECT COUNT(*) AS rowcount FROM `album` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
				$pages = $this->db->getRowCount($result)/10;
				$start = $page*10-10;
				$end = 10;
				$start = $this->db->escapeString($start);
				$galleries = array();
				$result = $this->db->query("SELECT * FROM `album` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `album` DESC LIMIT $start,$end");
				while ($row = $this->db->fetchArray($result)) {
					$album = $this->db->escapeString($row['album']);
					$folder = htmlentities($row['folder'], null, "UTF-8");
					$photograph = $row['photograph'];
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$description = $row['description'];
					$result2 = $this->db->query("SELECT * FROM `picture` WHERE `album`='$album' AND `deleted`='0' AND `visible`='1' ORDER BY RAND() LIMIT 1");
					while ($row2 = $this->db->fetchArray($result2)) {
						$file = htmlentities($row2['filename'], null, "UTF-8");
						$picture = "albums/".$folder."thumb_".$file;
						$picSize = getimagesize($picture);
						array_push($galleries,array('album'=>$album,'photograph'=>$photograph,'date'=>$date,'description'=>$description,'picture'=>$picture,'picSize'=>$picSize));
					}
				}
				require_once("template/gallery.main.tpl.php");
			}
			else {
				if ($_GET['action']=="thumb") {
					$location = $this->db->escapeString($_GET['id']);
					$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
					while ($row = $this->db->fetchArray($result)) {
						$location = $this->db->escapeString($row['maps_to']);
					}
					$album = $this->db->escapeString($_GET['show']);
					$pictures = array();
					$result = $this->db->query("SELECT * FROM `album` WHERE `album`='$album' AND `location`='$location' AND `visible`='1' AND `deleted`='0'");
					while ($row = $this->db->fetchArray($result)) {
						$folder = htmlentities($row['folder'], null, "UTF-8");
						$photograph = htmlentities($row['photograph'], null, "UTF-8");
						$result2 = $this->db->query("SELECT * FROM `picture` WHERE `album`='$album' AND `deleted`='0' AND `visible`='1' ORDER BY `filename`");
						while ($row2 = $this->db->fetchArray($result2)) {
							$file = htmlentities($row2['filename'], null, "UTF-8");
							$id = htmlentities($row2['picture'], null, "UTF-8");
							$thumb = "albums/".$folder."thumb_".$file;
							$picture = "albums/".$folder.$file;
							$picSize = getimagesize($picture);
							$subtitle = htmlentities($row2['subtitle'], null, "UTF-8");
							array_push($pictures,array('subtitle'=>$subtitle, 'id'=>$id,'thumb'=>$thumb,'picture'=>$picture,'picSize'=>$picSize, 'width'=>$picSize[0], 'height'=>$picSize[1]));
						}
						require_once("template/gallery.thumbs.tpl.php");
					}
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
		return null;
	}
	
	public function getTitle() {
		return null;
	}
}
?>