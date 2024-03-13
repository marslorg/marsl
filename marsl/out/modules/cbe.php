<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/cbe/location.php");
include_once(dirname(__FILE__)."/cbe/band.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/../includes/slugify/vendor/autoload.php");

use Cocur\Slugify\Slugify;

class CBE implements Module {

	private $db;
	private $auth;
	private $role;
	private $basic;
	private $baseLinks = array();

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
		$this->basic = new Basic($db, $auth, $role);
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
    			$ret = $this->buildTagArray($row, $type, $ret);
			}
		}
		
		if ($type=="location") {
			$result = $this->db->query("SELECT `id`, `location`.`tag` AS tagname FROM `location` JOIN `news_tag` ON(`location`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_location' AND `news`='$news' ORDER BY `location`.`tag`");
			while ($row = $this->db->fetchArray($result)) {
				$ret = $this->buildTagArray($row, $type, $ret);
			}
		}
		
		return $ret;
	}

    private function buildTagArray($row, $type, $ret)
    {
        $uri = "";
		$config = new Configuration();
        if ($config->getEnableOldURIs()) {
        	$uri = "index.php?tag=".$row['id']."&scope=cbe_".$type;
        }
        else {
        	$slugify = new Slugify();
        	$tagPart = $slugify->slugify($row['tagname'])."-".$row['id'];
        	$uri = "tag/cbe_".$type."/".$tagPart;
        }
        array_push($ret, array('id'=>$row['id'], 'tag'=>$row['tagname'], 'uri'=>$uri));
		return $ret;
    }
	
	public function displayTag() {
		$tagID = $this->db->escapeString($this->getTagID());
		$type = $this->getScope();
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));

		if ($type=="cbe_location") {
			$articles = array();
			$tagName = "";
			$result = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$tagID'");
			while ($row = $this->db->fetchArray($result)) {
				$tagName = $this->basic->convertToHTMLEntities($row['tag']);
			}
			$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_location' ORDER BY `date` DESC");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationReadAllowed($row['location'], $this->role->getRole())) {
					$news = $row['news'];
					$headline = $this->basic->convertToHTMLEntities($row['headline']);
					$title = $this->basic->convertToHTMLEntities($row['title']);
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$location = $row['location'];
					$locationName = $this->basic->convertToHTMLEntities($row['name']);
					$link = $this->generateRelativeURI($location, $locationName, $news);
					array_push($articles, array('news'=>$news, 'headline'=>$headline, 'title'=>$title, 'date'=>$date, 'location'=>$location, 'locationName'=>$locationName, 'link'=>$link));
				}
			}
			require_once("template/cbe.location.tpl.php");
		}
		
		if ($type=="cbe_band") {
			$articles = array();
			$tagName = "";
			$result = $this->db->query("SELECT `tag` FROM `band` WHERE `id`='$tagID'");
			while ($row = $this->db->fetchArray($result)) {
				$tagName = $this->basic->convertToHTMLEntities($row['tag']);
			}
			$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_band' ORDER BY `date` DESC");
			while ($row = $this->db->fetchArray($result)) {
				if ($this->auth->locationReadAllowed($row['location'], $this->role->getRole())) {
					$news = $row['news'];
					$headline = $this->basic->convertToHTMLEntities($row['headline']);
					$title = $this->basic->convertToHTMLEntities($row['title']);
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$location = $row['location'];
					$locationName = $this->basic->convertToHTMLEntities($row['name']);
					$link = $this->generateRelativeURI($location, $locationName, $news);
					array_push($articles, array('news'=>$news, 'headline'=>$headline, 'title'=>$title, 'date'=>$date, 'location'=>$location, 'locationName'=>$locationName, 'link'=>$link));
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

	public function getRestfulURIPartFromOldURL() {
		$uri = "";
		$scope = $this->getScope();
		if (isset($scope)) {
			$uri = $uri."/".$scope;
			$tagID = $this->db->escapeString($this->getTagID());
			$explodedScope = explode("_", $scope);
			$subScope = $this->db->escapeString($explodedScope[1]);
			$tagName = "";
			$result = $this->db->query("SELECT `tag` FROM `".$subScope."` WHERE `id`='$tagID'");
			while ($row = $this->db->fetchArray($result)) {
				$tagName = $this->basic->convertToHTMLEntities($row['tag']);
			}
			$slugify = new Slugify();
			$tagPart = $slugify->slugify($tagName)."-".$tagID;
			$uri = $uri."/".$tagPart;
		}
		return $uri;
	}

	public function getOldURIPartFromRestfulURL() {
		$uri = "";

		$scope = $this->getScope();
		$tagID = $this->getTagID();
		if (isset($scope) && isset($tagID)) {
			$uri = "index.php?tag=".$tagID."&scope=".$scope;
		}

		return $uri;
	}

	private function getTagID() {
		$tagID = -1;

		if (isset($_GET['tag'])) {
			$tagID = $_GET['tag'];
		}
		else if (isset($_GET['request_uri']) && !empty($_GET['request_uri'])) {
			$requestURI = $_GET['request_uri'];
			$explodedRequestURI = explode('/', $requestURI);
			if (sizeof($explodedRequestURI) > 2) {
				$tag = $explodedRequestURI[2];
				$explodedTag = explode('-', $tag);
				$explodedTagSlugSize = sizeof($explodedTag);
				if ($explodedTagSlugSize > 0) {
					$tagID = $explodedTag[$explodedTagSlugSize-1];
				}
			}
		}

		return $tagID;
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

	private function generateRelativeURI($location, $locationName, $news) {
		if (array_key_exists($location, $this->baseLinks)) {
			$link = $this->baseLinks[$location];
		}
		else {
			$navi = new Navigation($this->db, $this->auth, $this->role);
			$uri = $navi->getRelativeURI($location, $locationName, true);
			$link = $uri."show=".$news."&action=read";
		}
		return $link;
	}
}
?>