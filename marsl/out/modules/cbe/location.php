<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");

class Location {

	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}
	
	public function display() {
		
	}
	
	public function admin() {
		if ($this->auth->moduleAdminAllowed("cbe", $this->role->getRole())) {
			$newEntry = false;
			$entrySuccessful = false;
			if (isset($_POST['action'])) {
				if ($_POST['action']=="newClub") {
					if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$newEntry = true;
						$entry = $this->db->escapeString($_POST['entry']);
						if (!$this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$entry' LIMIT 1")) {
							$this->db->query("INSERT INTO `location`(`tag`) VALUES('$entry')");
							$entrySuccessful = true;
						}
					}
				}
			}
			$deletionSuccessful = false;
			if (isset($_GET['action2'])) {
				if ($_GET['action2']=="delete") {
					if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
						$clubID = $this->db->escapeString($_GET['club']);
						$this->db->query("DELETE FROM `news_tag` WHERE `tag`='$clubID' AND `type`='cbe_location'");
						$this->db->query("DELETE FROM `location` WHERE `id`='$clubID'");
						$deletionSuccessful = true;
					}
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			$clubs = array();
			$search = $this->db->escapeString($_GET['search']);
			$result = $this->db->query("SELECT `id`, `tag` FROM `location` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
			while ($row = $this->db->fetchArray($result)) {
				$id = $row['id'];
				$tag = htmlentities($row['tag'], null, "ISO-8859-1");
				array_push($clubs, array('id'=>$id, 'tag'=>$tag));
			}
			require_once("template/cbe.clubs.tpl.php");
		}
	}
	
	public function edit($id) {
		$authTime = time();
		$authToken = $this->auth->getToken($authTime);
		if ($this->auth->moduleAdminAllowed("cbe", $this->role->getRole())) {
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
					if (($_POST['action']=="tagExists")||$this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
						if ($_POST['action']=="tagExists") {
							if ((($_POST['do']=="rename")||($_POST['do']=="autoRename"))&&$this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
								$result = $this->db->query("SELECT `id` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')");
								while ($row = $this->db->fetchArray($result)) {
									$duplicateID = $row['id'];
									$result2 = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$id'");
									while ($row2 = $this->db->fetchArray($result2)) {
										$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
										$i = 2;
										$autoTag = $tag." (".$i.")";
										while ($this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
											$i++;
											$autoTag = $tag." (".$i.")";
										}
										require_once("template/cbe.clubs.tag.tpl.php");
									}
								}
							}
							else {
								if ($_POST['do']=="saveDuplicate") {
									$duplicateID = $this->db->escapeString($_POST['duplicateID']);
									$result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='cbe_location'");
									while ($row = $this->db->fetchArray($result)) {
										$newsID = $row['news'];
										$this->db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='cbe_location'");
									}
									$this->db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='cbe_location' AND `tag`='$id'");
									$this->db->query("DELETE FROM `location` WHERE `id`='$id'");
									$id = $duplicateID;
									require_once("template/cbe.clubs.edit.success.tpl.php");
								}
								if ($_POST['do']=="moveToDuplicate") {
									$targetTag = $this->db->escapeString($_POST['targetTag']);
									$duplicateID = $this->db->escapeString($_POST['duplicateID']);
									$result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='cbe_location'");
									while ($row = $this->db->fetchArray($result)) {
										$newsID = $row['news'];
										$this->db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='cbe_location'");
									}
									$this->db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='cbe_location' AND `tag`='$duplicateID'");
									$this->db->query("DELETE FROM `location` WHERE `id`='$duplicateID'");
									$this->db->query("UPDATE `location` SET `tag`='$targetTag' WHERE `id`='$id'");
									require_once("template/cbe.clubs.edit.success.tpl.php");
								}
								if ($_POST['do']=="autoRename") {
									$this->db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
								if ($_POST['do']=="rename") {
									$this->db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
									$this->buildEditingForm($id);
								}
							}
						}
						else {
							$result = $this->db->query("SELECT `id` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')");
							while ($row = $this->db->fetchArray($result)) {
								$duplicateID = $row['id'];
								$result2 = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$id'");
								while ($row2 = $this->db->fetchArray($result2)) {
									$oldTag = htmlentities($row2['tag'], null, "ISO-8859-1");
									$i = 2;
									$autoTag = $tag." (".$i.")";
									while ($this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
										$i++;
										$autoTag = $tag." (".$i.")";
									}
									require_once("template/cbe.clubs.tag.tpl.php");
								}
							}
						}
					}
					else {
						$this->db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
						require_once("template/cbe.clubs.edit.success.tpl.php");
					}
				}
			}
			else {
				if (isset($_POST['action'])) {
					if ($_POST['action']=="send") {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$basic = new Basic($this->db, $this->auth, $this->role);
							$street = $this->db->escapeString($_POST['street']);
							$number = $this->db->escapeString($_POST['number']);
							$zip = $this->db->escapeString($_POST['zip']);
							$city = $this->db->escapeString($_POST['city']);
							$country = $this->db->escapeString($_POST['country']);
							$capacity = $this->db->escapeString($_POST['capacity']);
							$info = $this->db->escapeString($basic->cleanHTML($_POST['info']));
							$this->db->query("UPDATE `location` SET `street`='$street', `number`='$number', `zip`='$zip', `city`='$city', `country`='$country', `capacity`='$capacity', `info`='$info' WHERE `id`='$id'");
						}
					}
				}
				$this->buildEditingForm($id);
			}
		}
	}
	
	private function buildEditingForm($id) {
		$authTime = time();
		$authToken = $this->auth->getToken($authTime);
		$id = $this->db->escapeString($id);
		$news = array();
		$result = $this->db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='cbe_location' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
		while ($row = $this->db->fetchArray($result)) {
			$newsID = $row['news'];
			$headline = htmlentities($row['headline'], null, "ISO-8859-1");
			$title = htmlentities($row['title'], null, "ISO-8859-1");
			array_push($news, array('news'=>$newsID, 'headline'=>$headline, 'title'=>$title));
		}
		$result = $this->db->query("SELECT `tag`, `street`, `number`, `zip`, `city`, `country`, `capacity`, `info` FROM `location` WHERE `id`='$id'");
		while ($row = $this->db->fetchArray($result)) {
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