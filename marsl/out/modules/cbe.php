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
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}
	
	public function display() {
		
	}
	
	public function admin() {
		if ($this->auth->moduleAdminAllowed("cbe", $this->role->getRole())) {
			require_once("template/cbe.main.tpl.php");
			if (isset($_GET['action'])) {
				$band = new Band($this->db, $this->auth, $this->role);
				$club = new Location($this->db, $this->auth, $this->role);
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
				
				if ((strlen($tag)>0)&&(!$this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$tag' LIMIT 1"))) {
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
				
				if ((strlen($tag)>0)&&(!$this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' LIMIT 1"))) {
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
		$tagID = $this->db->escapeString($tagID);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($type=="location") {
			$articles = array();
			$tagName = "";
			$result = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$tagID'");
			while ($row = $this->db->fetchArray($result)) {
				$tagName = htmlentities($row['tag'], null, "ISO-8859-1");
			}
			$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_location' ORDER BY `date` DESC");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationReadAllowed($row['location'], $this->role->getRole())) {
					$news = $row['news'];
					$headline = htmlentities($row['headline'], null, "ISO-8859-1");
					$title = htmlentities($row['title'], null, "ISO-8859-1");
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$location = $row['location'];
					$locationName = htmlentities($row['name'], null, "ISO-8859-1");
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
				$tagName = htmlentities($row['tag'], null, "ISO-8859-1");
			}
			$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_band' ORDER BY `date` DESC");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationReadAllowed($row['location'], $this->role->getRole())) {
					$news = $row['news'];
					$headline = htmlentities($row['headline'], null, "ISO-8859-1");
					$title = htmlentities($row['title'], null, "ISO-8859-1");
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$location = $row['location'];
					$locationName = htmlentities($row['name'], null, "ISO-8859-1");
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