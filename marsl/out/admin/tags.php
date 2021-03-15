<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/role.php");
include_once (dirname(__FILE__)."/../user/auth.php");

class Tags {
	
	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}

	public function admin() {
		$user = new User($this->db, $this->role);
		if ($user->isHead()) {
			if (isset($_GET['action'])) {
				if ($_GET['action']=="edit") {
					$id = $this->db->escapeString($_GET['tagid']);
					$this->edit($id);
				}
			}
			else {
				$newEntry = false;
				$entrySuccessful = false;
				if (isset($_POST['action'])) {
					if ($_POST['action']=="newTag") {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$newEntry = true;
							$entry = $this->db->escapeString($_POST['entry']);
							if (!$this->db->isExisting("SELECT * FROM `general` WHERE `tag`='$entry' LIMIT 1")) {
								$this->db->query("INSERT INTO `general`(`tag`) VALUES('$entry')");
								$entrySuccessful = true;
							}
						}
					}
				}
				$deletionSuccessful = false;
				if (isset($_GET['action2'])) {
					if ($_GET['action2']=="delete") {
						if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
							$tagID = $this->db->escapeString($_GET['tagid']);
							$this->db->query("DELETE FROM `news_tag` WHERE `tag`='$tagID' AND `type`='general'");
							$this->db->query("DELETE FROM `general` WHERE `id`='$tagID'");
							$deletionSuccessful = true;
						}
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				$tags = array();
				$search = $this->db->escapeString($_GET['search']);
				$result = $this->db->query("SELECT `id`, `tag` FROM `general` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
				while ($row = $this->db->fetchArray($result)) {
					$id = $row['id'];
					$tag = htmlentities($row['tag'], null, "ISO-8859-1");
					array_push($tags, array('id'=>$id, 'tag'=>$tag));
				}
				require_once("template/tags.tpl.php");
			}
		}
	}
	
	private function edit($id) {
		$authTime = time();
		$authToken = $this->auth->getToken($authTime);
		$user = new User($this->db, $this->role);
		if ($user->isHead()) {
			$id = $this->db->escapeString($id);
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
				if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					if (isset($_POST['tag'])) {
						$tag = $this->db->escapeString($_POST['tag']);
					}
					if (isset($_POST['do'])) {
						if ($_POST['do']=="autoRename") {
							$tag = $this->db->escapeString($_POST['autoTag']);
						}
					}
					if (($_POST['action']=="tagExists")||$this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
						if ($_POST['action']=="tagExists") {
							if ((($_POST['do']=="rename")||($_POST['do']=="autoRename"))&&$this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
								$result = $this->db->query("SELECT `id` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')");
								while ($row = $this->db->fetchArray($result)) {
									$duplicateID = $row['id'];
									$result2 = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
									while ($row2 = $this->db->fetchArray($result2)) {
										$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
										$i = 2;
										$autoTag = $tag." (".$i.")";
										while ($this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
											$i++;
											$autoTag = $tag." (".$i.")";
										}
										require_once("template/tags.tag.tpl.php");
									}
								}
							}
							else {
								if ($_POST['do']=="saveDuplicate") {
									$duplicateID = $this->db->escapeString($_POST['duplicateID']);
									$result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='general'");
									while ($row = $this->db->fetchArray($result)) {
										$newsID = $row['news'];
										$this->db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='general'");
									}
									$this->db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='general' AND `tag`='$id'");
									$this->db->query("DELETE FROM `general` WHERE `id`='$id'");
									$id = $duplicateID;
									require_once("template/tags.edit.success.tpl.php");
								}
								if ($_POST['do']=="moveToDuplicate") {
									$targetTag = $this->db->escapeString($_POST['targetTag']);
									$duplicateID = $this->db->escapeString($_POST['duplicateID']);
									$result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='general'");
									while ($row = $this->db->fetchArray($result)) {
										$newsID = $row['news'];
										$this->db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='general'");
									}
									$this->db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='general' AND `tag`='$duplicateID'");
									$this->db->query("DELETE FROM `general` WHERE `id`='$duplicateID'");
									$this->db->query("UPDATE `general` SET `tag`='$targetTag' WHERE `id`='$id'");
									require_once("template/tags.edit.success.tpl.php");
								}
								if ($_POST['do']=="autoRename") {
									$this->db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
								if ($_POST['do']=="rename") {
									$this->db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
							}
						}
						else {
							$result = $this->db->query("SELECT `id` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')");
							while ($row = $this->db->fetchArray($result)) {
								$duplicateID = $row['id'];
								$result2 = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
								while ($row2 = $this->db->fetchArray($result2)) {
									$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
									$i = 2;
									$autoTag = $tag." (".$i.")";
									while ($this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
										$i++;
										$autoTag = $tag." (".$i.")";
									}
									require_once("template/tags.tag.tpl.php");
								}
							}
						}
					}
					else {
						$this->db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
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
		$authTime = time();
		$authToken = $this->auth->getToken($authTime);
		$id = $this->db->escapeString($id);
		$news = array();
		$result = $this->db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='general' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
		while ($row = $this->db->fetchArray($result)) {
			$newsID = $row['news'];
			$headline = htmlentities($row['headline'], null, "ISO-8859-1");
			$title = htmlentities($row['title'], null, "ISO-8859-1");
			array_push($news, array('news'=>$newsID, 'headline'=>$headline, 'title'=>$title));
		}
		$result = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
		while ($row = $this->db->fetchArray($result)) {
			$tag = htmlentities($row['tag'], null, "ISO-8859-1");
			require_once("template/tags.edit.tpl.php");
		}
	}
	
}

?>