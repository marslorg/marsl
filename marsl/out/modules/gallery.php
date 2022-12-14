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
	private $auth;
	private $role;
	private $PAGINATION_DISTANCE = 3;
	private $basic;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
		$this->basic = new Basic($db, $auth, $role);
	}
	
	/*
	 * Administrator interface for the gallery.
	 */
	public function admin() {
		$user = new User($this->db, $this->role);
		if ($user->isAdmin()) {
			$moduleAdmin = $this->auth->moduleAdminAllowed("gallery", $this->role->getRole());
			$moduleExtended = $this->auth->moduleExtendedAllowed("gallery", $this->role->getRole());
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
		$user = new User($this->db, $this->role);
		$navigation = new Navigation($this->db, $this->auth, $this->role);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($user->isAdmin()) {
			$moduleAdmin = $this->auth->moduleAdminAllowed("gallery", $this->role->getRole());
			$moduleExtended = $this->auth->moduleExtendedAllowed("gallery", $this->role->getRole());
			if ($moduleAdmin) {
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$result = $this->db->query("SELECT COUNT(`album`) AS rowcount FROM `album` WHERE `visible`='1' AND `deleted`='0'");
				$pages = $this->db->getRowCount($result)/10;
				$start = $page*10-10;
				$end = 10;
				$galleries = array();
				$start = $this->db->escapeString($start);
				$result = $this->db->query("SELECT `album`, `author`, `photograph`, `author_ip`, `location`, `description`, `date`, `postdate` FROM `album` WHERE `visible`='1' AND `deleted`='0' ORDER BY `postdate` DESC LIMIT $start,$end");
				while ($row = $this->db->fetchArray($result)) {
					$id = $this->basic->convertToHTMLEntities($row['album']);
					$author = $row['author'];
					$authorName = $this->basic->convertToHTMLEntities($user->getAcronymbyID($author, $this->auth));
					$photograph = $this->basic->convertToHTMLEntities($row['photograph']);
					$authorIP = $this->basic->convertToHTMLEntities($row['author_ip']);
					$location = $this->basic->convertToHTMLEntities($navigation->getNamebyID($row['location']));
					$locationAdmin = $this->auth->locationAdminAllowed($row['location'], $this->role->getRole());
					$editLink = ($moduleExtended&&$locationAdmin);
					$description = $row['description'];
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$dateTime->setTimestamp($row['postdate']);
					$postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
					array_push($galleries, array('photograph'=>$photograph, 'album'=>$id, 'authorIP'=>$authorIP, 'author'=>$authorName, 'location'=>$location, 'description'=>$description, 'date'=>$date, 'postdate'=>$postdate, 'editLink'=>$editLink));
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/gallery.tpl.php");
			}
		}
	}
	
	/*
	 * Allows and administrator to have a look at the album details and change them.
	 * E.g. deleting uploaded pictures.
	 */
	private function details() {
		$album = $this->db->escapeString($_GET['id']);
		$moduleAdmin = $this->auth->moduleAdminAllowed("gallery", $this->role->getRole());
		$moduleExtended = $this->auth->moduleExtendedAllowed("gallery", $this->role->getRole());
		$locationRead = false;
		$locationAdmin = false;
		$result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$album'");
		while ($row = $this->db->fetchArray($result)) {
			$locationRead = $this->auth->locationReadAllowed($row['location'], $this->role->getRole());
			$locationAdmin = $this->auth->locationAdminAllowed($row['location'], $this->role->getRole());
		}
		
		if (isset($_POST['action'])&&$this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
			if ($_POST['action']=="send") {
				if ($moduleExtended&&$moduleAdmin&&$locationAdmin) {
					$result = $this->db->query("SELECT `picture` FROM `picture` WHERE `album`='$album' AND `deleted`='0'");
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
				$picture = $this->basic->convertToHTMLEntities($row['picture']);
				$subtitle = $this->basic->convertToHTMLEntities($row['subtitle']);
				$filename = $this->basic->convertToHTMLEntities($row['filename']);
				$visible = $row['visibility'];
				$administrator = ($moduleExtended&&$moduleAdmin&&$locationAdmin);
				$folder = $this->basic->convertToHTMLEntities($row['folder']);
				
				$thumbPath = "../albums/".$folder."thumb_".$filename;
				$picPath = "../albums/".$folder.$filename;
				
				array_push($pictures, array('picture'=>$picture, 'subtitle'=>$subtitle, 'visible'=>$visible, 'thumbPath'=>$thumbPath, 'picPath'=>$picPath, 'administrator'=>$administrator));
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/gallery.thumbs.tpl.php");
		}
	}
	
	/*
	 * Shows the add photo dialog.
	 */
	private function addPhoto() {
		$album = $this->db->escapeString($_GET['id']);
		if ($this->auth->moduleExtendedAllowed("gallery", $this->role->getRole())&&$this->auth->moduleAdminAllowed("gallery", $this->role->getRole())) {
			$result = $this->db->query("SELECT `location`, `album` FROM `album` WHERE `album`='$album' AND `deleted`='0'");
			while ($row = $this->db->fetchArray($result)) {
				$location = $row['location'];
				if ($this->auth->locationAdminAllowed($location, $this->role->getRole())) {
					$album = $this->basic->convertToHTMLEntities($row['album']);
					require_once("template/gallery.addphoto.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Changes the meta-information of an album.
	 */
	private function edit() {
		$album = $this->db->escapeString($_GET['id']);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($this->auth->moduleExtendedAllowed("gallery", $this->role->getRole())) {
			if (isset($_POST['action'])) {
				if ($_POST['action']=="send") {
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$album'");
						while ($row = $this->db->fetchArray($result)) {
							$category = $row['location'];
							if ($this->auth->locationAdminAllowed($category, $this->role->getRole())&&$this->auth->locationAdminAllowed($_POST['category'], $this->role->getRole())) {
								$photograph = $this->db->escapeString($_POST['photograph']);
								$category = $this->db->escapeString($_POST['category']);
								if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
									$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
								}
								else {
									$date = time();
								}
								$description = $this->db->escapeString($this->basic->cleanHTML($_POST['description']));
								$this->db->query("UPDATE `album` SET `photograph`='$photograph', `location`='$category', `date`='$date', `description`='$description' WHERE `album`='$album'");
							}
						}
						
					}
				}
			}
			$locations = array();
			$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='gallery' AND `type` IN ('1', '2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationAdminAllowed($row['id'], $this->role->getRole())) {
					array_push($locations,array('location'=>$this->basic->convertToHTMLEntities($row['id']),'name'=>$this->basic->convertToHTMLEntities($row['name'])));
				}
			}
			$result = $this->db->query("SELECT `location`, `photograph`, `date`, `description` FROM `album` WHERE `album`='$album'");
			while ($row = $this->db->fetchArray($result)) {
				$category = $row['location'];
				if ($this->auth->locationAdminAllowed($category, $this->role->getRole())) {
					$photograph = $this->basic->convertToHTMLEntities($row['photograph']);
					$category = $this->basic->convertToHTMLEntities($category);
					$dateTime->setTimestamp($row['date']);
					$day = $dateTime->format("d");
					$month = $dateTime->format("m");
					$year = $dateTime->format("Y");
					$description = $row['description'];
					$album = $this->basic->convertToHTMLEntities($_GET['id']);
					$authTime = time();
					$authToken = $this->auth->getToken($authTime);
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
			$user = new User($this->db, $this->role);
			if ($user->isAdmin()) {
				$moduleAdmin = $this->auth->moduleAdminAllowed("gallery", $this->role->getRole());
				$moduleExtended = $this->auth->moduleExtendedAllowed("gallery", $this->role->getRole());
				if ($_GET['do']=="submit") {
					if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
						if ($moduleExtended&&$moduleAdmin) {
							$id = $this->db->escapeString($_GET['id']);
							$result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$id'");
							while ($row = $this->db->fetchArray($result)) {
								if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
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
					if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
						if ($moduleExtended&&$moduleAdmin) {
							$id = $this->db->escapeString($_GET['id']);
							$result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$id'");
							while ($row = $this->db->fetchArray($result)) {
								if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
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
		$user = new User($this->db, $this->role);
		$navigation = new Navigation($this->db, $this->auth, $this->role);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($user->isAdmin()) {
			$moduleAdmin = $this->auth->moduleAdminAllowed("gallery", $this->role->getRole());
			$moduleExtended = $this->auth->moduleExtendedAllowed("gallery", $this->role->getRole());
			if ($moduleAdmin&&$moduleExtended) {
				$galleries = array();
				$result = $this->db->query("SELECT `location`, `album`, `author`, `author_ip`, `photograph`, `description`, `date`, `postdate` FROM `album` WHERE `visible`='0' AND `deleted`='0'");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
						$id = $this->basic->convertToHTMLEntities($row['album']);
						$author = $row['author'];
						$authorName = $this->basic->convertToHTMLEntities($user->getAcronymbyID($author, $this->auth));
						$authorIP = $this->basic->convertToHTMLEntities($row['author_ip']);
						$photograph = $this->basic->convertToHTMLEntities($row['photograph']);
						$location = $this->basic->convertToHTMLEntities($navigation->getNamebyID($row['location']));
						$description = $row['description'];
						$dateTime->setTimestamp($row['date']);
						$date = $dateTime->format("d\.m\.Y");
						$dateTime->setTimestamp($row['postdate']);
						$postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
						array_push($galleries, array('photograph'=>$photograph, 'album'=>$id, 'authorIP'=>$authorIP, 'author'=>$authorName, 'location'=>$location, 'description'=>$description, 'date'=>$date, 'postdate'=>$postdate));
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
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
		$user = new User($this->db, $this->role);
		if ($this->auth->moduleAdminAllowed("gallery", $this->role->getRole())) {
			$locations = array();
			$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='gallery' AND `type` IN ('1', '2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationAdminAllowed($row['id'], $this->role->getRole())||$this->auth->locationExtendedAllowed($row['id'], $this->role->getRole())) {
					array_push($locations,array('location'=>$this->basic->convertToHTMLEntities($row['id']), 'name'=>$this->basic->convertToHTMLEntities($row['name'])));
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			if (isset($_GET['step'])) {
				if ($_GET['step']=="2") {
					$tmpDir = $_GET['dir'];
					require_once("template/gallery.upload.step2.tpl.php");
				}
				else {
					$tmpDir = $user->getID().$this->basic->randomHash();
					require_once("template/gallery.upload.tpl.php");
				}
			}
			else {
				$tmpDir = $user->getID().$this->basic->randomHash();
				require_once("template/gallery.upload.tpl.php");
			}
		}
	}
	
	/*
	 * Shows the FTP dialog.
	 */
	private function ftp() {
		if ($this->auth->moduleExtendedAllowed("gallery", $this->role->getRole())) {
			$locations = array();
			$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='gallery' AND `type` IN ('1', '2') ORDER BY `pos`");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationAdminAllowed($row['id'], $this->role->getRole())) {
					array_push($locations,array('location'=>$this->basic->convertToHTMLEntities($row['id']),'name'=>$this->basic->convertToHTMLEntities($row['name'])));
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/gallery.ftp.tpl.php");
		}
	}
	
	/*
	 * Shows the frontend of the gallery.
	 */
	public function display() {
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($this->auth->moduleReadAllowed("gallery", $this->role->getRole())) {
			if (!isset($_GET['action'])) {
				$location = "";
				if (isset($_GET['id'])) {
					$location = $this->db->escapeString($_GET['id']);
				}
				else {
					$location = $this->db->escapeString($this->basic->getHomeLocation());
				}
				$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = $this->db->fetchArray($result)) {
					$location = $this->db->escapeString($row['maps_to']);
				}
				$location = $this->db->escapeString($location);
				list($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage) = $this->getPagination($location);
				$galleries = array();
				$result = $this->db->query("SELECT `album`, `folder`, `photograph`, `date`, `description`, (SELECT `filename` FROM `picture` AS p WHERE `a`.`album` = `p`.`album` AND `deleted` = '0' AND `visible` = '1' ORDER BY RAND() LIMIT 1) AS `filename` FROM `album` AS a WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `postdate` DESC LIMIT $start,$end");
				while ($row = $this->db->fetchArray($result)) {
					$album = $this->db->escapeString($row['album']);
					$folder = $this->basic->convertToHTMLEntities($row['folder']);
					$photograph = $row['photograph'];
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$description = $row['description'];
					$file = htmlspecialchars($row['filename'], 0, "UTF-8");
					$picture = "albums/".$folder."thumb_".$file;
					$picSize = "";
					if (file_exists($picture)) {
						$picSize = getimagesize($picture);
					}
					else {
						$picture = "";
					}
					array_push($galleries, array('album'=>$album,'photograph'=>$photograph,'date'=>$date,'description'=>$description,'picture'=>$picture,'picSize'=>$picSize));
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
					$result = $this->db->query("SELECT `folder`, `photograph` FROM `album` WHERE `album`='$album' AND `location`='$location' AND `visible`='1' AND `deleted`='0'");
					while ($row = $this->db->fetchArray($result)) {
						$folder = $this->basic->convertToHTMLEntities($row['folder']);
						$photograph = $this->basic->convertToHTMLEntities($row['photograph']);
						$result2 = $this->db->query("SELECT `filename`, `picture`, `subtitle` FROM `picture` WHERE `album`='$album' AND `deleted`='0' AND `visible`='1' ORDER BY `filename`");
						while ($row2 = $this->db->fetchArray($result2)) {
							$file = htmlspecialchars($row2['filename'], 0, "UTF-8");
							$id = $this->basic->convertToHTMLEntities($row2['picture']);
							$thumb = "albums/".$folder."thumb_".$file;
							$picture = "albums/".$folder.$file;
                            if (file_exists($picture)) {
                                $picSize = getimagesize($picture);
                                $subtitle = $this->basic->convertToHTMLEntities($row2['subtitle']);
                                array_push($pictures, array('subtitle'=>$subtitle, 'id'=>$id,'thumb'=>$thumb,'picture'=>$picture,'picSize'=>$picSize, 'width'=>$picSize[0], 'height'=>$picSize[1]));
                            }
						}
						require_once("template/gallery.thumbs.tpl.php");
					}
				}
			}
		}
	}

	private function getPagination($location) {
        $result = $this->db->query("SELECT COUNT(`album`) AS rowcount FROM `album` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
        $pages = $this->db->getRowCount($result)/10;
        $page = 1;
        if (isset($_GET['page'])) {
        	$page = $_GET['page'];
        }
        $startPage = 1;
        if ($page - $this->PAGINATION_DISTANCE > 1) {
        	$startPage = $page - $this->PAGINATION_DISTANCE;
        }
        $endPage = $pages;
        if ($page + $this->PAGINATION_DISTANCE <= $endPage) {
        	$endPage = $page + $this->PAGINATION_DISTANCE;
        }
        $showFirstPage = $page > 1;
        $showPreviousPage = $page > 2;
        $showNextPage = $page < $pages - 1;
        $showLastPage = $page < $pages;
        $start = $page*10-10;
        $end = 10;
        $start = $this->db->escapeString($start);

        return array($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage);
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