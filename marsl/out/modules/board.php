<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/board/thread.php");
include_once(dirname(__FILE__)."/board/post.php");
include_once(dirname(__FILE__)."/module.php");

class Board implements Module {

	private $db;
	private $auth;
	private $role;
	private $boardRights;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;

		$curBoardRights = array();
		$result = $this->db->query("SELECT `role`, `board`, `read`, `write`, `extended`, `admin` FROM `rights_board` WHERE `read`='1' OR `write`='1' OR `extended`='1' OR `admin`='1'");
		while ($row = $this->db->fetchArray($result)) {
			$roleID = $row['role'];
			$board = $row['board'];
			$read = $row['read'] == 1;
			$write = $row['write'] == 1;
			$extended = $row['extended'] == 1;
			$admin = $row['admin'] == 1;

			if (array_key_exists($roleID, $curBoardRights) && array_key_exists($board, $curBoardRights[$roleID])) {
				$curBoardRights[$roleID][$board]['read'] = $curBoardRights[$roleID]['read'] || $read;
				$curBoardRights[$roleID][$board]['write'] = $curBoardRights[$roleID]['write'] || $write;
				$curBoardRights[$roleID][$board]['extended'] = $curBoardRights[$roleID]['extended'] || $extended;
				$curBoardRights[$roleID][$board]['admin'] = $curBoardRights[$roleID]['admin'] || $admin;
			}
			else {
				$curBoardRights[$roleID][$board]['read'] = $read;
				$curBoardRights[$roleID][$board]['write'] = $write;
				$curBoardRights[$roleID][$board]['extended'] = $extended;
				$curBoardRights[$roleID][$board]['admin'] = $admin;
			}
		}
		$this->boardRights = $curBoardRights;
	}
	
	/*
	 * Displays the boards of a global location.
	 */
	public function display() {
		$location = $this->db->escapeString($_GET['id']);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())) {
			if (isset($_GET['action'])) {
				$threadClass = new Thread($this->db, $this->auth, $this->role);
				$postClass = new Post($this->db, $this->auth, $this->role);
				if ($_GET['action']=="threads") {
					$threadClass->display();
				}
				if ($_GET['action']=="posts") {
					$postClass->display();
				}
				if ($_GET['action']=="edit") {
					$postClass->edit();
				}
				if ($_GET['action']=="answer") {
					$postClass->answer();
				}
				if ($_GET['action']=="newthread") {
					$threadClass->newThread();
				}
				if ($_GET['action']=="globalfix") {
					$threadClass->fixGlobal();
				}
				if ($_GET['action']=="localfix") {
					$threadClass->fixLocal();
				}
				if ($_GET['action']=="title") {
					$threadClass->changeTitle();
				}
				if ($_GET['action']=="move") {
					$threadClass->moveThread();
				}
				if ($_GET['action']=="delete") {
					$threadClass->delete();
				}
				if ($_GET['action']=="defix") {
					$threadClass->removeFixation();
				}
				if ($_GET['action']=="close") {
					$threadClass->close();
				}
				if ($_GET['action']=="open") {
					$threadClass->open();
				}
			}
			else {
				$user = new User($this->db, $this->role);
				$categories = array();
				$result = $this->db->query("SELECT `board`, `title`, `threadcount`, `postcount`, `description`, `type`, `location` FROM `board` WHERE `type` IN ('0', '1') AND `location`='$location' OR `location`IN (SELECT `board` FROM `board` WHERE `location`='$location') ORDER BY `type`, `pos`");
				while ($row = $this->db->fetchArray($result)) {
					$type = $row['type'];
					$board = $this->db->escapeString($row['board']);
					$title = htmlentities($row['title'], null, "ISO-8859-1");
                    if ($this->readAllowed($board, $this->role->getRole())) {
                        if ($type == 0) {
                            $categories[$board] = array('title'=>$title, 'boards'=>array());
                        }
						else if ($type == 1) {
							$threadcount = htmlentities($row['threadcount'], null, "ISO-8859-1");
							$postcount = htmlentities($row['postcount'], null, "ISO-8859-1");
							$description = htmlentities($row['description'], null, "ISO-8859-1");
							$category = $row['location'];
							$thread = "";
							$post = "";
							$threadTitle = "";
							$postTime = "";
							$postAuthor = "";
							$authorName = "";
							$page = "";
							$result3 = $this->db->query("SELECT `post`, `date`, `post`.`thread` AS `thread`, `title`, `post`.`author` AS postauthor FROM `post` JOIN `thread` ON (`thread`.`thread`=`post`.`thread`) WHERE `deleted`='0' AND `board`='$board' AND `type` IN ('0', '1', '2', '3') ORDER BY `date` DESC LIMIT 1");
							while ($row3 = $this->db->fetchArray($result3)) {
								$thread = htmlentities($row3['thread'], null, "ISO-8859-1");
								$post = htmlentities($row3['post'], null, "ISO-8859-1");
								$threadTitle = htmlentities($row3['title'], null, "ISO-8859-1");
								$dateTime->setTimestamp($row3['date']);
								$postTime = $dateTime->format("d\.m\.Y\, H\:i\:s");
								$postAuthor = htmlentities($row3['postauthor'], null, "ISO-8859-1");
								$authorName = htmlentities($user->getNickbyID($postAuthor), null, "ISO-8859-1");
								$page = 1;
								$result4 = $this->db->query("SELECT COUNT(`thread`) AS paging FROM `post` WHERE `thread`='$thread' AND `deleted`='0'");
								while ($row4 = $this->db->fetchArray($result4)) {
									$page = ceil($row4['paging']/10);
								}
							}
							$operators = array();
							$result3 = $this->db->query("SELECT `user` FROM `board_operator` WHERE `board`='$board'");
							while ($row3 = $this->db->fetchArray($result3)) {
								$operator = htmlentities($row3['user'], null, "ISO-8859-1");
								$operatorRole = $this->role->getRolebyUser($operator);
								if ($this->auth->moduleReadAllowed("board", $operatorRole)&&$this->auth->moduleWriteAllowed("board", $operatorRole)&&$this->auth->locationReadAllowed($location, $operatorRole)&&$this->auth->locationWriteAllowed($location, $operatorRole)&&$this->readAllowed($board, $operatorRole)&&$this->writeAllowed($board, $operatorRole)&&$this->extendedAllowed($board, $operatorRole)) {
									$operatorNick = htmlentities($user->getNickbyID($operator), null, "ISO-8859-1");
									array_push($operators, array('user'=>$operator, 'nickname'=>$operatorNick));
								}
							}
							array_push($categories[$category]['boards'], array('board'=>$board, 'title'=>$title, 'description'=>$description, 'threadcount'=>$threadcount, 'postcount'=>$postcount, 'thread'=>$thread, 'threadTitle'=>$threadTitle, 'page'=>$page, 'post'=>$post, 'date'=>$postTime, 'user'=>$postAuthor, 'nickname'=>$authorName, 'operators'=>$operators));
                        }
                    }
				}
				require_once("template/board.main.tpl.php");
			}
		}
	}
	
	/*
	 * Returns whether the logged in user is a global administrator in the board. Either as a module administrator or as a location administrator.
	 */
	public function isAdmin($boardID, $userID) {
		$location = $this->getLocation($boardID);
		$roleID = $this->role->getRoleByUser($userID);
		if (($this->adminAllowed($boardID, $roleID)&&$this->writeAllowed($boardID, $roleID)&&$this->readAllowed($boardID, $roleID))||($this->auth->moduleReadAllowed("board", $roleID)&&$this->auth->moduleWriteAllowed("board", $roleID)&&$this->auth->locationReadAllowed($location, $roleID)&&$this->auth->locationWriteAllowed($location, $roleID)&&($this->auth->locationAdminAllowed($location, $roleID)||$this->auth->moduleAdminAllowed("board", $roleID)))) {
			return true;
		}
		else {
			return false;
		}
	}
	
	/*
	 * Return whether the logged in user is a operator in the board.
	 */
	public function isOperator($boardID, $userID) {
		$boardID = $this->db->escapeString($boardID);
		$userID = $this->db->escapeString($userID);
		if ($this->db->isExisting("SELECT `user` FROM `board_operator` WHERE `board`='$boardID' AND `user`='$userID' LIMIT 1")) {
			$roleID = $this->role->getRolebyUser($userID);
			$location = $this->getLocation($boardID);
			if ($this->auth->moduleReadAllowed("board", $roleID)&&$this->auth->moduleWriteAllowed("board", $roleID)&&$this->auth->locationReadAllowed($location, $roleID)&&$this->auth->locationWriteAllowed($location, $roleID)&&$this->readAllowed($boardID, $roleID)&&$this->writeAllowed($boardID, $roleID)&&$this->extendedAllowed($boardID, $roleID)) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	/*
	 * Displays the administrator view of the board.
	 */
	public function admin() {
		if ($this->auth->moduleAdminAllowed("board", $this->role->getRole())) {
			if (isset($_GET['page'])) {
				if ($_GET['page']=="role") {
					$this->roleManagement();
				}
				if ($_GET['page']=="operator") {
					$this->operatorManagement();
				}
				if ($_GET['page']=="description") {
					$this->changeDescription();
				}
			}
			else {
				if (isset($_GET['action'])) {
					if ($_GET['action']=="change"&&(isset($_POST['board']))) {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$board = $this->db->escapeString($_POST['board']);
							if ($this->adminAllowed($board, $this->role->getRole())) {
								$type = "2";
								$location = "-1";
								$newLocation = $this->db->escapeString($_POST['location']);
								$result = $this->db->query("SELECT `type`, `location` FROM `board` WHERE `board`='$board'");
								while ($row = $this->db->fetchArray($result)) {
									$type = $row['type'];
									$location = $row['location'];
								}
								if ($type=="0") {
									if ($this->db->isExisting("SELECT `id` FROM `navigation` WHERE `module`='board' AND `id`='$newLocation' LIMIT 1")) {
										$location = $newLocation;
									}
								}
								if ($type=="1") {
									if ($this->db->isExisting("SELECT `board` FROM `board` WHERE `type`='0' AND `board`='$newLocation' LIMIT 1")) {
										$location = $newLocation;
									}
								}
								$pos = $this->db->escapeString($_POST['pos']);
								$title = $this->db->escapeString($_POST['title']);
								$this->db->query("UPDATE `board` SET `title`='$title', `pos`='$pos', `location`='$location' WHERE `board`='$board'");
							}
						}
					}
					if ($_GET['action']=="del") {
						if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
							$board = $this->db->escapeString($_GET['board']);
							if ($this->adminAllowed($board, $this->role->getRole())&&$this->extendedAllowed($board, $this->role->getRole())&&$this->writeAllowed($board, $this->role->getRole())&&$this->readAllowed($board, $this->role->getRole())) {
								$this->db->query("UPDATE `board` SET `type`='2' WHERE `board`='$board'");
							}
						}
					}
					if ($_GET['action']=="addcat") {
						if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
							$this->db->query("INSERT INTO `board`(`pos`, `title`,`type`) VALUES('0','Standard','0')");
							$board = $this->db->lastInsertedID();
							$this->setRights($this->role->getRole(), $board, '1', '1', '1', '1');
						}
					}
					if ($_GET['action']=="addboard") {
						if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
							$this->db->query("INSERT INTO `board`(`pos`, `title`,`type`,`threadcount`,`postcount`) VALUES('0','Standard','1','0','0')");
							$board = $this->db->lastInsertedID();
							$this->setRights($this->role->getRole(), $board, '1', '1', '1', '1');
						}
					}
				}
				
				$locations = array();
				$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='board' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->auth->locationAdminAllowed($row['id'], $this->role->getRole())) {
						$id = htmlentities($row['id'], null, "ISO-8859-1");
						$name = htmlentities($row['name'], null, "ISO-8859-1");
						array_push($locations, array('id'=>$id, 'name'=>$name));
					}
				}
				
				$categories = array();
				$result = $this->db->query("SELECT `board`, `pos`, `location`, `title` FROM `board` WHERE `type`='0' ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->adminAllowed($row['board'], $this->role->getRole())) {
						$board = htmlentities($row['board'], null, "ISO-8859-1");
						$pos = htmlentities($row['pos'], null, "ISO-8859-1");
						$location = htmlentities($row['location'], null, "ISO-8859-1");
						$title = htmlentities($row['title'], null, "ISO-8859-1");
						$boardAdmin = ($this->adminAllowed($row['board'], $this->role->getRole())&&$this->extendedAllowed($row['board'], $this->role->getRole())&&$this->writeAllowed($row['board'], $this->role->getRole())&&$this->readAllowed($row['board'], $this->role->getRole()));
						array_push($categories, array('boardAdmin'=>$boardAdmin, 'board'=>$board, 'pos'=>$pos, 'location'=>$location, 'title'=>$title));
					}
				}
				
				$boards = array();
				$result = $this->db->query("SELECT `board`, `pos`, `location`, `title` FROM `board` WHERE `type`='1' ORDER BY `pos`");
				while ($row = $this->db->fetchArray($result)) {
					if ($this->adminAllowed($row['board'], $this->role->getRole())) {
						$location = $this->db->escapeString($row['location']);
						$board = htmlentities($row['board'], null, "ISO-8859-1");
						$pos = htmlentities($row['pos'], null, "ISO-8859-1");
						$location = htmlentities($row['location'], null, "ISO-8859-1");
						$title = htmlentities($row['title'], null, "ISO-8859-1");
						$boardAdmin = ($this->adminAllowed($row['board'], $this->role->getRole())&&$this->extendedAllowed($row['board'], $this->role->getRole())&&$this->writeAllowed($row['board'], $this->role->getRole())&&$this->readAllowed($row['board'], $this->role->getRole()));
						array_push($boards, array('boardAdmin'=>$boardAdmin, 'location'=>$location, 'board'=>$board, 'pos'=>$pos, 'title'=>$title));
					}
				}
				$authTime = time();
				$authToken = $this->auth->getToken($authTime);
				require_once("template/board.main.tpl.php");
			}
		}
	}
	
	/*
	 * Sets the rights of a board.
	 */
	public function setRights($role, $board, $read, $write, $extended, $admin) {
		$role = $this->db->escapeString($role);
		$board = $this->db->escapeString($board);
		$read = $this->db->escapeString($read);
		$write = $this->db->escapeString($write);
		$extended = $this->db->escapeString($extended);
		$admin = $this->db->escapeString($admin);
		if ($this->db->isExisting("SELECT `board` FROM `rights_board` WHERE `role`= '$role' AND `board`='$board' LIMIT 1")) {
			$this->db->query("UPDATE `rights_board` SET `read` = '$read', `write` = '$write', `extended` = '$extended', `admin` = '$admin' WHERE `role` = '$role' AND `board` = '$board'");
		}
		else {
			$this->db->query("INSERT INTO `rights_board`(`role`,`board`,`read`,`write`,`extended`,`admin`) VALUES('$role','$board','$read','$write','$extended','$admin')");
		}
	}
	
	/*
	 * Manages which role has which right on a board.
	 */
	private function roleManagement() {
		$board = $this->db->escapeString($_GET['board']);
		if ($this->adminAllowed($board, $this->role->getRole())&&$this->extendedAllowed($board, $this->role->getRole())&&$this->writeAllowed($board, $this->role->getRole())&&$this->readAllowed($board, $this->role->getRole())) {
			$name = $this->getNameById($board);
			$roles = $this->role->getPossibleRoles($this->role->getRole());
			if (isset($_POST['change'])) {
				if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					foreach ($roles as $roleID) {
						if ($roleID!=$this->role->getRole()) {
							$read = isset($_POST[$roleID.'_read']);
							$write = isset($_POST[$roleID.'_write']);
							$extended = isset($_POST[$roleID.'_extended']);
							$admin = isset($_POST[$roleID.'_admin']);
							$this->setRights($roleID, $board, $read, $write, $extended, $admin);
						}
					}
				}
			}
			$rights = array();
			foreach ($roles as $roleID) {
				if ($roleID!=$this->role->getRole()) {
					$roleID = $this->db->escapeString($roleID);
					if ($this->db->isExisting("SELECT `board` FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board' LIMIT 1")) {
						$result = $this->db->query("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board'");
						while ($row = $this->db->fetchArray($result)) {
							$roleName = htmlentities($this->role->getNamebyID($row['role']), null, "ISO-8859-1");
							array_push($rights,array('name'=>$roleName,'role'=>htmlentities($row['role'], null, "ISO-8859-1"),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
						}
					}
					else {
						$roleName = htmlentities($this->role->getNamebyID($roleID), null, "ISO-8859-1");
						array_push($rights,array('name'=>$roleName,'role'=>htmlentities($roleID, null, "ISO-8859-1"),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
					}
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/board.role.tpl.php");
		}
	}
	
	/*
	 * Manages the operators of a board.
	 */
	private function operatorManagement() {
		$board = $this->db->escapeString($_GET['board']);
		if ($this->adminAllowed($board, $this->role->getRole())&&$this->auth->moduleAdminAllowed("board", $this->role->getRole())) {
			if (isset($_POST['add'])) {
				$operator = $this->db->escapeString($_POST['operator']);
				if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					if ($this->db->isExisting("SELECT `nickname` FROM `user` JOIN `rights_board` USING(`role`) WHERE `extended`='1' AND `read`='1' AND `write`='1' AND `admin`='0' AND `board`='$board' AND `user`='$operator' LIMIT 1")) {
						$this->db->query("INSERT INTO `board_operator`(`user`,`board`) VALUES('$operator','$board')");
					}
				}
			}
			if (isset($_GET['action'])) {
				if ($_GET['action']=="delete") {
					if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
						$operator = $this->db->escapeString($_GET['operator']);
						$this->db->query("DELETE FROM `board_operator` WHERE `user`='$operator' AND `board`='$board'");
					}
				}
			}
			$name = $this->getNameById($board);
			$operators = array();
			$boardOperators = array();
			$result = $this->db->query("SELECT `user`, `nickname` FROM `user` JOIN `rights_board` USING(`role`) WHERE `extended`='1' AND `read`='1' AND `write`='1' AND `admin`='0' AND `board`='$board'");
			while ($row = $this->db->fetchArray($result)) {
				$user = $this->db->escapeString($row['user']);
				$nickname = htmlentities($row['nickname'], null, "ISO-8859-1");
				if ($this->db->isExisting("SELECT `board` FROM `board_operator` WHERE `user`='$user' AND `board`='$board' LIMIT 1")) {
					array_push($boardOperators, array('user'=>$user, 'nickname'=>$nickname));
				}
				else {
					array_push($operators, array('user'=>$user, 'nickname'=>$nickname));
				}
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/board.operator.tpl.php");
		}
	}
	
	/*
	 * Changes the description of a board.
	 */
	public function changeDescription() {
		$board = $this->db->escapeString($_GET['board']);
		if ($this->adminAllowed($board, $this->role->getRole())) {
			if (isset($_POST['action'])) {
				if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$description = $this->db->escapeString($_POST['description']);
					$this->db->query("UPDATE `board` SET `description`='$description' WHERE `board`='$board' AND `type`='1'");
				}
			}
			$name = $this->getNameById($board);
			$description = "";
			$basic = new Basic($this->db, $this->auth, $this->role);
			$result = $this->db->query("SELECT `description` FROM `board` WHERE `board`='$board'");
			while ($row = $this->db->fetchArray($result)) {
				$description = $basic->cleanHTML($row['description']);
			}
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
			require_once("template/board.description.tpl.php");
		}
	}
	
	/*
	 * Get the name of a board by a given board ID.
	 */
	public function getNameById($board) {
		$board = $this->db->escapeString($board);
		$result = $this->db->query("SELECT `title` FROM `board` WHERE `board`='$board'");
		while ($row = $this->db->fetchArray($result)) {
			$title = $row['title'];
		}
		return $title;
	}
	
	/*
	 * Evaluates the right matrix.
	 */
	private function evalRights($right, $board, $roleID) {
		$hasRight = false;
		$roles = $this->role->getPossibleRoles($roleID);
		$rolesLength = sizeof($roles);
		for ($roleIdx = 0; !$hasRight && $roleIdx < $rolesLength; $roleIdx++) {
			$curRoleID = $roles[$roleIdx];
			if (array_key_exists($curRoleID, $this->boardRights) && array_key_exists($board, $this->boardRights[$curRoleID]) && array_key_exists($right, $this->boardRights[$curRoleID][$board])) {
				$hasRight = $this->boardRights[$curRoleID][$board][$right];
			}
		}
		return $hasRight;
	}
	
	/*
	 * Returns whether the role is allowed to read the board.
	 */
	public function readAllowed($board, $roleID) {
		return $this->evalRights("read", $board, $roleID);
	}
	
	/*
	 * Returns whether the role is allowed to write in the board.
	*/
	public function writeAllowed($board, $roleID) {
		return $this->evalRights("write", $board, $roleID);
	}
	
	/*
	 * Returns whether the role is allowed to do extended services in the board.
	*/
	public function extendedAllowed($board, $roleID) {
		return $this->evalRights("extended", $board, $roleID);
	}
	
	/*
	 * Returns whether the role is allowed to administrate the board.
	*/
	public function adminAllowed($board, $roleID) {
		return $this->evalRights("admin", $board, $roleID);
	}
	
	/*
	 * Get location of a board.
	 */
	public function getLocation($board) {
		$board = $this->db->escapeString($board);
		$location = "";
		$result = $this->db->query("SELECT `location`, `type` FROM `board` WHERE `board`='$board'");
		while ($row = $this->db->fetchArray($result)) {
			if ($row['type']=="1") {
				$board = $row['location'];
				$result2 = $this->db->query("SELECT `location` FROM `board` WHERE `board`='$board'");
				while ($row2 = $this->db->fetchArray($result2)) {
					$location = $row2['location'];
				}
			}
			else {
				$location = $row['location'];
			}
		}
		return $location;
	}
	
	/*
	 * Interface method stub.
	 */
	public function isSearchable() {
		return false;
	}
	
	/*
	 * Interface method stub.
	 */
	public function getSearchList() {
		return array();
	}
	
	/*
	 * Interface method stub.
	 */
	public function search($query, $type) {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function isTaggable() {
		return false;
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagList() {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function addTags($tagString, $type, $news) {
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagString($type, $news) {
	}
	
	public function getTags($type, $news) {
		return null;
	}
	
	public function displayTag($tagID, $type) {
	}
	
	public function getImage() {
		return null;
	}
	
	public function getTitle() {
		return null;
	}
	
}

?>