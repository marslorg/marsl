<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/cbe/location.php");
include_once(dirname(__FILE__)."/cbe/band.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");

class CBE implements Module {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	public function display() {
		
	}
	
	public function admin() {
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		if ($auth->moduleAdminAllowed("cbe", $role->getRole())) {
			require_once("template/cbe.main.tpl.php");
			if (isset($_GET['action'])) {
				$band = new Band($this->db);
				$club = new Location($this->db);
				if ($_GET['action']=="bands") {
					$band->admin();
				}
				if ($_GET['action']=="clubs") {
					$club->admin();
				}
				if ($_GET['action']=="editband") {
					$id = $this->db->escapeString($_GET['band']);
					$band->edit($id);
				}
				if ($_GET['action']=="editclub") {
					$id = $this->db->escapeString($_GET['club']);
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
		array_push($types, array('type'=>"location", 'text'=>"Locations"));
		return $types;
	}
	
	public function addTags($tagString, $type, $news) {
		$tags = array_filter(explode(";", $tagString));
		$news = $this->db->escapeString($news);
		if ($type=="band") {
			$this->db->query("DELETE FROM `news_tag` WHERE `type`='cbe_band' AND `news`='$news'");
		}
		if ($type=="location") {
			$this->db->query("DELETE FROM `news_tag` WHERE `type`='cbe_location' AND `news`='$news'");
		}
		foreach ($tags as $tag) {
			$tag = $this->db->escapeString($tag);
			$tag = trim($tag);
			if ($type=="band") {
				$bandID = "";
				
				if ((strlen($tag)>0)&&(!$this->db->isExisting("SELECT * FROM `band` WHERE `tag`='$tag'"))) {
					$this->db->query("INSERT INTO `band`(`tag`) VALUES('$tag')");
				}

				$result = $this->db->query("SELECT `id` FROM `band` WHERE `tag`='$tag'");
				while ($row = $this->db->fetchArray($result)) {
					$bandID = $row['id'];
				}
				$this->db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$bandID','$news','cbe_band')");
			}
			if ($type=="location") {
				$locationID = "";
				
				if ((strlen($tag)>0)&&(!$this->db->isExisting("SELECT * FROM `location` WHERE `tag`='$tag'"))) {
					$this->db->query("INSERT INTO `location`(`tag`) VALUES('$tag')");
				}
				
				$result = $this->db->query("SELECT `id` FROM `location` WHERE `tag`='$tag'");
				while ($row = $this->db->fetchArray($result)) {
					$locationID = $row['id'];
				}
				
				$this->db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$locationID','$news','cbe_location')");
			}
		}
	}
	
	public function getTagString($type, $news) {
		$retString = array();
		$news = $this->db->escapeString($news);
		
		if ($type=="band") {
			$result = $this->db->query("SELECT `band`.`tag` AS tagname FROM `band` JOIN `news_tag` ON(`band`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_band' AND `news`='$news' ORDER BY `band`.`tag`");
			while ($row = $this->db->fetchArray($result)) {
				array_push($retString, $row['tagname']);
			}
		}
		
		if ($type=="location") {
			$result = $this->db->query("SELECT `location`.`tag` AS tagname FROM `location` JOIN `news_tag` ON(`location`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_location' AND `news`='$news' ORDER BY `location`.`tag`");
			while ($row = $this->db->fetchArray($result)) {
				array_push($retString, $row['tagname']);
			}
		}
		
		return implode(";", $retString);
		
	}
	
	public function getTags($type, $news) {
		$ret = array();
		$news = $this->db->escapeString($news);
		
		if ($type=="band") {
			$result = $this->db->query("SELECT `id`, `band`.`tag` AS tagname FROM `band` JOIN `news_tag` ON(`band`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_band' AND `news`='$news' ORDER BY `band`.`tag`");
			while ($row = $this->db->fetchArray($result)) {
				array_push($ret, array('id'=>$row['id'], 'tag'=>$row['tagname']));
			}
		}
		
		if ($type=="location") {
			$result = $this->db->query("SELECT `id`, `location`.`tag` AS tagname FROM `location` JOIN `news_tag` ON(`location`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_location' AND `news`='$news' ORDER BY `location`.`tag`");
			while ($row = $this->db->fetchArray($result)) {
				array_push($ret, array('id'=>$row['id'], 'tag'=>$row['tagname']));
			}
		}
		
		return $ret;
	}
	
	public function displayTag($tagID, $type) {
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$tagID = $this->db->escapeString($tagID);
		if ($type=="location") {
			$articles = array();
			$tagName = "";
			$result = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$tagID'");
			while ($row = $this->db->fetchArray($result)) {
				$tagName = htmlentities($row['tag'], null, "UTF-8");
			}
			$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_location' ORDER BY `date` DESC");
			while ($row = $this->db->fetchArray($result)) {
				if ($auth->locationReadAllowed($row['location'], $role->getRole())) {
					$news = $row['news'];
					$headline = htmlentities($row['headline'], null, "UTF-8");
					$title = htmlentities($row['title'], null, "UTF-8");
					$date = date("d\.m\.Y", $row['date']);
					$location = $row['location'];
					$locationName = htmlentities($row['name'], null, "UTF-8");
					array_push($articles, array('news'=>$news, 'headline'=>$headline, 'title'=>$title, 'date'=>$date, 'location'=>$location, 'locationName'=>$locationName));
				}
			}
			require_once("template/cbe.location.tpl.php");
		}
		
		if ($type=="band") {
			$articles = array();
			$tagName = "";
			$result = $this->db->query("SELECT `tag` FROM `band` WHERE `id`='$tagID'");
			while ($row = $this->db->fetchArray($result)) {
				$tagName = htmlentities($row['tag'], null, "UTF-8");
			}
			$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_band' ORDER BY `date` DESC");
			while ($row = $this->db->fetchArray($result)) {
				if ($auth->locationReadAllowed($row['location'], $role->getRole())) {
					$news = $row['news'];
					$headline = htmlentities($row['headline'], null, "UTF-8");
					$title = htmlentities($row['title'], null, "UTF-8");
					$date = date("d\.m\.Y", $row['date']);
					$location = $row['location'];
					$locationName = htmlentities($row['name'], null, "UTF-8");
					array_push($articles, array('news'=>$news, 'headline'=>$headline, 'title'=>$title, 'date'=>$date, 'location'=>$location, 'locationName'=>$locationName));
				}
			}
			require_once("template/cbe.band.tpl.php");
		}
	}
	
	public function getImage() {
		return null;
	}
	
	public function getTitle() {
		return null;
	}
}
?>