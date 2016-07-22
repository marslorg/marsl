<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");

class Location {
	
	public function display() {
		
	}
	
	public function admin() {
		$role = new Role();
		$auth = new Authentication();
		if ($auth->moduleAdminAllowed("cbe", $role->getRole())) {
			$db = new DB();
			$newEntry = false;
			$entrySuccessful = false;
			if (isset($_POST['action'])) {
				if ($_POST['action']=="newClub") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$newEntry = true;
						$entry = $db->escape($_POST['entry']);
						if (!$db->isExisting("SELECT * FROM `location` WHERE `tag`='$entry'")) {
							$db->query("INSERT INTO `location`(`tag`) VALUES('$entry')");
							$entrySuccessful = true;
						}
					}
				}
			}
			$deletionSuccessful = false;
			if (isset($_GET['action2'])) {
				if ($_GET['action2']=="delete") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						$clubID = $db->escape($_GET['club']);
						$db->query("DELETE FROM `news_tag` WHERE `tag`='$clubID' AND `type`='cbe_location'");
						$db->query("DELETE FROM `location` WHERE `id`='$clubID'");
						$deletionSuccessful = true;
					}
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			$clubs = array();
			$search = $db->escape($_GET['search']);
			$result = $db->query("SELECT `id`, `tag` FROM `location` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
			while ($row = $db->fetchArray($result)) {
				$id = $row['id'];
				$tag = htmlentities($row['tag'], null, "ISO-8859-1");
				array_push($clubs, array('id'=>$id, 'tag'=>$tag));
			}
			require_once("template/cbe.clubs.tpl.php");
		}
	}
	
	public function edit($id) {
		$role = new Role();
		$auth = new Authentication();
		$authTime = time();
		$authToken = $auth->getToken($authTime);
		if ($auth->moduleAdminAllowed("cbe", $role->getRole())) {
			$db = new DB();
			$id = $db->escape($id);
			$nameconvertion = false;
			if (isset($_POST['action'])) {
				if ($_POST['action']=="name") {
					$nameconvertion = true;
				}
				if ($_POST['action']=="tagExists") {
					$nameconvertion = true;
				}
			}
			
			if ($nameconvertion) {
				if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					if (isset($_POST['tag'])) {
						$tag = $db->escape($_POST['tag']);
					}
					if (isset($_POST['do'])) {
						if ($_POST['do']=="autoRename") {
							$tag = $db->escape($_POST['autoTag']);
						}
					}
					if (($_POST['action']=="tagExists")||$db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')")) {
						if ($_POST['action']=="tagExists") {
							if ((($_POST['do']=="rename")||($_POST['do']=="autoRename"))&&$db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')")) {
								$result = $db->query("SELECT `id` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')");
								while ($row = $db->fetchArray($result)) {
									$duplicateID = $row['id'];
									$result2 = $db->query("SELECT `tag` FROM `location` WHERE `id`='$id'");
									while ($row2 = $db->fetchArray($result2)) {
										$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
										$i = 2;
										$autoTag = $tag." (".$i.")";
										while ($db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$autoTag' AND NOT(`id`='$id')")) {
											$i++;
											$autoTag = $tag." (".$i.")";
										}
										require_once("template/cbe.clubs.tag.tpl.php");
									}
								}
							}
							else {
								if ($_POST['do']=="saveDuplicate") {
									$duplicateID = $db->escape($_POST['duplicateID']);
									$result = $db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='cbe_location'");
									while ($row = $db->fetchArray($result)) {
										$newsID = $row['news'];
										$db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='cbe_location'");
									}
									$db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='cbe_location' AND `tag`='$id'");
									$db->query("DELETE FROM `location` WHERE `id`='$id'");
									$id = $duplicateID;
									require_once("template/cbe.clubs.edit.success.tpl.php");
								}
								if ($_POST['do']=="moveToDuplicate") {
									$targetTag = $db->escape($_POST['targetTag']);
									$duplicateID = $db->escape($_POST['duplicateID']);
									$result = $db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='cbe_location'");
									while ($row = $db->fetchArray($result)) {
										$newsID = $row['news'];
										$db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='cbe_location'");
									}
									$db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='cbe_location' AND `tag`='$duplicateID'");
									$db->query("DELETE FROM `location` WHERE `id`='$duplicateID'");
									$db->query("UPDATE `location` SET `tag`='$targetTag' WHERE `id`='$id'");
									require_once("template/cbe.clubs.edit.success.tpl.php");
								}
								if ($_POST['do']=="autoRename") {
									$db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
								if ($_POST['do']=="rename") {
									$db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
							}
						}
						else {
							$result = $db->query("SELECT `id` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')");
							while ($row = $db->fetchArray($result)) {
								$duplicateID = $row['id'];
								$result2 = $db->query("SELECT `tag` FROM `location` WHERE `id`='$id'");
								while ($row2 = $db->fetchArray($result2)) {
									$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
									$i = 2;
									$autoTag = $tag." (".$i.")";
									while ($db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$autoTag' AND NOT(`id`='$id')")) {
										$i++;
										$autoTag = $tag." (".$i.")";
									}
									require_once("template/cbe.clubs.tag.tpl.php");
								}
							}
						}
					}
					else {
						$db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
						require_once("template/cbe.clubs.edit.success.tpl.php");
					}
				}
			}
			else {
				if (isset($_POST['action'])) {
					if ($_POST['action']=="send") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$basic = new Basic();
							$street = $db->escape($_POST['street']);
							$number = $db->escape($_POST['number']);
							$zip = $db->escape($_POST['zip']);
							$city = $db->escape($_POST['city']);
							$country = $db->escape($_POST['country']);
							$capacity = $db->escape($_POST['capacity']);
							$info = $db->escape($basic->cleanHTML($_POST['info']));
							$db->query("UPDATE `location` SET `street`='$street', `number`='$number', `zip`='$zip', `city`='$city', `country`='$country', `capacity`='$capacity', `info`='$info' WHERE `id`='$id'");
						}
					}
				}
				$this->buildEditingForm($id);
			}
		}
	}
	
	private function buildEditingForm($id) {
		$auth = new Authentication();
		$authTime = time();
		$authToken = $auth->getToken($authTime);
		$db = new DB();
		$id = $db->escape($id);
		$news = array();
		$result = $db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='cbe_location' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
		while ($row = $db->fetchArray($result)) {
			$newsID = $row['news'];
			$headline = htmlentities($row['headline'], null, "ISO-8859-1");
			$title = htmlentities($row['title'], null, "ISO-8859-1");
			array_push($news, array('news'=>$newsID, 'headline'=>$headline, 'title'=>$title));
		}
		$result = $db->query("SELECT `tag`, `street`, `number`, `zip`, `city`, `country`, `capacity`, `info` FROM `location` WHERE `id`='$id'");
		while ($row = $db->fetchArray($result)) {
			$tag = htmlentities($row['tag'], null, "ISO-8859-1");
			$street = htmlentities($row['street'], null, "ISO-8859-1");
			$number = htmlentities($row['number'], null, "ISO-8859-1");
			$zip = htmlentities($row['zip'], null, "ISO-8859-1");
			$city = htmlentities($row['city'], null, "ISO-8859-1");
			$country = htmlentities($row['country'], null, "ISO-8859-1");
			$capacity = htmlentities($row['capacity'], null, "ISO-8859-1");
			$info = $row['info'];
			require_once("template/cbe.clubs.edit.tpl.php");
		}
	}
	
}
?>