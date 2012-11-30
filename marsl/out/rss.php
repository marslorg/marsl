<?php
include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/includes/dbsocket.php");
include_once(dirname(__FILE__)."/includes/config.inc.php");
include_once(dirname(__FILE__)."/user/auth.php");
include_once(dirname(__FILE__)."/user/role.php");

class RSS {
	
	/*
	 * Initialize the RSS feed for the news articles.
	 */
	public function display() {
		header("Content-type: application/rss+xml");
		$db = new DB();
		$db->connect();
		$auth = new Authentication();
		$role = new Role();
		if($auth->moduleReadAllowed("news", $role->getGuestRole())) {
			$config = new Configuration();
			$feedtitle = $config->getTitle()." - RSS Feed";
			$feedlink = $config->getDomain();
			$feeddescription = "RSS Feed von ".$config->getTitle();
			$items = array();
			$result = $db->query("SELECT `news`.`location` AS `location`, `news`.`news` AS `news`, `news`.`teaser` AS `teaser`, `news`.`headline` AS `headline`, `news`.`title` AS `title`, `news`.`postdate` AS `postdate` FROM `news`
					JOIN `rights` ON (`rights`.`location` = `news`.`location`)
					JOIN `stdroles` ON (`rights`.`role` = `stdroles`.`guest`)
					WHERE `rights`.`read` = '1' AND `news`.`deleted` = '0' AND `news`.`visible` = '1' ORDER BY `postdate` DESC LIMIT 0,10");
			while ($row = mysql_fetch_array($result)) {
				$domain = $config->getDomain();
				$location = htmlentities($row['location']);
				$news = htmlentities($row['news']);
				$link = $domain."/index.php?id=".$location."&amp;show=".$news."&amp;action=read";
				$teaser = htmlentities($row['teaser']);
				$title = htmlspecialchars($row['headline']).": ".htmlspecialchars($row['title']);
				$date = date("D, d M Y H:i:s O", $row['postdate']);
				array_push($items, array('link'=>$link, 'teaser'=>$teaser, 'title'=>$title, 'date'=>$date));
			}
			require_once("template/rss.tpl.php");
		}
		$db->close();
	}
}

$rss = new RSS();
$rss->display();
?>