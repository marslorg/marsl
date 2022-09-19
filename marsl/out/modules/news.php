<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");
include_once(dirname(__FILE__)."/../includes/mailer.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/../includes/web-push-php-6.0.5/vendor/autoload.php");

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class News implements Module {

	private $db;
	private $auth;
	private $role;
	private $PAGINATION_DISTANCE = 3;
	private $config;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
		$this->config = new Configuration();
	}
	
	/*
	 * Displays the admin interface of the news module.
	 */
	public function admin() {
		$navigation = new Navigation($this->db, $this->auth, $this->role);
		$basic = new Basic($this->db, $this->auth, $this->role);
		$user = new User($this->db, $this->role);
		$modules = $basic->getModules();
		$moduleTags = array();

		$dateTime = new DateTime("now", new DateTimeZone($this->config->getTimezone()));

		$domain = $this->config->getDomain();

		foreach ($modules as $module) {
			include_once(dirname(__FILE__)."/".$module['file'].".php");
			$class = new $module['class']($this->db, $this->auth, $this->role);
			if ($class->isTaggable()) {
				$tagList = $class->getTagList();
				foreach($tagList as $tagType) {
					$typeID = $module['file']."_".$tagType['type'];
					$typeName = $tagType['text'];
					array_push($moduleTags, array('type'=>$typeID, 'name'=>$typeName));
				}
			}	
		}
		if ($this->auth->moduleAdminAllowed("news", $this->role->getRole())) {
			require_once("template/news.navigation.tpl.php");
			if(!isset($_GET['action'])) {
				/*
				 * TODO Tag-Editing
				 */
				$headline = "";
				$corrected = false;
				$title = "";
				$category = "";
				$day = "DD";
				$month = "MM";
				$year = "YYYY";
				$teaser = "";
				$text = "";
				$city = "";
				$tmpModuleTags = array();
				foreach ($moduleTags as $moduleTag) {
					$moduleTag['tags'] = "";
					array_push($tmpModuleTags, $moduleTag);
				}
				$moduleTags = $tmpModuleTags;
				$new = true;
				if (isset($_POST['action'])) {
					$new = false;
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$failed = false;

						if ($failed) {
							$headline = $basic->convertToHTMLEntities($_POST['headline']);
							$title = $basic->convertToHTMLEntities($_POST['title']);
							$category = $_POST['category'];
							$day = $basic->convertToHTMLEntities($_POST['day']);
							$month = ($_POST['month']);
							$year = $basic->convertToHTMLEntities($_POST['year']);
							$teaser = $basic->cleanHTML($_POST['teaser']);
							$text = $basic->cleanHTML($_POST['text']);
							$city = $basic->cleanHTML($_POST['city']);
							$corrected = isset($_POST['corrected']);
							$tmpModuleTags = array();
							foreach ($moduleTags as $moduleTag) {
								$moduleTag['tags'] = $basic->convertToHTMLEntities($_POST[$moduleTag['type']]);
								array_push($tmpModuleTags, $moduleTag);
							}
							$moduleTags = $tmpModuleTags;
						}
						else {
							$author = $this->db->escapeString($user->getID());
							$authorIP = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
							$headline = $this->db->escapeString($_POST['headline']);
							$title = $this->db->escapeString($_POST['title']);
							$location = $this->db->escapeString($_POST['category']);
							$corrected = 0;
							if (isset($_POST['corrected'])) {
								$corrected = 1;
							}
							$tmpModuleTags = array();
							foreach ($moduleTags as $moduleTag) {
								$moduleTag['tags'] = $_POST[$moduleTag['type']];
								array_push($tmpModuleTags, $moduleTag);
							}
							$moduleTags = $tmpModuleTags;
							$date = "";
							$postdate = time();
							$city = $this->db->escapeString($_POST['city']);
							if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
								$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
							}
							else {
								$date = time();
							}
							$teaser = $this->db->escapeString($basic->cleanHTML($_POST['teaser']));
							$text = $this->db->escapeString($basic->cleanHTML($_POST['text']));
							if ($this->auth->locationAdminAllowed($location, $this->role->getRole())||$this->auth->locationExtendedAllowed($location, $this->role->getRole())) {

								$picture1 = 0;
								if (isset($_POST['picture1'])) {
									$picture1 = $this->db->escapeString($_POST['picture1']);
								}
								
								$picture2 = 0;
								if (isset($_POST['picture2'])) {
									$picture2 = $this->db->escapeString($_POST['picture2']);
									$result = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picture2'");
									while ($row = $this->db->fetchArray($result)) {
										$fileName = $row['url'];
										$fileLink = "../news/".$fileName;
										$oldIMG = imagecreatefromjpeg($fileLink);
										$newIMG = imagecreatetruecolor(640, 320);
										$pic2X = $_POST['pic2X'];
										$pic2Y = $_POST['pic2Y'];
										$pic2W = $_POST['pic2W'];
										$pic2H = $_POST['pic2H'];
										unlink($fileLink);
										$fileName = "r".$fileName;
										$fileLink = "../news/".$fileName;
										$this->db->query("UPDATE `news_picture` SET `url`='$fileName' WHERE `picture`='$picture2'");
										imagecopyresampled($newIMG, $oldIMG, 0, 0, $pic2X, $pic2Y, 640, 320, $pic2W, $pic2H);
										ImageDestroy($oldIMG);
										imagejpeg($newIMG, $fileLink);
									}
								}

								$this->db->query("INSERT INTO `news`(`author`,`author_ip`,`headline`,`title`,`teaser`,`text`,`picture1`,`picture2`,`date`,`visible`,`deleted`,`location`,`city`,`postdate`,`corrected`) 
								VALUES('$author','$authorIP','$headline','$title','$teaser','$text','$picture1','$picture2','$date','0','0','$location','$city','$postdate','$corrected')");
								$newsID = $this->db->lastInsertedID();
								foreach ($moduleTags as $moduleTag) {
									
									$type = explode("_", $moduleTag['type']);
									$file = $type[0];
									$scope = $type[1];
									$module = $basic->getModule($file);
									include_once(dirname(__FILE__)."/".$file.".php");
									$class = new $module['class']($this->db, $this->auth, $this->role);
									$class->addTags($moduleTag['tags'], $scope, $newsID);

								}
								$administrators = $user->getAdminUsers();
								foreach ($administrators as $administrator) {
									$administratorRole = $this->role->getRolebyUser($administrator);
									if ($this->auth->moduleAdminAllowed("news", $administratorRole)) {
										if ($this->auth->locationAdminAllowed($location, $administratorRole)) {
											$mailer = new Mailer($this->db, $this->role);
											$mailer->sendNewArticleMail($administrator, $this->auth);
										}
									}
								}
								$headline = "";
								$title = "";
								$category = "";
								$day = "DD";
								$month = "MM";
								$year = "YYYY";
								$teaser = "";
								$text = "";
								$city = "";
								$corrected = false;
								$tmpModuleTags = array();
								foreach ($moduleTags as $moduleTag) {
									$moduleTag['tags'] = "";
									array_push($tmpModuleTags, $moduleTag);
								}
								$moduleTags = $tmpModuleTags;
							}
						}
					}
				}
				$locations = array();
				$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='news' AND `type` IN ('1','2') ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['id'], $this->role->getRole())||$this->auth->locationExtendedAllowed($row['id'], $this->role->getRole())) {
						array_push($locations,array('location'=>$basic->convertToHTMLEntities($row['id']),'name'=>$basic->convertToHTMLEntities($row['name'])));
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/news.write.tpl.php");
			}
			else if ($_GET['action']=="queue") {
				$this->doGetActions();
				$news = array();
				$result = $this->db->query("SELECT
				`news`, `author`, `corrected`, `author_ip`, `location`, `headline`, `title`, `teaser`, `text`, `picture1`, `picture2`, `city`, `date`, `postdate`, `url`, `photograph`
				FROM `news`
				LEFT JOIN `news_picture` ON `picture`=`picture1`
				WHERE `visible`='0' AND `deleted`='0'");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
						$id = $basic->convertToHTMLEntities($row['news']);
						$author = $row['author'];
						$corrected = $row['corrected'];
						$authorName = $basic->convertToHTMLEntities($user->getAcronymbyID($author, $this->auth));
						$authorIP = $basic->convertToHTMLEntities($row['author_ip']);
						$location = $basic->convertToHTMLEntities($navigation->getNamebyID($row['location']));
						$headline = $basic->convertToHTMLEntities($row['headline']);
						$title = $basic->convertToHTMLEntities($row['title']);
						$teaser = $row['teaser'];
						$text = $row['text'];
						$picID1 = $this->db->escapeString($row['picture1']);
						$picID2 = $this->db->escapeString($row['picture2']);
						$picture1 = $basic->convertToHTMLEntities($row['url']);
						if (empty($picture1)) {
							$picture1 = "empty";
						}
						$photograph1 = "";
						if (!empty($row['photograph'])) {
							$photograph1 = "<br />Foto: ".$basic->convertToHTMLEntities($row['photograph']);
						}
						$city = $basic->convertToHTMLEntities($row['city']);
						$dateTime->setTimestamp($row['date']);
						$date = $dateTime->format("d\.m\.Y");
						$dateTime->setTimestamp($row['postdate']);
						$postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
						array_push($news,array('author'=>$authorName,'authorIP'=>$authorIP,'news'=>$id, 'location'=>$location, 'headline'=>$headline, 'title'=>$title, 'teaser'=>$teaser, 'picture1'=>$picture1, 'photograph1'=>$photograph1, 'city'=>$city, 'date'=>$date, 'postdate'=>$postdate, 'text'=>$text, 'corrected'=>$corrected));
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/news.queue.tpl.php");
			}
			else if ($_GET['action']=="edit") {
				$id = $this->db->escapeString($basic->convertToHTMLEntities($_GET['id']));
				$result = $this->db->query("SELECT `corrected`, `headline`, `title`, `location`, `date`, `teaser`, `text`, `picture1`, `picture2`, `city` FROM `news` WHERE `news`='$id' AND `deleted`='0'");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
						$corrected = $row['corrected'];
						$headline = $basic->convertToHTMLEntities($row['headline']);
						$title = $basic->convertToHTMLEntities($row['title']);
						$category = $basic->convertToHTMLEntities($row['location']);
						$dateTime->setTimestamp($row['date']);
						$day = $dateTime->format("d");
						$month = $dateTime->format("m");
						$year = $dateTime->format("Y");
						$teaser = $row['teaser'];
						$text = $row['text'];
						$picture1 = $row['picture1'];
						$picture2 = $row['picture2'];
						$city = $basic->convertToHTMLEntities($row['city']);
						$tmpModuleTags = array();
						foreach ($moduleTags as $moduleTag) {

							$type = explode("_", $moduleTag['type']);
							$file = $type[0];
							$scope = $type[1];
							$module = $basic->getModule($file);
							include_once(dirname(__FILE__)."/".$file.".php");
							$class = new $module['class']($this->db, $this->auth, $this->role);
							$moduleTag['tags'] = $basic->convertToHTMLEntities($class->getTagString($scope, $id));

							array_push($tmpModuleTags, $moduleTag);
						}
						$moduleTags = $tmpModuleTags;
						$new = true;
						$failed = false;
						if (isset($_POST['action'])) {
							$new = false;
							if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
								$failed = false;

								if ($failed) {
									$headline = $basic->convertToHTMLEntities($_POST['headline']);
									$title = $basic->convertToHTMLEntities($_POST['title']);
									$category = $_POST['category'];
									$day = $basic->convertToHTMLEntities($_POST['day']);
									$month = $basic->convertToHTMLEntities($_POST['month']);
									$year = $basic->convertToHTMLEntities($_POST['year']);
									$teaser = $basic->cleanHTML($_POST['teaser']);
									$text = $basic->cleanHTML($_POST['text']);
									$city = $basic->cleanHTML($_POST['city']);
									$corrected = isset($_POST['corrected']);
									$tmpModuleTags = array();
									foreach ($moduleTags as $moduleTag) {
										$moduleTag['tags'] = $basic->convertToHTMLEntities($_POST[$moduleTag['type']]);
										array_push($tmpModuleTags, $moduleTag);
									}
									$moduleTags = $tmpModuleTags;
								}
								else {
									$author = $this->db->escapeString($user->getID());
									$authorIP = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
									$headline = $this->db->escapeString($_POST['headline']);
									$title = $this->db->escapeString($_POST['title']);
									$location = $this->db->escapeString($_POST['category']);
									if (isset($_POST['corrected'])) {
										$corrected = 1;
									}
									else {
										$corrected = 0;
									}
									$date = "";
									$postdate = time();
									$city = $this->db->escapeString($_POST['city']);
									if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
										$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
									}
									else {
										$date = time();
									}
									$teaser = $this->db->escapeString($basic->cleanHTML($_POST['teaser']));
									$text = $this->db->escapeString($basic->cleanHTML($_POST['text']));
									
									if (isset($_POST['picture1'])) {
										$picture1 = $this->db->escapeString($_POST['picture1']);
									}
									
									if (isset($_POST['picture2'])) {
										$picture2 = $this->db->escapeString($_POST['picture2']);
										$result = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picture2'");
										while ($row = $this->db->fetchArray($result)) {
											$fileName = $row['url'];
											$fileLink = "../news/".$fileName;
											$oldIMG = imagecreatefromjpeg($fileLink);
											$newIMG = imagecreatetruecolor(640, 320);
											$pic2X = $_POST['pic2X'];
											$pic2Y = $_POST['pic2Y'];
											$pic2W = $_POST['pic2W'];
											$pic2H = $_POST['pic2H'];
											unlink($fileLink);
											$fileName = "r".$fileName;
											$fileLink = "../news/".$fileName;
											$this->db->query("UPDATE `news_picture` SET `url`='$fileName' WHERE `picture`='$picture2'");
											imagecopyresampled($newIMG, $oldIMG, 0, 0, $pic2X, $pic2Y, 640, 320, $pic2W, $pic2H);
											ImageDestroy($oldIMG);
											imagejpeg($newIMG, $fileLink);
										}
									}
									
									$tmpModuleTags = array();
									foreach ($moduleTags as $moduleTag) {
										$moduleTag['tags'] = $_POST[$moduleTag['type']];
										array_push($tmpModuleTags, $moduleTag);
									}
									$moduleTags = $tmpModuleTags;
									$admin = $this->db->escapeString($user->getID());
									$adminIP = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
									$this->db->query("UPDATE `news` SET `date`='$date', `admin`='$admin', `admin_ip`='$adminIP', `headline`='$headline', `title`='$title', `teaser`='$teaser', `text`='$text', `picture1`='$picture1', `picture2`='$picture2', `location`='$location', `city`='$city', `corrected`='$corrected' WHERE `news`='$id'"); 
									foreach ($moduleTags as $moduleTag) {

										$type = explode("_", $moduleTag['type']);
										$file = $type[0];
										$scope = $type[1];
										$module = $basic->getModule($file);
										include_once(dirname(__FILE__)."/".$file.".php");
										$class = new $module['class']($this->db, $this->auth, $this->role);
										$class->addTags($moduleTag['tags'], $scope, $id);

									}
									$headline = $basic->convertToHTMLEntities($_POST['headline']);
									$title = $basic->convertToHTMLEntities($_POST['title']);
									$category = $_POST['category'];
									$day = $basic->convertToHTMLEntities($_POST['day']);
									$month = $basic->convertToHTMLEntities($_POST['month']);
									$year = $basic->convertToHTMLEntities($_POST['year']);
									$teaser = $basic->cleanHTML($_POST['teaser']);
									$text = $basic->cleanHTML($_POST['text']);
									$city = $basic->cleanHTML($_POST['city']);
									$tmpModuleTags = array();
									foreach ($moduleTags as $moduleTag) {
										$moduleTag['tags'] = $basic->convertToHTMLEntities($_POST[$moduleTag['type']]);
										array_push($tmpModuleTags, $moduleTag);
									}
									$moduleTags = $tmpModuleTags;
								}
							}
						}
						$locations = array();
						$result2 = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='news' AND `type` IN('1','2') ORDER BY `pos`");
						while ($row2 = $this->db->fetchArray($result2)) {
							if ($this->auth->locationAdminAllowed($row2['id'], $this->role->getRole())||$this->auth->locationExtendedAllowed($row2['id'], $this->role->getRole())) {
								array_push($locations,array('location'=>$basic->convertToHTMLEntities($row2['id']),'name'=>$basic->convertToHTMLEntities($row2['name'])));
							}
						}
						$authTime = time();
						$authToken = $this->auth->getToken($authTime);
						require_once("template/news.edit.tpl.php");
					}
				}
			}
			else if ($_GET['action']=="news") {
				$this->doGetActions();
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$result = $this->db->query("SELECT COUNT(`visible`) AS rowcount FROM `news` WHERE `visible`='1' AND `deleted`='0'");
				$pages = $this->db->getRowCount($result)/10;
				$start = $page*10-10;
				$end = 10;
				$news = array();
				$start = $this->db->escapeString($start);
				$result = $this->db->query("SELECT
				`news`, `corrected`, `author`, `author_ip`, `location`, `date`, `postdate`, `headline`, `title`, `picture1`, `city`, `teaser`, `text`, `url`, `photograph`
				FROM `news`
				LEFT JOIN `news_picture` ON `picture`=`picture1`
				WHERE `visible`='1' AND `deleted`='0' ORDER BY `postdate` DESC LIMIT $start,$end");
				while ($row = $this->db->fetchArray($result)) {
					$id = $basic->convertToHTMLEntities($row['news']);
					$corrected = $row['corrected'];
					$author = $basic->convertToHTMLEntities($user->getAcronymbyID($row['author'], $this->auth));
					$authorIP = $basic->convertToHTMLEntities($row['author_ip']);
					$category = $row['location'];
					$editLink = ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole()));
					$location = $navigation->getNamebyID($category);
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$dateTime->setTimestamp($row['postdate']);
					$postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
					$headline = $basic->convertToHTMLEntities($row['headline']);
					$title = $basic->convertToHTMLEntities($row['title']);
					$picture1 = $basic->convertToHTMLEntities($row['url']);
					if (empty($picture1)) {
						$picture1 = "empty";
					}
					$photograph1 = "";
					if (!empty($row['photograph'])) {
						$photograph1 = "<br />Foto: ".$basic->convertToHTMLEntities($row['photograph']);
					}
					$city = $basic->convertToHTMLEntities($row['city']);
					$teaser = $row['teaser'];
					$text = $row['text'];
					array_push($news,array('text'=>$text,'teaser'=>$teaser,'city'=>$city,'picture1'=>$picture1, 'photograph1'=>$photograph1, 'title'=>$title,'headline'=>$headline,'id'=>$id,'editLink'=>$editLink,'date'=>$date,'postdate'=>$postdate,'location'=>$location,'author'=>$author,'authorIP'=>$authorIP, 'corrected'=>$corrected));
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/news.tpl.php");
			}
			else if ($_GET['action']=="details") {
				$this->doGetActions();
				$id = $this->db->escapeString($_GET['id']);
				$result = $this->db->query("SELECT
				`visible`, `location`, `author`, `corrected`, `author_ip`, `headline`, `title`, `teaser`, `text`, `city`, `date`, `postdate`,
				`news_picture1`.`url` AS `url1`, `news_picture1`.`photograph` AS `photograph1`,
				`news_picture2`.`url` AS `url2`, `news_picture2`.`photograph` AS `photograph2`, `news_picture2`.`subtitle` AS `subtitle`
				FROM `news`
				LEFT JOIN `news_picture` AS `news_picture1` ON `news_picture1`.`picture` = `picture1`
				LEFT JOIN `news_picture` AS `news_picture2` ON `news_picture2`.`picture` = `picture2`
				WHERE `news`='$id' AND `deleted`='0'");
				while ($row = $this->db->fetchArray($result)) {
					$submitLink = (($row['visible']==0)&&($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())));
					$editLink = ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole()));
					$author = $row['author'];
					$corrected = $row['corrected'];
					$authorName = $basic->convertToHTMLEntities($user->getAcronymbyID($author, $this->auth));
					$id = $basic->convertToHTMLEntities($id);
					$authorIP = $basic->convertToHTMLEntities($row['author_ip']);
					$location = $basic->convertToHTMLEntities($navigation->getNamebyID($row['location']));
					$headline = $basic->convertToHTMLEntities($row['headline']);
					$title = $basic->convertToHTMLEntities($row['title']);
					$teaser = $row['teaser'];
					$text = $row['text'];
					$picture1 = $basic->convertToHTMLEntities($row['url1']);
					if (empty($picture1)) {
						$picture1 = "empty";
					}
					$photograph1 = "";
					if (!empty($row['photograph1'])) {
						$photograph1 = "<br />Foto: ".$basic->convertToHTMLEntities($row['photograph1']);
					}
					$picture2 = $basic->convertToHTMLEntities($row['url2']);
					if (empty($picture2)) {
						$picture2 = "empty";
					}
					$subtitle2 = $basic->convertToHTMLEntities($row['subtitle']);
					$photograph2 = "";
					if (!empty($row['photograph2'])) {
						$photograph2 = " Foto: ".$basic->convertToHTMLEntities($row['photograph2']);
					}
					$city = $basic->convertToHTMLEntities($row['city']);
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$dateTime->setTimestamp($row['postdate']);
					$postdate = $dateTime->format("\a\m d\. M Y \u\m H\:i\:s");
					$authTime = time();
					$authToken = $this->auth->getToken($authTime);
					require_once("template/news.details.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Displays the frontend of a news module.
	 */
	public function display() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$user = new User($this->db, $this->role);

		$dateTime = new DateTime("now", new DateTimeZone($this->config->getTimezone()));
		
		if ($this->auth->moduleReadAllowed("news", $this->role->getRole())) {
			if (!isset($_GET['action'])) {
				$location = "";
				if (isset($_GET['id'])) {
					$location = $_GET['id'];
				}
				else {
					$location = $basic->getHomeLocation();
				}
				$location = $this->db->escapeString($location);
				$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = $this->db->fetchArray($result)) {
					$location = $this->db->escapeString($row['maps_to']);
				}
			    list($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage) = $this->getPagination($location);
				$news = array();
				$result = $this->db->query("SELECT
				`date`, `postdate`, `author`, `teaser`, `text`, `city`, `headline`, `title`, `news`, `url`, `photograph`
				FROM `news`
				LEFT JOIN `news_picture` ON `picture`=`picture1`
				WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `postdate` DESC LIMIT $start,$end");
				while ($row=$this->db->fetchArray($result)) {
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$dateTime->setTimestamp($row['postdate']);
					$postdate = $dateTime->format("d\.m\.Y");
					$author = $row['author'];
					$authorName = strtolower($basic->convertToHTMLEntities($user->getAcronymbyID($author, $this->auth)));
					$picture1 = $basic->convertToHTMLEntities($row['url']);
                    if (empty($picture1)) {
                        $picture1 = "empty";
                    }
					$photograph1 = "";
					if (!empty($row['photograph'])) {
						$photograph1 = "<br />Foto: ".$basic->convertToHTMLEntities($row['photograph']);
					}
					$teaser = $this->nofollowOutboundLinks($row['teaser']);
					$text = $this->nofollowOutboundLinks($row['text']);
					$city = $basic->convertToHTMLEntities($row['city']);
					$headline = $basic->convertToHTMLEntities($row['headline']);
					$title = $basic->convertToHTMLEntities($row['title']);
					$id = $basic->convertToHTMLEntities($row['news']);
					array_push($news,array('city'=>$city,'headline'=>$headline,'title'=>$title,'id'=>$id,'date'=>$date,'postdate'=>$postdate,'author'=>$authorName,'picture1'=>$picture1, 'photograph1'=>$photograph1, 'teaser'=>$teaser,'text'=>$text));
				}
				require_once("template/news.main.tpl.php");
			}
			else if ($_GET['action']=="read") {
				$location = $this->db->escapeString($_GET['id']);
				$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = $this->db->fetchArray($result)) {
					$location = $this->db->escapeString($row['maps_to']);
				}
				$news = $this->db->escapeString($_GET['show']);
				$result = $this->db->query("SELECT
				`date`, `author`, `teaser`, `text`, `city`, `headline`, `title`,
				`news_picture1`.`url` AS `url1`, `news_picture1`.`photograph` AS `photograph1`,
				`news_picture2`.`url` AS `url2`, `news_picture2`.`photograph` AS `photograph2`, `news_picture2`.`subtitle` AS `subtitle`
				FROM `news`
				LEFT JOIN `news_picture` AS `news_picture1` ON `news_picture1`.`picture` = `picture1`
				LEFT JOIN `news_picture` AS `news_picture2` ON `news_picture2`.`picture` = `picture2`
				WHERE `location`='$location' AND `news`='$news' AND `visible`='1' AND `deleted`='0'");
				while ($row = $this->db->fetchArray($result)) {
					$dateTime->setTimestamp($row['date']);
					$date = $dateTime->format("d\.m\.Y");
					$author = $row['author'];
					$authorName = strtolower($basic->convertToHTMLEntities($user->getAcronymbyID($author, $this->auth)));
					$picture1 = $basic->convertToHTMLEntities($row['url1']);
					if (empty($picture1)) {
						$picture1 = "empty";
					}
					$photograph1 = "";
					if (!empty($row['photograph1'])) {
						$photograph1 = "<br />Foto: ".$basic->convertToHTMLEntities($row['photograph1']);
					}
					$picture2 = $basic->convertToHTMLEntities($row['url2']);
					if (empty($picture2)) {
						$picture2 = "empty";
					}
					$subtitle2 = $basic->convertToHTMLEntities($row['subtitle']);
					$photograph2 = "";
					if (!empty($row['photograph2'])) {
						$photograph2 = " Foto: ".$basic->convertToHTMLEntities($row['photograph2']);
					}
					$teaser = $this->nofollowOutboundLinks($row['teaser']);
					$text = $this->nofollowOutboundLinks($row['text']);
					$city = $basic->convertToHTMLEntities($row['city']);
					$headline = $basic->convertToHTMLEntities($row['headline']);
					$title = $basic->convertToHTMLEntities($row['title']);
					$url = $this->config->getDomain()."/index.php?id=".$_GET['id']."&amp;show=".$_GET['show']."&amp;action=read";
					
					$modules = $basic->getModules();
					$moduleTags = array();
					foreach ($modules as $module) {
						include_once(dirname(__FILE__)."/".$module['file'].".php");
						$class = new $module['class']($this->db, $this->auth, $this->role);
						if ($class->isTaggable()) {
							$tagList = $class->getTagList();
							foreach($tagList as $tagType) {
								$typeID = $module['file']."_".$tagType['type'];
								$typeName = $tagType['text'];
								array_push($moduleTags, array('type'=>$typeID, 'name'=>$typeName, 'tags'=>$class->getTags($tagType['type'], $news)));
							}
						}
					}
					
					require_once("template/news.tpl.php");
				}
			}
		}
	}

    private function getPagination($location) {
        $result = $this->db->query("SELECT COUNT(`visible`) AS rowcount FROM `news` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
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
	 * Executes some smaller functions on a news article.
	 */
	private function doGetActions() {
		$user = new User($this->db, $this->role);
		if (isset($_GET['do'])) {
			if ($_GET['do']=="submit") {
    			$this->submitArticleToFrontend($user);
			}
			else if ($_GET['do']=="del") {
    			$this->deleteArticle();
			}
		}
	}

    private function deleteArticle()
    {
        if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
        	$id = $this->db->escapeString($_GET['id']);
        	$result = $this->db->query("SELECT `location` FROM `news` WHERE `news`='$id'");
        	while ($row = $this->db->fetchArray($result)) {
        		if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
        			$this->db->query("UPDATE `news` SET `deleted`='1' WHERE `news`='$id'");
        		}
        	}
        }
    }

    private function submitArticleToFrontend($user) {
        if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
        	$id = $this->db->escapeString($_GET['id']);
        	$result = $this->db->query("SELECT `location`, `headline`, `title`, `teaser` FROM `news` WHERE `news`='$id'");
        	while ($row = $this->db->fetchArray($result)) {
        		if ($this->auth->locationAdminAllowed($row['location'], $this->role->getRole())) {
        			$admin = $this->db->escapeString($user->getID());
        			$adminIP = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
					$this->db->query("UPDATE `news` SET `visible`='1', `admin`='$admin', `admin_ip`='$adminIP' WHERE `news`='$id'");
                    if ($this->auth->locationReadAllowed($row['location'], $this->role->getGuestRole())) {
						$messageTitle = $row['headline'].": ".$row['title'];
						$teaser = str_replace("<br />", "\n", $row['teaser']);
						$teaser = strip_tags($teaser);
						$teaser = html_entity_decode($teaser);
						$uri = "index.php?id=".$row['location']."&show=".$id."&action=read";
						$this->expoPushArticle($uri, $messageTitle);
                        $this->webPushArticle($uri, $messageTitle, $teaser);
                    }
        		}
        	}
        }
	}

	private function expoPushArticle($uri, $messageTitle) {
		$result = $this->db->query("SELECT `pushtoken` FROM `pushtoken` WHERE `type` = 'expo'");
		$multipleMessagesArray = array();
		$multipleMessagesArrayIdx = 0;
		$multipleMessagesArray[$multipleMessagesArrayIdx] = array();
		$currentMessageIdx = 0;
		while ($row = $this->db->fetchArray($result)) {
			if ($currentMessageIdx >= 100) {
				$multipleMessagesArrayIdx++;
				$multipleMessagesArray[$multipleMessagesArrayIdx] = array();
				$currentMessageIdx = 0;
			}
			array_push($multipleMessagesArray[$multipleMessagesArrayIdx], $this->buildExpoPushArray($row['pushtoken'], $uri, $messageTitle));
			$currentMessageIdx++;
		}

		for ($i = 0; $i <= $multipleMessagesArrayIdx; $i++) {
			list($httpResult, $body) = $this->sendExpoPushArray($multipleMessagesArray[$i]);
			$this->handleExpoResponse($httpResult, $body);
		}
	}

	private function handleExpoResponse($httpResult, $body) {
		if ($httpResult == 200) {
			$response = json_decode($body);
			$dataArray = $response->data;
			foreach ($dataArray as $data) {
				if ($data->status == "error") {
					$stripOutExpoPushTokenFirstPartArray = explode("ExponentPushToken[", $data->message);
					if (sizeof($stripOutExpoPushTokenFirstPartArray) > 1) {
						$messageWithoutExpoPushTokenFirstPart = $stripOutExpoPushTokenFirstPartArray[1];
						$tokenArray = explode("]", $messageWithoutExpoPushTokenFirstPart);
						if (sizeof($tokenArray) > 0) {
							$token = $tokenArray[0];
							$expoToken = "ExponentPushToken[".$token."]";
							$expoToken = $this->db->escapeString($expoToken);
                        	$this->db->query("DELETE FROM `pushtoken` WHERE `pushtoken`='$expoToken'");
						}
					}
				}
			}
		}
	}

	private function sendExpoPushArray($expoPushArray) {
		$expoPushJSON = json_encode($expoPushArray);
		$ch = curl_init("https://exp.host/--/api/v2/push/send");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $expoPushJSON);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($expoPushJSON)));
		$response = curl_exec($ch);
		$httpResult = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$body = substr($response, $header_size);
        curl_close($ch);
		return array($httpResult, $body);
	}

	private function buildExpoPushArray($pushToken, $uri, $messageTitle) {
		$expoArray = array();
		$expoArray['to'] = $pushToken;
		$expoArray['title'] = "Neuer Artikel auf " . $this->config->getTitle();
		$expoArray['body'] = $messageTitle;
		$expoArray['sound'] = "default";

		$payloadArray = array();
		$payloadArray['uri'] = $uri;
		$payloadJSON = json_encode($payloadArray);

		$expoArray['data'] = $payloadJSON;
		return $expoArray;
	}
	
	private function webPushArticle($uri, $messageTitle, $teaser) {
        if (extension_loaded('gmp')) {
            $payloadJSON = $this->buildPayloadJSON($uri, $messageTitle, $teaser);
            $webPush = $this->buildWebPushObject($payloadJSON);

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    $statusCode = $report->getResponse()->getStatusCode();
                    if ($statusCode == 410 || $statusCode == 404) {
                        $endpoint = $report->getRequest()->getUri()->__toString();
                        $endpoint = $this->db->escapeString($endpoint);
                        $this->db->query("DELETE FROM `pushtoken` WHERE `endpoint`='$endpoint'");
                    }
                }
            }
        }
	}

	private function buildPayloadJSON($uri, $messageTitle, $teaser) {
		$url = $this->config->getDomain().$this->config->getBasePath()."/".$uri;
		$icon = $this->config->getDomain().$this->config->getBasePath()."/includes/graphics/icon_512x512.png";

		$payloadArray = array();
		$payloadArray['title'] = $messageTitle;
		$payloadArray['body'] = $teaser;
		$payloadArray['icon'] = $icon;
		$payloadArray['data'] = $url;
		$payloadArray['requireInteraction'] = true;

		$payloadJSON = json_encode($payloadArray);
		return $payloadJSON;
	}

	private function buildWebPushObject($payloadJSON) {
		$auth = [
			'VAPID' => [
				'subject' => $this->config->getDomain().$this->config->getBasePath(),
				'publicKey' => $this->config->getWebPushPublicKey(),
				'privateKey' => $this->config->getWebPushPrivateKey()
			]
		];
		$webPush = new WebPush($auth);
		$result = $this->db->query("SELECT `endpoint`, `key`, `auth` FROM `pushtoken` WHERE `type` = 'webpush'");
		while ($row = $this->db->fetchArray($result)) {
			$subscription = Subscription::create([
				'endpoint'=>$row['endpoint'],
				'keys'=>['p256dh'=>$row['key'], 'auth'=>$row['auth']]
			]);
			$webPush->queueNotification($subscription, $payloadJSON);
		}
		return $webPush;
	}
	
	/*
	 * Saves a news picture.
	 */
	private function savePicture($picture, $maxWidth, $maxHeight, $strictWidth, $strictHeight) {
		mt_srand(time());
		$random = mt_rand();
		$dir = "../news/";
		if (move_uploaded_file($picture['tmp_name'], $dir.$random.$picture['name'])) {
			$picinfo = @getimagesize($dir.$random.$picture['name']);
			if (getimagesize($dir.$random.$picture['name'])) {
				$width = $picinfo[0];
				$height = $picinfo[1];
				if (($width>$maxWidth)&&($maxWidth>0)) {
					unlink($dir.$random.$picture['name']);
					return false;
				}
				else if (($height>$maxHeight)&&($maxHeight>0)) {
					unlink($dir.$random.$picture['name']);
					return false;
				}
				else if (($width!=$strictWidth)&&($strictWidth>0)) {
					unlink($dir.$random.$picture['name']);
					return false;
				}
				else if (($height!=$strictHeight)&&($strictHeight>0)) {
					unlink($dir.$random.$picture['name']);
					return false;
				}
				else {
					chmod($dir.$random.$picture['name'], 0644);
					return $random.$picture['name'];
				}
			}
			else {
				unlink($dir.$random.$picture['name']);
				return false;
			}
		}
		else {
			return "empty";
		}
	}
	
	/*
	 * Interface method stub.
	*/
	public function isSearchable() {
		return true;
	}
	
	/*
	 * Returns the fulltext searchable types of this module.
	*/
	public function getSearchList() {
		$types = array();
		array_push($types, array('type'=>"all", 'text'=>"Alle Nachrichten"));
		return $types;
	}
	
	/*
	 * Performs a fulltext search over the attributes of the news table.
	*/
	public function search($query, $type) {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$roleID = $this->role->getRole();
		if ($this->auth->moduleReadAllowed("news", $roleID)) {
			$query = $this->db->escapeString($query);
			$queryString = "";
			if (strlen($query) >= 8 && substr($query, 0, 4) == "\\\\\\\"" && substr($query, -4) == "\\\\\\\"") {
				$queryWord = substr($query, 4, -4);
				$query = "\"".$queryWord."\"";
				$queryWordClean = $this->db->escapeString($queryWord);
				$queryString = $queryString = "(`title` LIKE '%".$queryWordClean."%' OR `headline` LIKE '%".$queryWordClean."%' OR `teaser` LIKE '%".$queryWordClean."%' OR `text` LIKE '%".$queryWordClean."%')";
			}
			else {
                $queryWords = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
                $queryWordCount = 1;
                foreach ($queryWords as $queryWord) {
                    $queryWordClean = $this->db->escapeString($queryWord);
                    if ($queryWordCount == 1) {
                        $queryString = "(`title` LIKE '%".$queryWordClean."%' OR `headline` LIKE '%".$queryWordClean."%' OR `teaser` LIKE '%".$queryWordClean."%' OR `text` LIKE '%".$queryWordClean."%')";
                    } else {
                        $queryString = $queryString." AND (`title` LIKE '%".$queryWordClean."%' OR `headline` LIKE '%".$queryWordClean."%' OR `teaser` LIKE '%".$queryWordClean."%' OR `text` LIKE '%".$queryWordClean."%')";
                    }
                    $queryWordCount++;
                }
            }

			if ($type=="standard") {
			}
			else {
			
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$start = $page*10-10;
				$end = 10;
				$start = $this->db->escapeString($start);
				$startCounter = ($page-1)*10+1;
				
				$news = array();
				$topic = "";

				$pages = 0;
				
				if ($type=="all") {
					$topic = "Alle Nachrichten";
					$result = $this->db->query("SELECT COUNT(`visible`) AS rowcount FROM `news` JOIN `rights` ON (`rights`.`location`=`news`.`location`)
					WHERE ".$queryString."  AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' ORDER BY `date` DESC");
					$pages = $this->db->getRowCount($result)/10;
					
					$result = $this->db->query("SELECT `teaser`, `headline`, `title`, `news`, `news`.`location` AS `newslocation` FROM `news` JOIN `rights` ON (`rights`.`location`=`news`.`location`)
					WHERE ".$queryString."  AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' ORDER BY `date` DESC LIMIT $start,$end");
					while ($row = $this->db->fetchArray($result)) {
						$teaser = $row['teaser'];
						$headline = $basic->convertToHTMLEntities($row['headline']);
						$title = $basic->convertToHTMLEntities($row['title']);
						$newsid = $row['news'];
						$location = $row['newslocation'];
						array_push($news, array('teaser'=>$teaser, 'headline'=>$headline, 'title'=>$title, 'news'=>$newsid, 'location'=>$location));
					}
				}

				list($startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage) = $this->getPaginationForSearch($pages, $page);

				require_once("template/news.search.tpl.php");
			}
		}
	}

	private function getPaginationForSearch($pages, $page) {
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

        return array($startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage);
    }
	
	/*
	 * Interface method stub.
	*/
	public function isTaggable() {
		return true;
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagList() {
		$types = array();
		array_push($types, array('type'=>"general", 'text'=>"Allgemein"));
		return $types;
	}
	
	/*
	 * Adds the tags for the general scope.
	*/
	public function addTags($tagString, $type, $news) {
		$tags = array_filter(explode(";", $tagString));
		$news = $this->db->escapeString($news);
		$this->db->query("DELETE FROM `news_tag` WHERE `type`='general' AND `news`='$news'");
		foreach ($tags as $tag) {
			$tag = $this->db->escapeString($tag);
			$tag = trim($tag);
			$id = "";
			if ((strlen($tag)>0)&&(!$this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' LIMIT 1"))) {
				$this->db->query("INSERT INTO `general`(`tag`) VALUES('$tag')");
			}
	
			$result = $this->db->query("SELECT `id` FROM `general` WHERE `tag`='$tag'");
			while ($row = $this->db->fetchArray($result)) {
				$id = $row['id'];
			}
			$this->db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$id','$news','general')");
		}
	}
	
	/*
	 * Returns the tags for the general scope.
	*/
	public function getTagString($type, $news) {
		$retString = array();
		$news = $this->db->escapeString($news);
		
		$result = $this->db->query("SELECT `general`.`tag` AS tagname FROM `general` JOIN `news_tag` ON(`general`.`id`=`news_tag`.`tag`) WHERE `type`='general' AND `news`='$news' ORDER BY `general`.`tag`");
		while ($row = $this->db->fetchArray($result)) {
			array_push($retString, $row['tagname']);
		}
		
		return implode(";", $retString);
	}
	
	public function getTags($type, $news) {
		$ret = array();
		$news = $this->db->escapeString($news);
		$result = $this->db->query("SELECT `id`, `general`.`tag` AS tagname FROM `general` JOIN `news_tag` ON(`general`.`id`=`news_tag`.`tag`) WHERE `type`='general' AND `news`='$news' ORDER BY `general`.`tag`");
		while ($row = $this->db->fetchArray($result)) {
			array_push($ret, array('id'=>$row['id'], 'tag'=>$row['tagname']));
		}
		
		return $ret;
	}
	
	public function displayTag($tagID, $type) {
		$basic = new Basic($this->db, $this->auth, $this->role);
		$tagID = $this->db->escapeString($tagID);
		$articles = array();
		$tagName = "";
		$result = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$tagID'");

		$dateTime = new DateTime("now", new DateTimeZone($this->config->getTimezone()));

		while ($row = $this->db->fetchArray($result)) {
			$tagName = $basic->convertToHTMLEntities($row['tag']);
		}
		$result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='general' ORDER BY `date` DESC");
		while ($row = $this->db->fetchArray($result)) {
			if ($this->auth->locationReadAllowed($row['location'], $this->role->getRole())) {
				$news = $row['news'];
				$headline = $basic->convertToHTMLEntities($row['headline']);
				$title = $basic->convertToHTMLEntities($row['title']);
				$dateTime->setTimestamp($row['date']);
				$date = $dateTime->format("d\.m\.Y");
				$location = $row['location'];
				$locationName = $basic->convertToHTMLEntities($row['name']);
				array_push($articles, array('news'=>$news, 'headline'=>$headline, 'title'=>$title, 'date'=>$date, 'location'=>$location, 'locationName'=>$locationName));
			}
		}
		require_once("template/news.tag.tpl.php");
	}
	
	public function getImage() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		if (isset($_GET['action'])) {
			if ($_GET['action']=="read") {
				if ($this->auth->moduleReadAllowed("news", $this->role->getRole())) {
					$newsID = $this->db->escapeString($_GET['show']);
					$location = $this->db->escapeString($_GET['id']);
					$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
					while ($row = $this->db->fetchArray($result)) {
						$location = $this->db->escapeString($row['maps_to']);
					}
					if ($this->auth->locationReadAllowed($location, $this->role->getRole())) {
						$picture = "empty";
						$result = $this->db->query("SELECT `url` FROM `news` JOIN `news_picture` ON `picture`=`picture2` WHERE `location`='$location' AND `news`='$newsID' AND `visible`='1' AND `deleted`='0'");
						while ($row = $this->db->fetchArray($result)) {
							$picture = "news/".$basic->convertToHTMLEntities($row['url']);
							if (empty($picture)) {
								$picture = "empty";
							}
						}
						if ($picture=="empty") {
							return null;
						}
						else {
							return $picture;
						}
					}
					else {
						return null;
					}
				}
				else {
					return null;
				}
			}
			else {
				return null;
			}
		}
		else {
			return null;
		}
	}
	
	public function getTitle() {
		if (isset($_GET['action'])) {
			if ($_GET['action']=="read") {
				if ($this->auth->moduleReadAllowed("news", $this->role->getRole())) {
					$newsID = $this->db->escapeString($_GET['show']);
					$location = $this->db->escapeString($_GET['id']);
					$headline = "";
					$title = "";
					$result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
					while ($row = $this->db->fetchArray($result)) {
						$location = $this->db->escapeString($row['maps_to']);
					}
					if ($this->auth->locationReadAllowed($location, $this->role->getRole())) {
						$picture = "empty";
						$result = $this->db->query("SELECT `headline`, `title` FROM `news` WHERE `location`='$location' AND `news`='$newsID' AND `visible`='1' AND `deleted`='0'");
						while ($row = $this->db->fetchArray($result)) {
							$headline = $row['headline'];
							$title = $row['title'];
							$newsTitle = $headline.": ".$title;
						}
						if ((strlen($headline)==0)&&(strlen($title)==0)) {
							return null;
						}
						else {
							return $newsTitle;
						}
					}
					else {
						return null;
					}
				}
				else {
					return null;
				}
			}
			else {
				return null;
			}
		}
		else {
			return null;
		}
	}
	
	private function nofollowOutboundLinks($content) {
		return preg_replace_callback('~<(a\s[^>]+)>~isU',
				function ($match) {
			
					list ($original, $tag) = $match;
					
					if (strpos($tag, "nofollow")) {
						return $original;
					}
					elseif (strpos($tag, $this->config->getDomain())) {
						return $original;
					}
					else {
						return "<$tag rel=\"nofollow\">";
					}
				},
				$content);
	}
}
?>