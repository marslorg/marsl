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
	
	/*
	 * Administrator interface for the gallery.
	 */
	public function admin() {
		$user = new User();
		$db = new DB();
		$role = new Role();
		if ($user->isAdmin()) {
			$auth = new Authentication();
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
		$user = new User();
		$db = new DB();
		$role = new Role();
		$navigation = new Navigation();
		if ($user->isAdmin()) {
			$auth = new Authentication();
			$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
			$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
			if ($moduleAdmin) {
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$result = $db->query("SELECT * FROM `album` WHERE `visible`='1' AND `deleted`='0'");
				$pages = mysql_num_rows($result)/10;
				$start = $page*10-10;
				$end = 10;
				$galleries = array();
				$start = mysql_real_escape_string($start);
				$result = $db->query("SELECT * FROM `album` WHERE `visible`='1' AND `deleted`='0' ORDER BY `album` DESC LIMIT $start,$end");
				while ($row = mysql_fetch_array($result)) {
					$id = htmlentities($row['album']);
					$author = $row['author'];
					$authorName = htmlentities($user->getAcronymbyID($author));
					$photograph = htmlentities($row['photograph']);
					$authorIP = htmlentities($row['author_ip']);
					$location = htmlentities($navigation->getNamebyID($row['location']));
					$locationAdmin = $auth->locationAdminAllowed($row['location'], $role->getRole());
					$editLink = ($moduleExtended&&$locationAdmin);
					$description = $row['description'];
					$date = date("d\.m\.Y", $row['date']);
					$postdate = date("d\. M Y \u\m H\:i\:s", $row['postdate']);
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
		$db = new DB();
		$role = new Role();
		$auth = new Authentication();
		$album = mysql_real_escape_string($_GET['id']);
		$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
		$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
		$locationRead = false;
		$locationAdmin = false;
		$result = $db->query("SELECT `location` FROM `album` WHERE `album`='$album'");
		while ($row = mysql_fetch_array($result)) {
			$locationRead = $auth->locationReadAllowed($row['location'], $role->getRole());
			$locationAdmin = $auth->locationAdminAllowed($row['location'], $role->getRole());
		}
		
		if (isset($_POST['action'])&&$auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
			if ($_POST['action']=="send") {
				if ($moduleExtended&&$moduleAdmin&&$locationAdmin) {
					$result = $db->query("SELECT * FROM `picture` WHERE `album`='$album' AND `deleted`='0'");
					while ($row = mysql_fetch_array($result)) {
						$picture = mysql_real_escape_string($row['picture']);
						if (isset($_POST[$picture.'_delete'])) {
							$db->query("UPDATE `picture` SET `deleted`='1' WHERE `picture`='$picture'");
						}
						if (isset($_POST[$picture.'_submit'])) {
							$db->query("UPDATE `picture` SET `visible`='1' WHERE `picture`='$picture'");
						}
					}
				}
			}
		}
		
		if ($locationRead) {
			$pictures = array();
			$result = $db->query("SELECT `picture`.`visible` AS `visibility`, `folder`, `picture`, `subtitle`, `filename` FROM `picture` JOIN `album` USING(`album`) WHERE `album`='$album' AND `picture`.`deleted`='0' ORDER BY `filename`");
			while ($row = mysql_fetch_array($result)) {
				$picture = htmlentities($row['picture']);
				$subtitle = htmlentities($row['subtitle']);
				$filename = htmlentities($row['filename']);
				$visible = $row['visibility'];
				$administrator = ($moduleExtended&&$moduleAdmin&&$locationAdmin);
				$folder = htmlentities($row['folder']);
				
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
		$db = new DB();
		$role = new Role();
		$auth = new Authentication();
		$album = mysql_real_escape_string($_GET['id']);
		if ($auth->moduleExtendedAllowed("gallery", $role->getRole())&&$auth->moduleAdminAllowed("gallery", $role->getRole())) {
			$result = $db->query("SELECT * FROM `album` WHERE `album`='$album' AND `deleted`='0'");
			while ($row = mysql_fetch_array($result)) {
				$location = $row['location'];
				if ($auth->locationAdminAllowed($location, $role->getRole())) {
					$album = htmlentities($row['album']);
					require_once("template/gallery.addphoto.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Changes the meta-information of an album.
	 */
	private function edit() {
		$db = new DB();
		$role = new Role();
		$auth = new Authentication();
		$album = mysql_real_escape_string($_GET['id']);
		if ($auth->moduleExtendedAllowed("gallery", $role->getRole())) {
			if (isset($_POST['action'])) {
				if ($_POST['action']=="send") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$result = $db->query("SELECT * FROM `album` WHERE `album`='$album'");
						while ($row = mysql_fetch_array($result)) {
							$category = $row['location'];
							if ($auth->locationAdminAllowed($category, $role->getRole())&&$auth->locationAdminAllowed($_POST['category'], $role->getRole())) {
								$photograph = mysql_real_escape_string($_POST['photograph']);
								$category = mysql_real_escape_string($_POST['category']);
								if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
									$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
								}
								else {
									$date = time();
								}
								$basic = new Basic();
								$description = mysql_real_escape_string($basic->cleanHTML($_POST['description']));
								$db->query("UPDATE `album` SET `photograph`='$photograph', `location`='$category', `date`='$date', `description`='$description' WHERE `album`='$album'");
							}
						}
						
					}
				}
			}
			$locations = array();
			$result = $db->query("SELECT * FROM `navigation` WHERE `module`='gallery' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
			while ($row = mysql_fetch_array($result)) {
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
					array_push($locations,array('location'=>htmlentities($row['id']),'name'=>htmlentities($row['name'])));
				}
			}
			$result = $db->query("SELECT * FROM `album` WHERE `album`='$album'");
			while ($row = mysql_fetch_array($result)) {
				$category = $row['location'];
				if ($auth->locationAdminAllowed($category, $role->getRole())) {
					$photograph = htmlentities($row['photograph']);
					$category = htmlentities($category);
					$day = date("d", $row['date']);
					$month = date("m", $row['date']);
					$year = date("Y", $row['date']);
					$description = $row['description'];
					$album = htmlentities($_GET['id']);
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
			$user = new User();
			if ($user->isAdmin()) {
				$db = new DB();
				$role = new Role();
				$auth = new Authentication();
				$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
				$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
				if ($_GET['do']=="submit") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						if ($moduleExtended&&$moduleAdmin) {
							$id = mysql_real_escape_string($_GET['id']);
							$result = $db->query("SELECT * FROM `album` WHERE `album`='$id'");
							while ($row = mysql_fetch_array($result)) {
								if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
									$admin = mysql_real_escape_string($user->getID());
									$adminIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
									$db->query("UPDATE `album` SET `visible`='1', `admin`='$admin', `admin_ip`='$adminIP' WHERE `album`='$id'");
									$db->query("UPDATE `picture` SET `visible`='1' WHERE `album`='$id'");
								}
							}
						}
					}
				}
				if ($_GET['do']=="del") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						if ($moduleExtended&&$moduleAdmin) {
							$id = mysql_real_escape_string($_GET['id']);
							$result = $db->query("SELECT * FROM `album` WHERE `album`='$id'");
							while ($row = mysql_fetch_array($result)) {
								if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
									$db->query("UPDATE `album` SET `deleted`='1' WHERE `album`='$id'");
									$db->query("UPDATE `picture` SET `deleted`='1' WHERE `album`='$id'");
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
		$user = new User();
		$db = new DB();
		$role = new Role();
		$navigation = new Navigation();
		if ($user->isAdmin()) {
			$auth = new Authentication();
			$moduleAdmin = $auth->moduleAdminAllowed("gallery", $role->getRole());
			$moduleExtended = $auth->moduleExtendedAllowed("gallery", $role->getRole());
			if ($moduleAdmin&&$moduleExtended) {
				$galleries = array();
				$result = $db->query("SELECT * FROM `album` WHERE `visible`='0' AND `deleted`='0'");
				while ($row = mysql_fetch_array($result)) {
					if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
						$id = htmlentities($row['album']);
						$author = $row['author'];
						$authorName = htmlentities($user->getAcronymbyID($author));
						$authorIP = htmlentities($row['author_ip']);
						$photograph = htmlentities($row['photograph']);
						$location = htmlentities($navigation->getNamebyID($row['location']));
						$description = $row['description'];
						$date = date("d\.m\.Y", $row['date']);
						$postdate = date("d\. M Y \u\m H\:i\:s", $row['postdate']);
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
		$db = new DB();
		$role = new Role();
		$auth = new Authentication();
		$basic = new Basic();
		$user = new User();
		if ($auth->moduleAdminAllowed("gallery", $role->getRole())) {
			$locations = array();
			$result = $db->query("SELECT * FROM `navigation` WHERE `module`='gallery' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
			while ($row = mysql_fetch_array($result)) {
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())||$auth->locationExtendedAllowed($row['id'], $role->getRole())) {
					array_push($locations,array('location'=>htmlentities($row['id']), 'name'=>htmlentities($row['name'])));
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
		$db = new DB();
		$role = new Role();
		$auth = new Authentication();
		if ($auth->moduleExtendedAllowed("gallery", $role->getRole())) {
			$locations = array();
			$result = $db->query("SELECT * FROM `navigation` WHERE `module`='gallery' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
			while ($row = mysql_fetch_array($result)) {
				if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
					array_push($locations,array('location'=>htmlentities($row['id']),'name'=>htmlentities($row['name'])));
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
		$auth = new Authentication();
		$basic = new Basic();
		$db = new DB();
		$role = new Role();
		if ($auth->moduleReadAllowed("gallery", $role->getRole())) {
			if (!isset($_GET['action'])) {
				$location = "";
				if (isset($_GET['id'])) {
					$location = mysql_real_escape_string($_GET['id']);
				}
				else {
					$location = mysql_real_escape_string($basic->getHomeLocation());
				}
				$result = $db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = mysql_fetch_array($result)) {
					$location = mysql_real_escape_string($row['maps_to']);
				}
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$location = mysql_real_escape_string($location);
				$result = $db->query("SELECT * FROM `album` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
				$pages = mysql_num_rows($result)/10;
				$start = $page*10-10;
				$end = 10;
				$start = mysql_real_escape_string($start);
				$galleries = array();
				$result = $db->query("SELECT * FROM `album` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `album` DESC LIMIT $start,$end");
				while ($row = mysql_fetch_array($result)) {
					$album = mysql_real_escape_string($row['album']);
					$folder = htmlentities($row['folder']);
					$photograph = $row['photograph'];
					$date = date("d\.m\.Y", $row['date']);
					$description = $row['description'];
					$result2 = $db->query("SELECT * FROM `picture` WHERE `album`='$album' AND `deleted`='0' AND `visible`='1' ORDER BY RAND() LIMIT 1");
					while ($row2 = mysql_fetch_array($result2)) {
						$file = htmlentities($row2['filename']);
						$picture = "albums/".$folder."thumb_".$file;
						$picSize = getimagesize($picture);
						array_push($galleries,array('album'=>$album,'photograph'=>$photograph,'date'=>$date,'description'=>$description,'picture'=>$picture,'picSize'=>$picSize));
					}
				}
				require_once("template/gallery.main.tpl.php");
			}
			else {
				if ($_GET['action']=="thumb") {
					$location = mysql_real_escape_string($_GET['id']);
					$result = $db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
					while ($row = mysql_fetch_array($result)) {
						$location = mysql_real_escape_string($row['maps_to']);
					}
					$album = mysql_real_escape_string($_GET['show']);
					$pictures = array();
					$result = $db->query("SELECT * FROM `album` WHERE `album`='$album' AND `location`='$location' AND `visible`='1' AND `deleted`='0'");
					while ($row = mysql_fetch_array($result)) {
						$folder = htmlentities($row['folder']);
						$photograph = htmlentities($row['photograph']);
						$result2 = $db->query("SELECT * FROM `picture` WHERE `album`='$album' AND `deleted`='0' AND `visible`='1' ORDER BY `filename`");
						while ($row2 = mysql_fetch_array($result2)) {
							$file = htmlentities($row2['filename']);
							$id = htmlentities($row2['picture']);
							$thumb = "albums/".$folder."thumb_".$file;
							$picture = "albums/".$folder.$file;
							$picSize = getimagesize($picture);
							$subtitle = htmlentities($row2['subtitle']);
							array_push($pictures,array('subtitle'=>$subtitle, 'id'=>$id,'thumb'=>$thumb,'picture'=>$picture,'picSize'=>$picSize));
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
}
?>