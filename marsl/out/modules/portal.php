<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/module.php");

class Portal implements Module {
	
	/*
	 * Displays the frontend portal with the featured content slider and the category boxes.
	 */
	public function display() {
		$id;
		$basic = new Basic();
		$auth = new Authentication();
		$role = new Role();
		$db = new DB();
		if (isset($_GET['id'])) {
			$id = mysql_real_escape_string($_GET['id']);
		}
		else {
			$id = mysql_real_escape_string($basic->getHomeLocation());
		}
		$result = $db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
		while ($row = mysql_fetch_array($result)) {
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
		$db = new DB();
		$auth = new Authentication();
		$role = new Role();
		$news = array();
		$result = $db->query("SELECT * FROM `news` JOIN `news_picture` ON `picture2`=`picture` WHERE `deleted`='0' AND `visible`='1' AND `featured`='1' ORDER BY `postdate` DESC LIMIT 4");
		while ($row = mysql_fetch_array($result)) {
			$location = htmlentities($row['location']);
			$photograph = "";
			if ($auth->locationReadAllowed($location, $role->getRole())) {
				$date = date("d\.m\.Y", $row['date']);
				if (!empty($row['photograph'])) {
					$photograph = " Foto: ".htmlentities($row['photograph']);
				}
				array_push($news, array('location'=>$location, 'picture'=>htmlentities($row['url']), 'photograph'=>$photograph, 'date'=>$date, 'news'=>$row['news'], 'headline'=>htmlentities($row['headline']), 'title'=>htmlentities($row['title']), 'teaser'=>$row['teaser']));
			}
		}
		require_once("template/portal.featured.tpl.php");
	}
	
	/*
	 * Displays the content boxes.
	 */
	private function constructPortal() {
		$db = new DB();
		$auth = new Authentication();
		$role = new Role();
		$pages = array();
		$result = $db->query("SELECT * FROM `navigation` WHERE `module`='news' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
		while ($row = mysql_fetch_array($result)) {
			if ($auth->locationReadAllowed($row['id'], $role->getRole())) {
				array_push($pages, array('location'=>$row['id'], 'name'=>htmlentities($row['name'])));
			}
		}
		require_once("template/portal.head.tpl.php");
		$nb_id = 0;
		foreach ($pages as $page) {
			$location = mysql_real_escape_string($page['location']);
			$news = array();
			$result = $db->query("SELECT * FROM `news` WHERE `location`='$location' AND `visible`='1' AND `deleted`='0' AND `featured`='0' ORDER BY `postdate` DESC LIMIT 3");
			while ($row = mysql_fetch_array($result)) {
				$picID = mysql_real_escape_string($row['picture1']);
				$picture = "empty";
				$photograph = "";
				$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID'");
				while ($row2 = mysql_fetch_array($result2)) {
					$picture = htmlentities($row2['url']);
					if (!empty($row2['photograph'])) {
						$photograph = "<br /><b>Foto: ".htmlentities($row2['photograph'])."</b><br />";
					}
				}
				$width = 0;
				$height = 0;
				if (file_exists("news/".$picture)) {
					$picinfo = @getimagesize("news/".$picture);
					$width = $picinfo[0]/1.5;
					$height = $picinfo[1]/1.5;
				}
				array_push($news, array('width'=>$width,'height'=>$height,'picture'=>$picture, 'photograph'=>$photograph, 'teaser'=>$row['teaser'],'location'=>$location, 'news'=>$row['news'], 'headline'=>htmlentities($row['headline']), 'title'=>htmlentities($row['title'])));
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
		$user = new User();
		$auth = new Authentication();
		$role = new Role();
		if ($user->isAdmin()) {
			if ($auth->moduleAdminAllowed("portal", $role->getRole())) {
				$db = new DB();
				if (isset($_POST['action'])) {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$result = $db->query("SELECT * FROM `news` WHERE `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
						while ($row = mysql_fetch_array($result)) {
							$location = $row['location'];
							$picture = "empty";
							$picID = mysql_real_escape_string($row['picture2']);
							$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID'");
							while ($row2 = mysql_fetch_array($result2)) {
								$picture = htmlentities($row2['url']);
							}
							if ($auth->moduleReadAllowed("news", $role->getGuestRole())&&($picture!="empty")&&$auth->locationReadAllowed($location, $role->getGuestRole())&&$auth->locationAdminAllowed($location, $role->getRole())) {
								$article = mysql_real_escape_string($row['news']);
								if (isset($_POST[$row['news']])) {
									$db->query("UPDATE `news` SET `featured`='1' WHERE `news`='$article'");
								}
								else {
									$db->query("UPDATE `news` SET `featured`='0' WHERE `news`='$article'");
								}
							}
						}
					}
				}
				$result = $db->query("SELECT * FROM `news` WHERE `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
				$news = array();
				while ($row = mysql_fetch_array($result)) {
					$location = $row['location'];
					$date = date("d\.m\.Y", $row['postdate']);
					$picture = "empty";
					$picID = mysql_real_escape_string($row['picture2']);
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture = $row2['url'];
					}
					if ($auth->moduleReadAllowed("news", $role->getGuestRole())&&($picture!="empty")&&$auth->locationReadAllowed($location, $role->getGuestRole())&&$auth->locationAdminAllowed($location, $role->getRole())) {
						array_push($news, array('headline'=>htmlentities($row['headline']), 'id'=>$row['news'], 'title'=>htmlentities($row['title']), 'date'=>$date, 'featured'=>$row['featured']));
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
}

?>