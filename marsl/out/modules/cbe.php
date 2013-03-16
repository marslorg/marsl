<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/cbe/location.php");
include_once(dirname(__FILE__)."/cbe/band.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");

class CBE implements Module {
	
	public function display() {
		
	}
	
	public function admin() {
		$auth = new Authentication();
		$role = new Role();
		if ($auth->moduleAdminAllowed("cbe", $role->getRole())) {
			require_once("template/cbe.main.tpl.php");
			if (isset($_GET['action'])) {
				$band = new Band();
				$club = new Location();
				if ($_GET['action']=="bands") {
					$band->admin();
				}
				if ($_GET['action']=="clubs") {
					$club->admin();
				}
				if ($_GET['action']=="editband") {
					$id = mysql_real_escape_string($_GET['band']);
					$band->edit($id);
				}
				if ($_GET['action']=="editclub") {
					$id = mysql_real_escape_string($_GET['club']);
					$club->edit($id);
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
	
	public function isTaggable() {
		return true;
	}
	
	public function getTagList() {
		$types = array();
		array_push($types, array('type'=>"band", 'text'=>"Bands"));
		array_push($types, array('type'=>"location", 'text'=>"Location"));
		return $types;
	}
	
	public function addTags($tagString, $type, $news) {
		$db = new DB();
		$tags = array_filter(explode(";", $tagString));
		$news = mysql_real_escape_string($news);
		if ($type=="band") {
			$db->query("DELETE FROM `news_tag` WHERE `type`='cbe_band' AND `news`='$news'");
		}
		if ($type=="location") {
			$db->query("DELETE FROM `news_tag` WHERE `type`='cbe_location' AND `news`='$news'");
		}
		foreach ($tags as $tag) {
			$tag = mysql_real_escape_string($tag);
			$tag = trim($tag);
			if ($type=="band") {
				$bandID = "";
				
				if (!$db->isExisting("SELECT * FROM `band` WHERE `tag`='$tag'")) {
					$db->query("INSERT INTO `band`(`tag`) VALUES('$tag')");
				}

				$result = $db->query("SELECT `id` FROM `band` WHERE `tag`='$tag'");
				while ($row = mysql_fetch_array($result)) {
					$bandID = $row['id'];
				}
				$db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$bandID','$news','cbe_band')");
			}
			if ($type=="location") {
				$locationID = "";
				
				if (!$db->isExisting("SELECT * FROM `location` WHERE `tag`='$tag'")) {
					$db->query("INSERT INTO `location`(`tag`) VALUES('$tag')");
				}
				
				$result = $db->query("SELECT `id` FROM `location` WHERE `tag`='$tag'");
				while ($row = mysql_fetch_array($result)) {
					$locationID = $row['id'];
				}
				
				$db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$locationID','$news','cbe_location')");
			}
		}
	}
	
	public function getTagString($type, $news) {
		
		$db = new DB();
		$retString = array();
		$news = mysql_real_escape_string($news);
		
		if ($type=="band") {
			$result = $db->query("SELECT `band`.`tag` AS tagname FROM `band` JOIN `news_tag` ON(`band`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_band' AND `news`='$news' ORDER BY `band`.`tag`");
			while ($row = mysql_fetch_array($result)) {
				array_push($retString, $row['tagname']);
			}
		}
		
		if ($type=="location") {
			$result = $db->query("SELECT `location`.`tag` AS tagname FROM `location` JOIN `news_tag` ON(`location`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_location' AND `news`='$news' ORDER BY `location`.`tag`");
			while ($row = mysql_fetch_array($result)) {
				array_push($retString, $row['tagname']);
			}
		}
		
		return implode(";", $retString);
		
	}
}
?>