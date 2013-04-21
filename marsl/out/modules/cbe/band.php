<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");

class Band {
	
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
				if ($_POST['action']=="newBand") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$newEntry = true;
						$entry = mysql_real_escape_string($_POST['entry']);
						if (!$db->isExisting("SELECT * FROM `band` WHERE `tag`='$entry'")) {
							$db->query("INSERT INTO `band`(`tag`) VALUES('$entry')");
							$entrySuccessful = true;
						}
					}
				}
			}
			$deletionSuccessful = false;
			if (isset($_GET['action2'])) {
				if ($_GET['action2']=="delete") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						$bandID = mysql_real_escape_string($_GET['band']);
						$db->query("DELETE FROM `news_tag` WHERE `tag`='$bandID' AND `type`='cbe_band'");
						$db->query("DELETE FROM `band` WHERE `id`='$bandID'");
						$deletionSuccessful = true;
					}
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			$bands = array();
			$search = mysql_real_escape_string($_GET['search']);
			$result = $db->query("SELECT `id`, `tag` FROM `band` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
			while ($row = mysql_fetch_array($result)) {
				$id = $row['id'];
				$tag = htmlentities($row['tag']);
				array_push($bands, array('id'=>$id, 'tag'=>$tag));
			}
			require_once("template/cbe.bands.tpl.php");
		}
	}
	
	public function edit($id) {
		$role = new Role();
		$auth = new Authentication();
		$authTime = time();
		$authToken = $auth->getToken($authTime);
		if ($auth->moduleAdminAllowed("cbe", $role->getRole())) {
			$id = mysql_real_escape_string($id);
			$db = new DB();
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
						$tag = mysql_real_escape_string($_POST['tag']);
					}
					if (isset($_POST['do'])) {
						if ($_POST['do']=="autoRename") {
							$tag = mysql_real_escape_string($_POST['autoTag']);
						}
					}
					if (($_POST['action']=="tagExists")||$db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id')")) {
						if ($_POST['action']=="tagExists") {
							if ((($_POST['do']=="rename")||($_POST['do']=="autoRename"))&&$db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id')")) {
								$result = $db->query("SELECT `id` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id')");
								while ($row = mysql_fetch_array($result)) {
									$duplicateID = $row['id'];
									$result2 = $db->query("SELECT `tag` FROM `band` WHERE `id`='$id'");
									while ($row2 = mysql_fetch_array($result2)) {
										$oldTag = htmlentities($row2['tag']);
										$i = 2;
										$autoTag = $tag." (".$i.")";
										while ($db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$autoTag' AND NOT(`id`='$id')")) {
											$i++;
											$autoTag = $tag." (".$i.")";
										}
										require_once("template/cbe.bands.tag.tpl.php");
									}
								}
							}
							else {
								if ($_POST['do']=="saveDuplicate") {
									$duplicateID = mysql_real_escape_string($_POST['duplicateID']);
									$result = $db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='cbe_band'");
									while ($row = mysql_fetch_array($result)) {
										$newsID = $row['news'];
										$db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='cbe_band'");
									}
									$db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='cbe_band' AND `tag`='$id'");
									$db->query("DELETE FROM `band` WHERE `id`='$id'");
									$id = $duplicateID;
									require_once("template/cbe.bands.edit.success.tpl.php");
								}
								if ($_POST['do']=="moveToDuplicate") {
									$targetTag = mysql_real_escape_string($_POST['targetTag']);
									$duplicateID = mysql_real_escape_string($_POST['duplicateID']);
									$result = $db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='cbe_band'");
									while ($row = mysql_fetch_array($result)) {
										$newsID = $row['news'];
										$db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='cbe_band'");
									}
									$db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='cbe_band' AND `tag`='$duplicateID'");
									$db->query("DELETE FROM `band` WHERE `id`='$duplicateID'");
									$db->query("UPDATE `band` SET `tag`='$targetTag' WHERE `id`='$id'");
									require_once("template/cbe.bands.edit.success.tpl.php");
								}
								if ($_POST['do']=="autoRename") {
									$db->query("UPDATE `band` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
								if ($_POST['do']=="rename") {
									$db->query("UPDATE `band` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
							}
						}
						else {
							$result = $db->query("SELECT `id` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id')");
							while ($row = mysql_fetch_array($result)) {
								$duplicateID = $row['id'];
								$result2 = $db->query("SELECT `tag` FROM `band` WHERE `id`='$id'");
								while ($row2 = mysql_fetch_array($result2)) {
									$oldTag = htmlentities($row2['tag']);
									$i = 2;
									$autoTag = $tag." (".$i.")";
									while ($db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$autoTag' AND NOT(`id`='$id')")) {
										$i++;
										$autoTag = $tag." (".$i.")";
									}
									require_once("template/cbe.bands.tag.tpl.php");
								}
							}
						}
					}
					else {
						$db->query("UPDATE `band` SET `tag`='$tag' WHERE `id`='$id'");
						require_once("template/cbe.bands.edit.success.tpl.php");
					}
				}
			}
			else {
				if (isset($_POST['action'])) {
					if ($_POST['action']=="send") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$basic = new Basic();
							$founded = mysql_real_escape_string($_POST['founded']);
							$ended = mysql_real_escape_string($_POST['ended']);
							$info = mysql_real_escape_string($basic->cleanHTML($_POST['info']));
							$db->query("UPDATE `band` SET `founded`='$founded', `ended`='$ended', `info`='$info' WHERE `id`='$id'");
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
		$id = mysql_real_escape_string($id);
		$db = new DB();
		$news = array();
		$result = $db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='cbe_band' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
		while ($row = mysql_fetch_array($result)) {
			$newsID = $row['news'];
			$headline = htmlentities($row['headline']);
			$title = htmlentities($row['title']);
			array_push($news, array('news'=>$newsID, 'headline'=>$headline, 'title'=>$title));
		}
		$result = $db->query("SELECT `tag`, `founded`, `ended`, `info` FROM `band` WHERE `id`='$id'");
		while ($row = mysql_fetch_array($result)) {
			$tag = htmlentities($row['tag']);
			$founded = htmlentities($row['founded']);
			$ended = htmlentities($row['ended']);
			$info = $row['info'];
			require_once("template/cbe.bands.edit.tpl.php");
		}
	}
	
}
?>