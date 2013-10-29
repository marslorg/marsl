<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class Tags {
	
	public function admin() {
		$auth = new Authentication();
		$role = new Role();
		$user = new User();
		if ($user->isHead()) {
			if (isset($_GET['action'])) {
				if ($_GET['action']=="edit") {
					$id = mysql_real_escape_string($_GET['tagid']);
					$this->edit($id);
				}
			}
			else {
				$db = new DB();
				$newEntry = false;
				$entrySuccessful = false;
				if (isset($_POST['action'])) {
					if ($_POST['action']=="newTag") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$newEntry = true;
							$entry = mysql_real_escape_string($_POST['entry']);
							if (!$db->isExisting("SELECT * FROM `general` WHERE `tag`='$entry'")) {
								$db->query("INSERT INTO `general`(`tag`) VALUES('$entry')");
								$entrySuccessful = true;
							}
						}
					}
				}
				$deletionSuccessful = false;
				if (isset($_GET['action2'])) {
					if ($_GET['action2']=="delete") {
						if ($auth->checkToken($_GET['time'], $_GET['token'])) {
							$tagID = mysql_real_escape_string($_GET['tagid']);
							$db->query("DELETE FROM `news_tag` WHERE `tag`='$tagID' AND `type`='general'");
							$db->query("DELETE FROM `general` WHERE `id`='$tagID'");
							$deletionSuccessful = true;
						}
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				$tags = array();
				$search = mysql_real_escape_string($_GET['search']);
				$result = $db->query("SELECT `id`, `tag` FROM `general` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
				while ($row = mysql_fetch_array($result)) {
					$id = $row['id'];
					$tag = htmlentities($row['tag'], null, "ISO-8859-1");
					array_push($tags, array('id'=>$id, 'tag'=>$tag));
				}
				require_once("template/tags.tpl.php");
			}
		}
	}
	
	private function edit($id) {
		$role = new Role();
		$auth = new Authentication();
		$authTime = time();
		$authToken = $auth->getToken($authTime);
		$user = new User();
		if ($user->isHead()) {
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
					if (($_POST['action']=="tagExists")||$db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')")) {
						if ($_POST['action']=="tagExists") {
							if ((($_POST['do']=="rename")||($_POST['do']=="autoRename"))&&$db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')")) {
								$result = $db->query("SELECT `id` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')");
								while ($row = mysql_fetch_array($result)) {
									$duplicateID = $row['id'];
									$result2 = $db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
									while ($row2 = mysql_fetch_array($result2)) {
										$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
										$i = 2;
										$autoTag = $tag." (".$i.")";
										while ($db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$autoTag' AND NOT(`id`='$id')")) {
											$i++;
											$autoTag = $tag." (".$i.")";
										}
										require_once("template/tags.tag.tpl.php");
									}
								}
							}
							else {
								if ($_POST['do']=="saveDuplicate") {
									$duplicateID = mysql_real_escape_string($_POST['duplicateID']);
									$result = $db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='general'");
									while ($row = mysql_fetch_array($result)) {
										$newsID = $row['news'];
										$db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='general'");
									}
									$db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='general' AND `tag`='$id'");
									$db->query("DELETE FROM `general` WHERE `id`='$id'");
									$id = $duplicateID;
									require_once("template/tags.edit.success.tpl.php");
								}
								if ($_POST['do']=="moveToDuplicate") {
									$targetTag = mysql_real_escape_string($_POST['targetTag']);
									$duplicateID = mysql_real_escape_string($_POST['duplicateID']);
									$result = $db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='general'");
									while ($row = mysql_fetch_array($result)) {
										$newsID = $row['news'];
										$db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='general'");
									}
									$db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='general' AND `tag`='$duplicateID'");
									$db->query("DELETE FROM `general` WHERE `id`='$duplicateID'");
									$db->query("UPDATE `general` SET `tag`='$targetTag' WHERE `id`='$id'");
									require_once("template/tags.edit.success.tpl.php");
								}
								if ($_POST['do']=="autoRename") {
									$db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
								if ($_POST['do']=="rename") {
									$db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
							}
						}
						else {
							$result = $db->query("SELECT `id` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')");
							while ($row = mysql_fetch_array($result)) {
								$duplicateID = $row['id'];
								$result2 = $db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
								while ($row2 = mysql_fetch_array($result2)) {
									$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
									$i = 2;
									$autoTag = $tag." (".$i.")";
									while ($db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$autoTag' AND NOT(`id`='$id')")) {
										$i++;
										$autoTag = $tag." (".$i.")";
									}
									require_once("template/tags.tag.tpl.php");
								}
							}
						}
					}
					else {
						$db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
						require_once("template/tags.edit.success.tpl.php");
					}
				}
			}
			else {
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
		$result = $db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='general' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
		while ($row = mysql_fetch_array($result)) {
			$newsID = $row['news'];
			$headline = htmlentities($row['headline'], null, "ISO-8859-1");
			$title = htmlentities($row['title'], null, "ISO-8859-1");
			array_push($news, array('news'=>$newsID, 'headline'=>$headline, 'title'=>$title));
		}
		$result = $db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
		while ($row = mysql_fetch_array($result)) {
			$tag = htmlentities($row['tag'], null, "ISO-8859-1");
			require_once("template/tags.edit.tpl.php");
		}
	}
	
}

?>