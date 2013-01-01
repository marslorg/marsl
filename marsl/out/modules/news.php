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
		if ($auth->moduleAdminAllowed("news", $role->getRole())) {
			$db = new DB();
			require_once("template/news.navigation.tpl.php");
			if(!isset($_GET['action'])) {
				$headline = "";
				$title = "";
				$category = "";
				$day = "DD";
				$month = "MM";
				$year = "YYYY";
				$teaser = "";
				$text = "";
				$picture1 = "";
				$picture2 = "";
				$subtitle2 = "";
				$photograph1 = "";
				$photograph2 = "";
				$city = "";
				$new = true;
				if (isset($_POST['action'])) {
					$new = false;
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$failed = false;
						$picture1 = $this->savePicture($_FILES['picture1'], 200, 200, 0, 0);
						if ($picture1==false) {
							$failed = true;
						}
						$picture2 = $this->savePicture($_FILES['picture2'], 0, 320, 640, 0);
						if ($picture2==false) {
							$failed = true;
						}
						if ($failed) {
							$headline = htmlentities($_POST['headline']);
							$title = htmlentities($_POST['title']);
							$category = $_POST['category'];
							$day = htmlentities($_POST['day']);
							$month = htmlentities($_POST['month']);
							$year = htmlentities($_POST['year']);
							$teaser = $basic->cleanHTML($_POST['teaser']);
							$text = $basic->cleanHTML($_POST['text']);
							$city = $basic->cleanHTML($_POST['city']);
							$subtitle2 = htmlentities($_POST['subtitle2']);
							$photograph1 = htmlentities($_POST['photograph1']);
							$photograph2 = htmlentities($_POST['photograph2']);
						}
						else {
							$author = mysql_real_escape_string($user->getID());
							$authorIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
							$headline = mysql_real_escape_string($_POST['headline']);
							$title = mysql_real_escape_string($_POST['title']);
							$location = mysql_real_escape_string($_POST['category']);
							$subtitle2 = mysql_real_escape_string($_POST['subtitle2']);
							$photograph1 = mysql_real_escape_string($_POST['photograph1']);
							$photograph2 = mysql_real_escape_string($_POST['photograph2']);
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
								if ($picture1!="empty") {
									$picture1 = mysql_real_escape_string($picture1);
									$db->query("INSERT INTO `news_picture`(`url`, `photograph`) VALUES('$picture1', '$photograph1')");
									$picture1 = mysql_insert_id();
								}
								else {
									$picture1 = "";
								}
								if ($picture2!="empty") {
									$picture2 = mysql_real_escape_string($picture2);
									$db->query("INSERT INTO `news_picture`(`url`, `subtitle`, `photograph`) VALUES('$picture2', '$subtitle2', '$photograph2')");
									$picture2 = mysql_insert_id();
								}
								else {
									$picture2 = "";
								}
								$db->query("INSERT INTO `news`(`author`,`author_ip`,`headline`,`title`,`teaser`,`text`,`picture1`,`picture2`,`date`,`visible`,`deleted`,`location`,`city`,`postdate`) 
								VALUES('$author','$authorIP','$headline','$title','$teaser','$text','$picture1','$picture2','$date','0','0','$location','$city','$postdate')");
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
								$subtitle2 = "";
								$photograph1 = "";
								$photograph2 = "";
							}
						}
					}
				}
				$locations = array();
				$result = $db->query("SELECT * FROM `navigation` WHERE `module`='news' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
				while ($row = mysql_fetch_array($result)) {
					if ($auth->locationAdminAllowed($row['id'], $role->getRole())||$auth->locationExtendedAllowed($row['id'], $role->getRole())) {
						array_push($locations,array('location'=>htmlentities($row['id']),'name'=>htmlentities($row['name'])));
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
						$id = htmlentities($row['news']);
						$author = $row['author'];
						$authorName = htmlentities($user->getAcronymbyID($author));
						$authorIP = htmlentities($row['author_ip']);
						$location = htmlentities($navigation->getNamebyID($row['location']));
						$headline = htmlentities($row['headline']);
						$title = htmlentities($row['title']);
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
								$photograph1 = "<br />Foto: ".htmlentities($row2['photograph']);
							}
						}
						$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID2'");
						while ($row2 = mysql_fetch_array($result2)) {
							$picture2 = $row2['url'];
							$subtitle2 = $row2['subtitle'];
							if (!empty($row2['photograph'])) {
								$photograph2 = " Foto: ".htmlentities($row2['photograph']);
							}
						}
						$city = htmlentities($row['city']);
						$date = date("d\.m\.Y", $row['date']);
						$postdate = date("d\. M Y \u\m H\:i\:s", $row['postdate']);
						array_push($news,array('author'=>$authorName,'authorIP'=>$authorIP,'news'=>$id, 'location'=>$location, 'headline'=>$headline, 'title'=>$title, 'teaser'=>$teaser, 'picture1'=>$picture1, 'photograph1'=>$photograph1, 'city'=>$city, 'date'=>$date, 'postdate'=>$postdate, 'text'=>$text));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/news.queue.tpl.php");
			}
			else if ($_GET['action']=="edit") {
				$id = mysql_real_escape_string(htmlentities($_GET['id']));
				if ($db->isExisting("SELECT * FROM `news` WHERE `news`='$id' AND `deleted`='0'")) {
					$result = $db->query("SELECT * FROM `news` WHERE `news`='$id' AND `deleted`='0'");
					while ($row = mysql_fetch_array($result)) {
						if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
							$headline = htmlentities($row['headline']);
							$title = htmlentities($row['title']);
							$category = htmlentities($row['location']);
							$day = date("d", $row['date']);
							$month = date("m", $row['date']);
							$year = date("Y", $row['date']);
							$teaser = $row['teaser'];
							$text = $row['text'];
							$pic1 = $row['picture1'];
							$pic2 = $row['picture2'];
							$picture1 = "empty";
							$picture2 = "empty";
							$subtitle2 = "";
							$photograph1 = "";
							$photograph2 = "";
							$city = htmlentities($row['city']);
							$new = true;
							$failed = false;
							if (isset($_POST['action'])) {
								$new = false;
								if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
									$failed = false;
									$picture1 = $this->savePicture($_FILES['picture1'], 200, 200, 0, 0);
									if ($picture1==false) {
										$failed = true;
									}
									$picture2 = $this->savePicture($_FILES['picture2'], 0, 320, 640, 0);
									if ($picture2==false) {
										$failed = true;
									}
									if ($failed) {
										$headline = htmlentities($_POST['headline']);
										$title = htmlentities($_POST['title']);
										$category = $_POST['category'];
										$day = htmlentities($_POST['day']);
										$month = htmlentities($_POST['month']);
										$year = htmlentities($_POST['year']);
										$teaser = $basic->cleanHTML($_POST['teaser']);
										$text = $basic->cleanHTML($_POST['text']);
										$city = $basic->cleanHTML($_POST['city']);
									}
									else {
										$author = mysql_real_escape_string($user->getID());
										$authorIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
										$headline = mysql_real_escape_string($_POST['headline']);
										$title = mysql_real_escape_string($_POST['title']);
										$location = mysql_real_escape_string($_POST['category']);
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
										if ($picture1!="empty") {
											$picture1 = mysql_real_escape_string($picture1);
											$photograph1 = mysql_real_escape_string($_POST['photograph1']);
											$db->query("INSERT INTO `news_picture`(`url`, `photograph`) VALUES('$picture1', '$photograph1')");
											$pic1 = mysql_insert_id();
										}
										if ($picture2!="empty") {
											$picture2 = mysql_real_escape_string($picture2);
											$subtitle2 = mysql_real_escape_string($_POST['subtitle2']);
											$photograph2 = mysql_real_escape_string($_POST['photograph2']);
											$db->query("INSERT INTO `news_picture`(`url`, `subtitle`, `photograph`) VALUES('$picture2', '$subtitle2', '$photograph2')");
											$pic2 = mysql_insert_id();
										}
										$admin = mysql_real_escape_string($user->getID());
										$adminIP = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);
										$db->query("UPDATE `news` SET `date`='$date', `admin`='$admin', `admin_ip`='$adminIP', `headline`='$headline', `title`='$title', `teaser`='$teaser', `text`='$text', `picture1`='$pic1', `picture2`='$pic2', `location`='$location', `city`='$city' WHERE `news`='$id'"); 
										$headline = htmlentities($_POST['headline']);
										$title = htmlentities($_POST['title']);
										$category = $_POST['category'];
										$day = htmlentities($_POST['day']);
										$month = htmlentities($_POST['month']);
										$year = htmlentities($_POST['year']);
										$teaser = $basic->cleanHTML($_POST['teaser']);
										$text = $basic->cleanHTML($_POST['text']);
										$city = $basic->cleanHTML($_POST['city']);
									}
								}
							}
							$locations = array();
							$result = $db->query("SELECT * FROM `navigation` WHERE `module`='news' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
							while ($row = mysql_fetch_array($result)) {
								if ($auth->locationAdminAllowed($row['id'], $role->getRole())||$auth->locationExtendedAllowed($row['id'], $role->getRole())) {
									array_push($locations,array('location'=>htmlentities($row['id']),'name'=>htmlentities($row['name'])));
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
					$id = htmlentities($row['news']);
					$author = htmlentities($user->getAcronymbyID($row['author']));
					$authorIP = htmlentities($row['author_ip']);
					$category = $row['location'];
					$editLink = ($auth->locationAdminAllowed($row['location'], $role->getRole()));
					$location = $navigation->getNamebyID($category);
					$date = date("d\.m\.Y", $row['date']);
					$postdate = date("d\. M Y \u\m H\:i\:s", $row['postdate']);
					$headline = htmlentities($row['headline']);
					$title = htmlentities($row['title']);
					$picture1 = "empty";
					$photograph1 = "";
					$picID1 = mysql_real_escape_string($row['picture1']);
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					$city = htmlentities($row['city']);
					$teaser = $row['teaser'];
					$text = $row['text'];
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url']);
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph']);
						}
					}
					array_push($news,array('text'=>$text,'teaser'=>$teaser,'city'=>$city,'picture1'=>$picture1, 'photograph1'=>$photograph1, 'title'=>$title,'headline'=>$headline,'id'=>$id,'editLink'=>$editLink,'date'=>$date,'postdate'=>$postdate,'location'=>$location,'author'=>$author,'authorIP'=>$authorIP));
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
					$authorName = htmlentities($user->getAcronymbyID($author));
					$id = htmlentities($id);
					$authorIP = htmlentities($row['author_ip']);
					$location = htmlentities($navigation->getNamebyID($row['location']));
					$headline = htmlentities($row['headline']);
					$title = htmlentities($row['title']);
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
						$picture1 = htmlentities($row2['url']);
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph']);
						}
					}
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID2'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture2 = htmlentities($row2['url']);
						$subtitle2 = htmlentities($row2['subtitle']);
						if (!empty($row2['photograph'])) {
							$photograph2 = " Foto: ".htmlentities($row2['photograph']);
						}
					}
					$city = htmlentities(strtoupper($row['city']));
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
					$authorName = strtolower(htmlentities($user->getAcronymbyID($author)));
					$picID1 = mysql_real_escape_string($row['picture1']);
					$picture1 = "empty";
					$photograph1 = "";
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url']);
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph']);
						}
					}
					$teaser = $row['teaser'];
					$text = $row['text'];
					$city = htmlentities($row['city']);
					$headline = htmlentities($row['headline']);
					$title = htmlentities($row['title']);
					$id = htmlentities($row['news']);
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
					$authorName = strtolower(htmlentities($user->getAcronymbyID($author)));
					$picID1 = mysql_real_escape_string($row['picture1']);
					$picID2 = mysql_real_escape_string($row['picture2']);
					$picture1 = "empty";
					$photograph1 = "";
					$picture2 = "empty";
					$subtitle2 = "";
					$photograph2 = "";
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID1'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture1 = htmlentities($row2['url']);
						if (!empty($row2['photograph'])) {
							$photograph1 = "<br />Foto: ".htmlentities($row2['photograph']);
						}
					}
					$result2 = $db->query("SELECT * FROM `news_picture` WHERE `picture`='$picID2'");
					while ($row2 = mysql_fetch_array($result2)) {
						$picture2 = htmlentities($row2['url']);
						$subtitle2 = htmlentities($row2['subtitle']);
						if (!empty($row2['photograph'])) {
							$photograph2 = " Foto: ".htmlentities($row2['photograph']);
						}
					}
					$teaser = $row['teaser'];
					$text = $row['text'];
					$city = htmlentities($row['city']);
					$headline = htmlentities($row['headline']);
					$title = htmlentities($row['title']);
					$config = new Configuration();
					$url = $config->getDomain()."/index.php?id=".$_GET['id']."&amp;show=".$_GET['show']."&amp;action=read";
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
		$query = "omas +biffy";
		$type = "all";
		$query = mysql_real_escape_string($query);
		$role = new Role();
		$roleID = $role->getRole();
		if ($type=="all") {
			$db = new DB();
			$result = $db->query("SELECT *, ((1.5 * (MATCH(`title`) AGAINST ('$query' IN BOOLEAN MODE))) + (1.4 * (MATCH(`headline`) AGAINST ('$query' IN BOOLEAN MODE))) + (1.2 * (MATCH(`teaser`) AGAINST ('$query' IN BOOLEAN MODE))) + (0.8 * (MATCH(`text`) AGAINST ('$query' IN BOOLEAN MODE))) ) AS relevance FROM `news`
					JOIN `rights` ON (`rights`.`location`=`news`.`location`)
					WHERE (MATCH(`title`,`headline`,`teaser`,`text`) AGAINST ('$query' IN BOOLEAN MODE)) AND `visible`='1' AND `deleted`='0' AND `read`='1' AND `role`='$roleID' HAVING relevance > 0 ORDER BY relevance DESC");
			while ($row = mysql_fetch_array($result)) {
				echo $row['title']."<br />";
			}
		}
	}
}
?>