<?php
include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/includes/dbsocket.php");
include_once(dirname(__FILE__)."/includes/config.inc.php");
include_once(dirname(__FILE__)."/user/auth.php");
include_once(dirname(__FILE__)."/user/role.php");

class GoogleNews {
	
	/*
	 * Initialize the RSS feed for the news articles.
	 */
	public function display() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		header("Content-type: application/rss+xml");
		$db = new DB();
		$db->connect();
		$auth = new Authentication($db);
		$role = new Role($db);
		if($auth->moduleReadAllowed("news", $role->getGuestRole())) {
			$config = new Configuration();
			$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
			$feedtitle = $config->getTitle();
			$feedlink = $config->getDomain();
			$feeddescription = "RSS Feed von ".$config->getTitle();
			$items = array();
			$result = $db->query("SELECT `news`.`location` AS `location`, `news`.`news` AS `news`, `news`.`teaser` AS `teaser`, `news`.`headline` AS `headline`, `news`.`title` AS `title`, `news`.`postdate` AS `postdate` FROM `news`
					JOIN `rights` ON (`rights`.`location` = `news`.`location`)
					JOIN `stdroles` ON (`rights`.`role` = `stdroles`.`guest`)
					WHERE `rights`.`read` = '1' AND `news`.`deleted` = '0' AND `news`.`visible` = '1' ORDER BY `postdate` DESC LIMIT 0,1000");
			while ($row = $db->fetchArray($result)) {
				$domain = $config->getDomain();
				$location = htmlentities($row['location'], null, "UTF-8");
				$news = htmlentities($row['news'], null, "UTF-8");
				$link = $domain."/index.php?id=".$location."&amp;show=".$news."&amp;action=read";
				$teaser = htmlentities($row['teaser'], null, "UTF-8");
				$title = htmlspecialchars($row['headline']).": ".htmlspecialchars($row['title']);
				$dateTime->setTimestamp($row['postdate']);
				$date = $dateTime->format("Y-m-d");
				array_push($items, array('link'=>$link, 'teaser'=>$teaser, 'title'=>$title, 'date'=>$date));
			}
			require_once("template/googlenews.tpl.php");
		}
		$db->close();
	}
}

$gn = new GoogleNews();
$gn->display();
?>