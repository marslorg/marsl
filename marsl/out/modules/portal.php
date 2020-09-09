<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/module.php");

class Portal implements Module {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	/*
	 * Displays the frontend portal with the featured content slider and the category boxes.
	 */
	public function display() {
		$id;
		$basic = new Basic($this->db);
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		if (isset($_GET['id'])) {
			$id = $this->db->escapeString($_GET['id']);
		}
		else {
			$id = $this->db->escapeString($basic->getHomeLocation());
		}
		$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
		while ($row = $this->db->fetchArray($result)) {
			$id = $row['maps_to'];
		}
		if ($auth->moduleReadAllowed("portal", $role->getRole())&&$auth->moduleReadAllowed("news", $role->getRole())) {
			if ($auth->locationReadAllowed($id, $role->getRole())) {
				$this->constructFeaturedContent();
				$this->constructPortal();
			}
		}
	}
	
	/*
	 * Displays the featured content slider.
	 */
	private function constructFeaturedContent() {
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		$news = array();
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		$result = $this->db->query("SELECT * FROM `news` JOIN `news_picture` ON `picture2`=`picture` WHERE `deleted`='0' AND `visible`='1' AND `featured`='1' ORDER BY `postdate` DESC LIMIT 4");
		while ($row = $this->db->fetchArray($result)) {
			$location = htmlentities($row['location'], null, "UTF-8");
			$photograph = "";
			if ($auth->locationReadAllowed($location, $role->getRole())) {
				$dateTime->setTimestamp($row['date']);
				$date = $dateTime->format("d\.m\.Y");
				if (!empty($row['photograph'])) {
					$photograph = " Foto: ".htmlentities($row['photograph'], null, "UTF-8");
				}
				array_push($news, array('location'=>$location, 'picture'=>htmlentities($row['url'], null, "UTF-8"), 'photograph'=>$photograph, 'date'=>$date, 'news'=>$row['news'], 'headline'=>htmlentities($row['headline'], null, "UTF-8"), 'title'=>htmlentities($row['title'], null, "UTF-8"), 'teaser'=>$row['teaser']));
			}
		}
		require_once("template/portal.featured.tpl.php");
	}
	
	/*
	 * Displays the content boxes.
	 */
	private function constructPortal() {
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		$pages = array();
		$result = $this->db->query("SELECT * FROM `navigation` WHERE `module`='news' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
		while ($row = $this->db->fetchArray($result)) {
			if ($auth->locationReadAllowed($row['id'], $role->getRole())) {
				array_push($pages, array('location'=>$row['id'], 'name'=>htmlentities($row['name'], null, "UTF-8")));
			}
		}
		require_once("template/portal.head.tpl.php");
		$nb_id = 0;
		foreach ($pages as $page) {
			$location = $this->db->escapeString($page['location']);
			$news = array();
			$result = $this->db->query("SELECT * FROM `news` WHERE `location`='$location' AND `visible`='1' AND `deleted`='0' AND `featured`='0' ORDER BY `postdate` DESC LIMIT 3");
			while ($row = $this->db->fetchArray($result)) {
				$picID = $this->db->escapeString($row['picture1']);
				$picture = "empty";
				$photograph = "";
				$result2 = $this->db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID'");
				while ($row2 = $this->db->fetchArray($result2)) {
					$picture = htmlentities($row2['url'], null, "UTF-8");
					if (!empty($row2['photograph'])) {
						$photograph = "<br /><b>Foto: ".htmlentities($row2['photograph'], null, "UTF-8")."</b><br />";
					}
				}
				$width = 0;
				$height = 0;
				if (file_exists("news/".$picture)) {
					$picinfo = @getimagesize("news/".$picture);
					$width = $picinfo[0]/1.5;
					$height = $picinfo[1]/1.5;
				}
				array_push($news, array('width'=>$width,'height'=>$height,'picture'=>$picture, 'photograph'=>$photograph, 'teaser'=>$row['teaser'],'location'=>$location, 'news'=>$row['news'], 'headline'=>htmlentities($row['headline'], null, "UTF-8"), 'title'=>htmlentities($row['title'], null, "UTF-8")));
			}
			require("template/portal.main.tpl.php");
			$nb_id++;
		}
		require_once("template/portal.foot.tpl.php");
	}
	
	/*
	 * Admin interface for the portal to choose the articles which should be shown in the featured content slider.
	 */
	public function admin() {
		$user = new User($this->db);
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($user->isAdmin()) {
			if ($auth->moduleAdminAllowed("portal", $role->getRole())) {
				if (isset($_POST['action'])) {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$result = $this->db->query("SELECT * FROM `news` WHERE `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC LIMIT 30");
						while ($row = $this->db->fetchArray($result)) {
							$location = $row['location'];
							$picture = "empty";
							$picID = $this->db->escapeString($row['picture2']);
							$result2 = $this->db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID'");
							while ($row2 = $this->db->fetchArray($result2)) {
								$picture = htmlentities($row2['url'], null, "UTF-8");
							}
							if ($auth->moduleReadAllowed("news", $role->getGuestRole())&&($picture!="empty")&&$auth->locationReadAllowed($location, $role->getGuestRole())&&$auth->locationAdminAllowed($location, $role->getRole())) {
								$article = $this->db->escapeString($row['news']);
								if (isset($_POST[$row['news']])) {
									$this->db->query("UPDATE `news` SET `featured`='1' WHERE `news`='$article'");
								}
								else {
									$this->db->query("UPDATE `news` SET `featured`='0' WHERE `news`='$article'");
								}
							}
						}
					}
				}
				$result = $this->db->query("SELECT * FROM `news` WHERE `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC LIMIT 30");
				$news = array();
				while ($row = $this->db->fetchArray($result)) {
					$location = $row['location'];
					$dateTime->setTimestamp($row['postdate']);
					$date = $dateTime->format("d\.m\.Y");
					$picture = "empty";
					$picID = $this->db->escapeString($row['picture2']);
					$result2 = $this->db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID'");
					while ($row2 = $this->db->fetchArray($result2)) {
						$picture = $row2['url'];
					}
					if ($auth->moduleReadAllowed("news", $role->getGuestRole())&&($picture!="empty")&&$auth->locationReadAllowed($location, $role->getGuestRole())&&$auth->locationAdminAllowed($location, $role->getRole())) {
						array_push($news, array('headline'=>htmlentities($row['headline'], null, "UTF-8"), 'id'=>$row['news'], 'title'=>htmlentities($row['title'], null, "UTF-8"), 'date'=>$date, 'featured'=>$row['featured']));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/portal.tpl.php");
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