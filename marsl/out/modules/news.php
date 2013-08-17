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

class News implements Module {
	/*
	 * Displays the admin interface of the news module.
	 */
	public function admin() {
		$navigation = new Navigation();
		$auth = new Authentication();
		$basic = new Basic();
		$user = new User();
		$role = new Role();
		$modules = $basic->getModules();
		$moduleTags = array();
		foreach ($modules as $module) {
			include_once(dirname(__FILE__)."/".$module['file'].".php");
			$class = new $module['class'];
			if ($class->isTaggable()) {
				$tagList = $class->getTagList();
				foreach($tagList as $tagType) {
					$typeID = $module['file']."_".$tagType['type'];
					$typeName = $tagType['text'];
					array_push($moduleTags, array('type'=>$typeID, 'name'=>$typeName));
				}
			}	
		}
		if ($auth->moduleAdminAllowed("news", $role->getRole())) {
			$db = new DB();
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
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$failed = false;

						if ($failed) {
							$headline = htmlentities($_POST['headline'], null, "ISO-8859-1");
							$title = htmlentities($_POST['title'], null, "ISO-8859-1");
							$category = $_POST['category'];
							$day = htmlentities($_POST['day'], null, "ISO-8859-1");
							$month = htmlentities($_POST['month'], null, "ISO-8859-1");
							$year = htmlentities($_POST['year'], null, "ISO-8859-1");
							$teaser = $basic->cleanHTML($_POST['teaser']);
							$text = $basic->cleanHTML($_POST['text']);
							$city = $basic->cleanHTML($_POST['city']);
							$corrected = isset($_POST['corrected']);
							$tmpModuleTags = array();
							foreach ($moduleTags as $moduleTag) {
								$moduleTag['tags'] = htmlentities($_POST[$moduleTag['type']], null, "ISO-8859-1");
								array_push($tmpModuleTags, $moduleTag);
							}
							$moduleTags = $tmpModuleTags;
						}
						else {
							$author = mysql_real_escape_string($user->getID());
							$authorIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
							$headline = mysql_real_escape_string($_POST['headline']);
							$title = mysql_real_escape_string($_POST['title']);
							$location = mysql_real_escape_string($_POST['category']);
							$corrected = isset($_POST['corrected']);
							$tmpModuleTags = array();
							foreach ($moduleTags as $moduleTag) {
								$moduleTag['tags'] = mysql_real_escape_string($_POST[$moduleTag['type']]);
								array_push($tmpModuleTags, $moduleTag);
							}
							$moduleTags = $tmpModuleTags;
							$date = "";
							$postdate = time();
							$city = mysql_real_escape_string($_POST['city']);
							if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
								$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
							}
							else {
								$date = time();
							}
							$teaser = mysql_real_escape_string($basic->cleanHTML($_POST['teaser']));
							$text = mysql_real_escape_string($basic->cleanHTML($_POST['text']));
							if ($auth->locationAdminAllowed($location, $role->getRole())||$auth->locationExtendedAllowed($location, $role->getRole())) {

								$picture1 = "";
								if (isset($_POST['picture1'])) {
									$picture1 = mysql_real_escape_string($_POST['picture1']);
								}
								
								$picture2 = "";
								if (isset($_POST['picture2'])) {
									$picture2 = mysql_real_escape_string($_POST['picture2']);
									$result = $db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picture2'");
									while ($row = mysql_fetch_array($result)) {
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
										$db->query("UPDATE `news_picture` SET `url`='$fileName' WHERE `picture`='$picture2'");
										imagecopyresampled($newIMG, $oldIMG, 0, 0, $pic2X, $pic2Y, 640, 320, $pic2W, $pic2H);
										ImageDestroy($oldIMG);
										imagejpeg($newIMG, $fileLink);
									}
								}

								$db->query("INSERT INTO `news`(`author`,`author_ip`,`headline`,`title`,`teaser`,`text`,`picture1`,`picture2`,`date`,`visible`,`deleted`,`location`,`city`,`postdate`,`corrected`) 
								VALUES('$author','$authorIP','$headline','$title','$teaser','$text','$picture1','$picture2','$date','0','0','$location','$city','$postdate','$corrected')");
								$newsID = mysql_insert_id();
								foreach ($moduleTags as $moduleTag) {
									
									$type = explode("_", $moduleTag['type']);
									$file = $type[0];
									$scope = $type[1];
									$module = $basic->getModule($file);
									include_once(dirname(__FILE__)."/".$file.".php");
									$class = new $module['class'];
									$class->addTags($moduleTag['tags'], $scope, $newsID);

								}
								$administrators = $user->getAdminUsers();
								foreach ($administrators as $administrator) {
									$administratorRole = $role->getRolebyUser($administrator);
									if ($auth->moduleAdminAllowed("news", $administratorRole)) {
										if ($auth->locationAdminAllowed($location, $administratorRole)) {
											$mailer = new Mailer();
											$mailer->sendNewArticleMail($administrator);
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
				$result = $db->query("SELECT * FROM `navigation` WHERE `module`='news' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
				while ($row = mysql_fetch_array($result)) {
					if ($auth->locationAdminAllowed($row['id'], $role->getRole())||$auth->locationExtendedAllowed($row['id'], $role->getRole())) {
						array_push($locations,array('location'=>htmlentities($row['id'], null, "ISO-8859-1"),'name'=>htmlentities($row['name'], null, "ISO-8859-1")));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/news.write.tpl.php");
			}
			else if ($_GET['action']=="queue") {
				$this->doThings();
				$news = array();
				$result = $db->query("SELECT * FROM `news` WHERE `visible`='0' AND `deleted`='0'");
				while ($row = mysql_fetch_array($result)) {
					if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
						$id = htmlentities($row['news'], null, "ISO-8859-1");
						$author = $row['author'];
						$corrected = $row['corrected'];
						$authorName = htmlentities($user->getAcronymbyID($author), null, "ISO-8859-1");
						$authorIP = htmlentities($row['author_ip'], null, "ISO-8859-1");
						$location = htmlentities($navigation->getNamebyID($row['location']), null, "ISO-8859-1");
						$headline = htmlentities($row['headline'], null, "ISO-8859-1");
						$title = htmlentities($row['title'], null, "ISO-8859-1");
						$teaser = $row['teaser'];
						$text = $row['text'];
						$picID1 = mysql_real_escape_string($row['picture1']);
						$picID2 = mysql_real_escape_string($row['picture2']);
						$picture1 = "empty";
						$photograph1 = "";
						$picture2 = "empty";
						$subtitle2 = "";
						$photograph2 = "";
						$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
						while ($row2 = mysql_fetch_array($result2)) {
							$picture1 = $row2['url'];
							if (!empty($row2['photograph'])) {
								$photograph1 = "<br />Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
							}
						}
						$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID2'");
						while ($row2 = mysql_fetch_array($result2)) {
							$picture2 = $row2['url'];
							$subtitle2 = htmlentities($row2['subtitle'], null, "ISO-8859-1");
							if (!empty($row2['photograph'])) {
								$photograph2 = " Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
							}
						}
						$city = htmlentities($row['city'], null, "ISO-8859-1");
						$date = date("d\.m\.Y", $row['date']);
						$postdate = date("d\. M Y \u\m H\:i\:s", $row['postdate']);
						array_push($news,array('author'=>$authorName,'authorIP'=>$authorIP,'news'=>$id, 'location'=>$location, 'headline'=>$headline, 'title'=>$title, 'teaser'=>$teaser, 'picture1'=>$picture1, 'photograph1'=>$photograph1, 'city'=>$city, 'date'=>$date, 'postdate'=>$postdate, 'text'=>$text, 'corrected'=>$corrected));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/news.queue.tpl.php");
			}
			else if ($_GET['action']=="edit") {
				$id = mysql_real_escape_string(htmlentities($_GET['id'], null, "ISO-8859-1"));
				if ($db->isExisting("SELECT * FROM `news` WHERE `news`='$id' AND `deleted`='0'")) {
					$result = $db->query("SELECT * FROM `news` WHERE `news`='$id' AND `deleted`='0'");
					while ($row = mysql_fetch_array($result)) {
						if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
							$corrected = $row['corrected'];
							$headline = htmlentities($row['headline'], null, "ISO-8859-1");
							$title = htmlentities($row['title'], null, "ISO-8859-1");
							$category = htmlentities($row['location'], null, "ISO-8859-1");
							$day = date("d", $row['date']);
							$month = date("m", $row['date']);
							$year = date("Y", $row['date']);
							$teaser = $row['teaser'];
							$text = $row['text'];
							$picture1 = $row['picture1'];
							$picture2 = $row['picture2'];
							$city = htmlentities($row['city'], null, "ISO-8859-1");
							$tmpModuleTags = array();
							foreach ($moduleTags as $moduleTag) {

								$type = explode("_", $moduleTag['type']);
								$file = $type[0];
								$scope = $type[1];
								$module = $basic->getModule($file);
								include_once(dirname(__FILE__)."/".$file.".php");
								$class = new $module['class'];
								$moduleTag['tags'] = $class->getTagString($scope, $id);

								array_push($tmpModuleTags, $moduleTag);
							}
							$moduleTags = $tmpModuleTags;
							$new = true;
							$failed = false;
							if (isset($_POST['action'])) {
								$new = false;
								if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
									$failed = false;

									if ($failed) {
										$headline = htmlentities($_POST['headline'], null, "ISO-8859-1");
										$title = htmlentities($_POST['title'], null, "ISO-8859-1");
										$category = $_POST['category'];
										$day = htmlentities($_POST['day'], null, "ISO-8859-1");
										$month = htmlentities($_POST['month'], null, "ISO-8859-1");
										$year = htmlentities($_POST['year'], null, "ISO-8859-1");
										$teaser = $basic->cleanHTML($_POST['teaser']);
										$text = $basic->cleanHTML($_POST['text']);
										$city = $basic->cleanHTML($_POST['city']);
										$corrected = isset($_POST['corrected']);
										$tmpModuleTags = array();
										foreach ($moduleTags as $moduleTag) {
											$moduleTag['tags'] = htmlentities($_POST[$moduleTag['type']], null, "ISO-8859-1");
											array_push($tmpModuleTags, $moduleTag);
										}
										$moduleTags = $tmpModuleTags;
									}
									else {
										$author = mysql_real_escape_string($user->getID());
										$authorIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
										$headline = mysql_real_escape_string($_POST['headline']);
										$title = mysql_real_escape_string($_POST['title']);
										$location = mysql_real_escape_string($_POST['category']);
										$corrected = isset($_POST['corrected']);
										$date = "";
										$postdate = time();
										$city = mysql_real_escape_string($_POST['city']);
										if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
											$date = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
										}
										else {
											$date = time();
										}
										$teaser = mysql_real_escape_string($basic->cleanHTML($_POST['teaser']));
										$text = mysql_real_escape_string($basic->cleanHTML($_POST['text']));
										
										if (isset($_POST['picture1'])) {
											$picture1 = mysql_real_escape_string($_POST['picture1']);
										}
										
										if (isset($_POST['picture2'])) {
											$picture2 = mysql_real_escape_string($_POST['picture2']);
											$result = $db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picture2'");
											while ($row = mysql_fetch_array($result)) {
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
												$db->query("UPDATE `news_picture` SET `url`='$fileName' WHERE `picture`='$picture2'");
												imagecopyresampled($newIMG, $oldIMG, 0, 0, $pic2X, $pic2Y, 640, 320, $pic2W, $pic2H);
												ImageDestroy($oldIMG);
												imagejpeg($newIMG, $fileLink);
											}
										}
										
										$tmpModuleTags = array();
										foreach ($moduleTags as $moduleTag) {
											$moduleTag['tags'] = mysql_real_escape_string($_POST[$moduleTag['type']]);
											array_push($tmpModuleTags, $moduleTag);
										}
										$moduleTags = $tmpModuleTags;
										$admin = mysql_real_escape_string($user->getID());
										$adminIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
										$db->query("UPDATE `news` SET `date`='$date', `admin`='$admin', `admin_ip`='$adminIP', `headline`='$headline', `title`='$title', `teaser`='$teaser', `text`='$text', `picture1`='$picture1', `picture2`='$picture2', `location`='$location', `city`='$city', `corrected`='$corrected' WHERE `news`='$id'"); 
										foreach ($moduleTags as $moduleTag) {

											$type = explode("_", $moduleTag['type']);
											$file = $type[0];
											$scope = $type[1];
											$module = $basic->getModule($file);
											include_once(dirname(__FILE__)."/".$file.".php");
											$class = new $module['class'];
											$class->addTags($moduleTag['tags'], $scope, $id);

										}
										$headline = htmlentities($_POST['headline'], null, "ISO-8859-1");
										$title = htmlentities($_POST['title'], null, "ISO-8859-1");
										$category = $_POST['category'];
										$day = htmlentities($_POST['day'], null, "ISO-8859-1");
										$month = htmlentities($_POST['month'], null, "ISO-8859-1");
										$year = htmlentities($_POST['year'], null, "ISO-8859-1");
										$teaser = $basic->cleanHTML($_POST['teaser']);
										$text = $basic->cleanHTML($_POST['text']);
										$city = $basic->cleanHTML($_POST['city']);
										$tmpModuleTags = array();
										foreach ($moduleTags as $moduleTag) {
											$moduleTag['tags'] = htmlentities($_POST[$moduleTag['type']], null, "ISO-8859-1");
											array_push($tmpModuleTags, $moduleTag);
										}
										$moduleTags = $tmpModuleTags;
									}
								}
							}
							$locations = array();
							$result = $db->query("SELECT * FROM `navigation` WHERE `module`='news' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
							while ($row = mysql_fetch_array($result)) {
								if ($auth->locationAdminAllowed($row['id'], $role->getRole())||$auth->locationExtendedAllowed($row['id'], $role->getRole())) {
									array_push($locations,array('location'=>htmlentities($row['id'], null, "ISO-8859-1"),'name'=>htmlentities($row['name'], null, "ISO-8859-1")));
								}
							}
							$authTime = time();
							$authToken = $auth->getToken($authTime);
							require_once("template/news.edit.tpl.php");
						}
					}
				}
			}
			else if ($_GET['action']=="news") {
				$this->doThings();
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$result = $db->query("SELECT * FROM `news` WHERE `visible`='1' AND `deleted`='0'");
				$pages = mysql_num_rows($result)/10;
				$start = $page*10-10;
				$end = 10;
				$news = array();
				$start = mysql_real_escape_string($start);
				$result = $db->query("SELECT * FROM `news` WHERE `visible`='1' AND `deleted`='0' ORDER BY `postdate` DESC LIMIT $start,$end");
				while ($row = mysql_fetch_array($result)) {
					$id = htmlentities($row['news'], null, "ISO-8859-1");
					$corrected = $row['corrected'];
					$author = htmlentities($user->getAcronymbyID($row['author']), null, "ISO-8859-1");
					$authorIP = htmlentities($row['author_ip'], null, "ISO-8859-1");
					$category = $row['location'];
					$editLink = ($auth->locationAdminAllowed($row['location'], $role->getRole()));
					$location = $navigation->getNamebyID($category);
					$date = date("d\.m\.Y", $row['date']);
					$postdate = date("d\. M Y \u\m H\:i\:s", $row['postdate']);
					$headline = htmlentities($row['headline'], null, "ISO-8859-1");
					$title = htmlentities($row['title'], null, "ISO-8859-1");
					$picture1 = "empty";
					$photograph1 = "";
					$picID1 = mysql_real_escape_string($row['picture1']);
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					$city = htmlentities($row['city'], null, "ISO-8859-1");
					$teaser = $row['teaser'];
					$text = $row['text'];
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url'], null, "ISO-8859-1");
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
						}
					}
					array_push($news,array('text'=>$text,'teaser'=>$teaser,'city'=>$city,'picture1'=>$picture1, 'photograph1'=>$photograph1, 'title'=>$title,'headline'=>$headline,'id'=>$id,'editLink'=>$editLink,'date'=>$date,'postdate'=>$postdate,'location'=>$location,'author'=>$author,'authorIP'=>$authorIP, 'corrected'=>$corrected));
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/news.tpl.php");
			}
			else if ($_GET['action']=="details") {
				$this->doThings();
				$id = mysql_real_escape_string($_GET['id']);
				$result = $db->query("SELECT * FROM `news` WHERE `news`='$id' AND `deleted`='0'");
				while ($row = mysql_fetch_array($result)) {
					$submitLink = (($row['visible']==0)&&($auth->locationAdminAllowed($row['location'], $role->getRole())));
					$editLink = ($auth->locationAdminAllowed($row['location'], $role->getRole()));
					$author = $row['author'];
					$corrected = $row['corrected'];
					$authorName = htmlentities($user->getAcronymbyID($author), null, "ISO-8859-1");
					$id = htmlentities($id, null, "ISO-8859-1");
					$authorIP = htmlentities($row['author_ip'], null, "ISO-8859-1");
					$location = htmlentities($navigation->getNamebyID($row['location']), null, "ISO-8859-1");
					$headline = htmlentities($row['headline'], null, "ISO-8859-1");
					$title = htmlentities($row['title'], null, "ISO-8859-1");
					$teaser = $row['teaser'];
					$text = $row['text'];
					$picID1 = mysql_real_escape_string($row['picture1']);
					$picID2 = mysql_real_escape_string($row['picture2']);
					$picture1 = "empty";
					$photograph1 = "";
					$picture2 = "empty";
					$subtitle2 = "";
					$photograph2 = "";
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url'], null, "ISO-8859-1");
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
						}
					}
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID2'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture2 = htmlentities($row2['url'], null, "ISO-8859-1");
						$subtitle2 = htmlentities($row2['subtitle'], null, "ISO-8859-1");
						if (!empty($row2['photograph'])) {
							$photograph2 = " Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
						}
					}
					$city = htmlentities($row['city'], null, "ISO-8859-1");
					$date = date("d\.m\.Y", $row['date']);
					$postdate = date("\a\m d\. M Y \u\m H\:i\:s", $row['postdate']);
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					require_once("template/news.details.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Displays the frontend of a news module.
	 */
	public function display() {
		$auth = new Authentication();
		$db = new DB();
		$basic = new Basic();
		$user = new User();
		$role = new Role();
		
		if ($auth->moduleReadAllowed("news", $role->getRole())) {
			if (!isset($_GET['action'])) {
				$location = "";
				if (isset($_GET['id'])) {
					$location = $_GET['id'];
				}
				else {
					$location = $basic->getHomeLocation();
				}
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$location = mysql_real_escape_string($location);
				$result = $db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = mysql_fetch_array($result)) {
					$location = mysql_real_escape_string($row['maps_to']);
				}
				$result = $db->query("SELECT * FROM `news` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
				$pages = mysql_num_rows($result)/10;
				$start = $page*10-10;
				$end = 10;
				$start = mysql_real_escape_string($start);
				$news = array();
				$result = $db->query("SELECT * FROM `news` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `postdate` DESC LIMIT $start,$end");
				while ($row=mysql_fetch_array($result)) {
					$date = date("d\.m\.Y", $row['date']);
					$postdate = date("d\.m\.Y", $row['postdate']);
					$author = $row['author'];
					$authorName = strtolower(htmlentities($user->getAcronymbyID($author), null, "ISO-8859-1"));
					$picID1 = mysql_real_escape_string($row['picture1']);
					$picture1 = "empty";
					$photograph1 = "";
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url'], null, "ISO-8859-1");
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
						}
					}
					$teaser = $row['teaser'];
					$text = $row['text'];
					$city = htmlentities($row['city'], null, "ISO-8859-1");
					$headline = htmlentities($row['headline'], null, "ISO-8859-1");
					$title = htmlentities($row['title'], null, "ISO-8859-1");
					$id = htmlentities($row['news'], null, "ISO-8859-1");
					array_push($news,array('city'=>$city,'headline'=>$headline,'title'=>$title,'id'=>$id,'date'=>$date,'postdate'=>$postdate,'author'=>$authorName,'picture1'=>$picture1, 'photograph1'=>$photograph1, 'teaser'=>$teaser,'text'=>$text));
				}
				require_once("template/news.main.tpl.php");
			}
			else if ($_GET['action']=="read") {
				$location = mysql_real_escape_string($_GET['id']);
				$result = $db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
				while ($row = mysql_fetch_array($result)) {
					$location = mysql_real_escape_string($row['maps_to']);
				}
				$news = mysql_real_escape_string($_GET['show']);
				$result = $db->query("SELECT * FROM `news` WHERE `location`='$location' AND `news`='$news' AND `visible`='1' AND `deleted`='0'");
				while ($row = mysql_fetch_array($result)) {
					$date = date("d\.m\.Y", $row['date']);
					$author = $row['author'];
					$authorName = strtolower(htmlentities($user->getAcronymbyID($author), null, "ISO-8859-1"));
					$picID1 = mysql_real_escape_string($row['picture1']);
					$picID2 = mysql_real_escape_string($row['picture2']);
					$picture1 = "empty";
					$photograph1 = "";
					$picture2 = "empty";
					$subtitle2 = "";
					$photograph2 = "";
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url'], null, "ISO-8859-1");
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
						}
					}
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID2'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture2 = htmlentities($row2['url'], null, "ISO-8859-1");
						$subtitle2 = htmlentities($row2['subtitle'], null, "ISO-8859-1");
						if (!empty($row2['photograph'])) {
							$photograph2 = " Foto: ".htmlentities($row2['photograph'], null, "ISO-8859-1");
						}
					}
					$teaser = $row['teaser'];
					$text = $row['text'];
					$city = htmlentities($row['city'], null, "ISO-8859-1");
					$headline = htmlentities($row['headline'], null, "ISO-8859-1");
					$title = htmlentities($row['title'], null, "ISO-8859-1");
					$config = new Configuration();
					$url = $config->getDomain()."/index.php?id=".$_GET['id']."&amp;show=".$_GET['show']."&amp;action=read";
					
					$modules = $basic->getModules();
					$moduleTags = array();
					foreach ($modules as $module) {
						include_once(dirname(__FILE__)."/".$module['file'].".php");
						$class = new $module['class'];
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
	
	/*
	 * Executes some smaller functions on a news article.
	 */
	private function doThings() {
		$db = new DB();
		$auth = new Authentication();
		$role = new Role();
		$user = new User();
		if (isset($_GET['do'])) {
			if ($_GET['do']=="submit") {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$id = mysql_real_escape_string($_GET['id']);
					$result = $db->query("SELECT * FROM `news` WHERE `news`='$id'");
					while ($row = mysql_fetch_array($result)) {
						if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
							$admin = mysql_real_escape_string($user->getID());
							$adminIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
							$db->query("UPDATE `news` SET `visible`='1', `admin`='$admin', `admin_ip`='$adminIP' WHERE `news`='$id'");
						}
					}
				}
			}
			else if ($_GET['do']=="del") {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$id = mysql_real_escape_string($_GET['id']);
					$result = $db->query("SELECT * FROM `news` WHERE `news`='$id'");
					while ($row = mysql_fetch_array($result)) {
						if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
							$db->query("UPDATE `news` SET `deleted`='1' WHERE `news`='$id'");
						}
					}
				}
			}
		}
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
		$auth = new Authentication();
		$role = new Role();
		$roleID = $role->getRole();
		if ($auth->moduleReadAllowed("news", $roleID)) {
			$query = mysql_real_escape_string($query);
			$db = new DB();
			if ($type=="standard") {
			}
			else {
			
				$page = 1;
				if (isset($_GET['page'])) {
					$page = $_GET['page'];
				}
				$start = $page*10-10;
				$end = 10;
				$start = mysql_real_escape_string($start);
				$startCounter = ($page-1)*10+1;
				
				$news = array();
				$topic = "";
				
				if ($type=="all") {
					$topic = "Alle Nachrichten";
					$result = $db->query("SELECT *, ((1.5 * (MATCH(`title`) AGAINST ('$query' IN BOOLEAN MODE))) + (1.4 * (MATCH(`headline`) AGAINST ('$query' IN BOOLEAN MODE))) + (1.2 * (MATCH(`teaser`) AGAINST ('$query' IN BOOLEAN MODE))) + (0.8 * (MATCH(`text`) AGAINST ('$query' IN BOOLEAN MODE))) ) AS relevance FROM `news`
							JOIN `rights` ON (`rights`.`location`=`news`.`location`)
							WHERE (MATCH(`title`,`headline`,`teaser`,`text`) AGAINST ('$query' IN BOOLEAN MODE)) AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' HAVING relevance > 0 ORDER BY relevance DESC");
					$pages = mysql_num_rows($result)/10;
					
					$result = $db->query("SELECT *, ((1.5 * (MATCH(`title`) AGAINST ('$query' IN BOOLEAN MODE))) + (1.4 * (MATCH(`headline`) AGAINST ('$query' IN BOOLEAN MODE))) + (1.2 * (MATCH(`teaser`) AGAINST ('$query' IN BOOLEAN MODE))) + (0.8 * (MATCH(`text`) AGAINST ('$query' IN BOOLEAN MODE))) ) AS relevance FROM `news`
							JOIN `rights` ON (`rights`.`location`=`news`.`location`)
							WHERE (MATCH(`title`,`headline`,`teaser`,`text`) AGAINST ('$query' IN BOOLEAN MODE)) AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' HAVING relevance > 0 ORDER BY relevance DESC LIMIT $start,$end");
					while ($row = mysql_fetch_array($result)) {
						$teaser = $row['teaser'];
						$headline = htmlentities($row['headline'], null, "ISO-8859-1");
						$title = htmlentities($row['title'], null, "ISO-8859-1");
						$newsid = $row['news'];
						$location = $row['location'];
						array_push($news, array('teaser'=>$teaser, 'headline'=>$headline, 'title'=>$title, 'news'=>$newsid, 'location'=>$location));
					}
				}
				require_once("template/news.search.tpl.php");
			}
		}
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
		$db = new DB();
		$tags = array_filter(explode(";", $tagString));
		$news = mysql_real_escape_string($news);
		$db->query("DELETE FROM `news_tag` WHERE `type`='general' AND `news`='$news'");
		foreach ($tags as $tag) {
			$tag = mysql_real_escape_string($tag);
			$tag = trim($tag);
			$id = "";
			if ((strlen($tag)>0)&&(!$db->isExisting("SELECT * FROM `general` WHERE `tag`='$tag'"))) {
				$db->query("INSERT INTO `general`(`tag`) VALUES('$tag')");
			}
	
			$result = $db->query("SELECT `id` FROM `general` WHERE `tag`='$tag'");
			while ($row = mysql_fetch_array($result)) {
				$id = $row['id'];
			}
			$db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$id','$news','general')");
		}
	}
	
	/*
	 * Returns the tags for the general scope.
	*/
	public function getTagString($type, $news) {
		$db = new DB();
		$retString = array();
		$news = mysql_real_escape_string($news);
		
		$result = $db->query("SELECT `general`.`tag` AS tagname FROM `general` JOIN `news_tag` ON(`general`.`id`=`news_tag`.`tag`) WHERE `type`='general' AND `news`='$news' ORDER BY `general`.`tag`");
		while ($row = mysql_fetch_array($result)) {
			array_push($retString, $row['tagname']);
		}
		
		return implode(";", $retString);
	}
	
	public function getTags($type, $news) {
		$db = new DB();
		$ret = array();
		$news = mysql_real_escape_string($news);
		$result = $db->query("SELECT `id`, `general`.`tag` AS tagname FROM `general` JOIN `news_tag` ON(`general`.`id`=`news_tag`.`tag`) WHERE `type`='general' AND `news`='$news' ORDER BY `general`.`tag`");
		while ($row = mysql_fetch_array($result)) {
			array_push($ret, array('id'=>$row['id'], 'tag'=>$row['tagname']));
		}
		
		return $ret;
	}
	
	public function displayTag($tagID, $type) {
		$db = new DB();
		$role = new Role();
		$auth = new Authentication();
		$tagID = mysql_real_escape_string($tagID);
		$articles = array();
		$tagName = "";
		$result = $db->query("SELECT `tag` FROM `general` WHERE `id`='$tagID'");
		while ($row = mysql_fetch_array($result)) {
			$tagName = htmlentities($row['tag'], null, "ISO-8859-1");
		}
		$result = $db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='general' ORDER BY `date` DESC");
		while ($row = mysql_fetch_array($result)) {
			if ($auth->locationReadAllowed($row['location'], $role->getRole())) {
				$news = $row['news'];
				$headline = htmlentities($row['headline'], null, "ISO-8859-1");
				$title = htmlentities($row['title'], null, "ISO-8859-1");
				$date = date("d\.m\.Y", $row['date']);
				$location = $row['location'];
				$locationName = htmlentities($row['name'], null, "ISO-8859-1");
				array_push($articles, array('news'=>$news, 'headline'=>$headline, 'title'=>$title, 'date'=>$date, 'location'=>$location, 'locationName'=>$locationName));
			}
		}
		require_once("template/news.tag.tpl.php");
	}
}
?>