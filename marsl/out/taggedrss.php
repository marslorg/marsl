<?php
include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/includes/dbsocket.php");
include_once(dirname(__FILE__)."/includes/config.inc.php");
include_once(dirname(__FILE__)."/includes/basic.php");
include_once(dirname(__FILE__)."/user/auth.php");
include_once(dirname(__FILE__)."/user/role.php");

class RSS {
	
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
			$feedtitle = $config->getTitle()." - RSS Feed";
			$feedlink = $config->getDomain();
			$feeddescription = "RSS Feed von ".$config->getTitle();
			$items = array();
			$result = $db->query("SELECT `news`.`location` AS `location`, `news`.`news` AS `news`, `news`.`teaser` AS `teaser`, `news`.`headline` AS `headline`, `news`.`title` AS `title`, `news`.`postdate` AS `postdate`, `news`.`picture1` AS `picture1`, `news`.`picture2` AS `picture2` FROM `news`
					JOIN `rights` ON (`rights`.`location` = `news`.`location`)
					JOIN `stdroles` ON (`rights`.`role` = `stdroles`.`guest`)
					WHERE `rights`.`read` = '1' AND `news`.`deleted` = '0' AND `news`.`visible` = '1' ORDER BY `postdate` DESC LIMIT 0,10");
			while ($row = $db->fetchArray($result)) {
				$domain = $config->getDomain();
				$location = htmlentities($row['location'], null, "UTF-8");
				$news = htmlentities($row['news'], null, "UTF-8");
				$link = $domain."/index.php?id=".$location."&amp;show=".$news."&amp;action=read";
				$teaser = html_entity_decode($row['teaser'], null, "UTF-8");
				$title = htmlspecialchars($row['headline']).": ".htmlspecialchars($row['title']);
				$date = date("D, d M Y H:i:s O", $row['postdate']);
				
				$picID1 = $db->escapeString($row['picture1']);
				$picID2 = $db->escapeString($row['picture2']);
				$teaserPicture = "empty";
				$newsPicture = "empty";
				$result2 = $db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picID1'");
				while ($row2 = $db->fetchArray($result2)) {
					$teaserPicture = $domain."/news/".htmlentities($row2['url'], null, "UTF-8");
				}
				$result2 = $db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picID2'");
				while ($row2 = $db->fetchArray($result2)) {
					$newsPicture = $domain."/news/".htmlentities($row2['url'], null, "UTF-8");
				}
				
				$basic = new Basic($db);
				$modules = $basic->getModules();
				$moduleTags = array();
				foreach ($modules as $module) {
					include_once(dirname(__FILE__)."/modules/".$module['file'].".php");
					$class = new $module['class']($db);
					if ($class->isTaggable()) {
						$tagList = $class->getTagList();
						foreach($tagList as $tagType) {
							$typeID = $module['file']."_".$tagType['type'];
							$typeName = $tagType['text'];
							$tags = $class->getTags($tagType['type'], $news);
							foreach ($tags as $tag) {
								$teaser = $this->tagTeaser($teaser, $tag['tag']);
								$title = $this->tagTeaser($title, $tag['tag']);
								array_push($moduleTags, htmlspecialchars($tag['tag']));
							}
						}
					}
				}
				
				$teaser = htmlspecialchars($teaser);
				
				array_push($items, array('link'=>$link, 'teaser'=>$teaser, 'title'=>$title, 'date'=>$date, 'teaserPicture'=>$teaserPicture, 'newsPicture'=>$newsPicture, 'tags'=>$moduleTags));
			}
			require_once("template/taggedrss.tpl.php");
		}
		$db->close();
	}
	
	private function tagTeaser($text, $tag) {
		$hashtag = "#".strtolower(str_replace(" ", "", $tag));
		$text = preg_replace("/".$tag."/", $hashtag, $text, 1);
		return $text;
	}
}

$rss = new RSS();
$rss->display();
?>