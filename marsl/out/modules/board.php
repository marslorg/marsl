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
	
	/*
	 * Displays the boards of a global location.
	 */
	public function display() {
		$auth = new Authentication();
		$role = new Role();
		$location = mysql_real_escape_string($_GET['id']);
		if ($auth->moduleReadAllowed("board", $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())) {
			if (isset($_GET['action'])) {
				$threadClass = new Thread();
				$postClass = new Post();
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
				$db = new DB();
				$user = new User();
				$categories = array();
				$result = $db->query("SELECT `board`, `title` FROM `board` WHERE `type`='0' AND `location`='$location' ORDER BY `pos`");
				while ($row = mysql_fetch_array($result)) {
					$category = mysql_real_escape_string($row['board']);
					if ($this->readAllowed($category, $role->getRole())) {
						$catTitle = htmlentities($row['title']);
						$boards = array();
						$result2 = $db->query("SELECT `board`, `title`, `threadcount`, `postcount`, `description` FROM `board` WHERE `type`='1' AND `location`='$category' ORDER BY `pos`");
						while ($row2 = mysql_fetch_array($result2)) {
							$board = mysql_real_escape_string($row2['board']);
							if ($this->readAllowed($board, $role->getRole())) {
								$boardTitle = htmlentities($row2['title']);
								$threadcount = htmlentities($row2['threadcount']);
								$postcount = htmlentities($row2['postcount']);
								$description = $row2['description'];
								$thread = "";
								$post = "";
								$threadTitle = "";
								$postTime = "";
								$postAuthor = "";
								$authorName = "";
								$page = "";
								$result3 = $db->query("SELECT `post`, `date`, `post`.`thread` AS `thread`, `title`, `post`.`author` AS postauthor FROM `post` JOIN `thread` ON (`thread`.`thread`=`post`.`thread`) WHERE `deleted`='0' AND `board`='$board' AND (`type`='0' OR `type`='1' OR `type`='2' OR `type`='3') ORDER BY `date` DESC LIMIT 0,1");
								while ($row3 = mysql_fetch_array($result3)) {
									$thread = htmlentities($row3['thread']);
									$post = htmlentities($row3['post']);
									$threadTitle = htmlentities($row3['title']);
									$postTime = date("d\.m\.Y\, H\:i\:s", $row3['date']);
									$postAuthor = htmlentities($row3['postauthor']);
									$authorName = htmlentities($user->getNickbyID($postAuthor));
									$page = 1;
									$result4 = $db->query("SELECT COUNT(*) AS paging FROM `post` WHERE `thread`='$thread' AND `deleted`='0'");
									while ($row4 = mysql_fetch_array($result4)) {
										$page = ceil($row4['paging']/10);
									}
								}
								$operators = array();
								$result3 = $db->query("SELECT `user` FROM `board_operator` WHERE `board`='$board'");
								while ($row3 = mysql_fetch_array($result3)) {
									$operator = htmlentities($row3['user']);
									$operatorRole = $role->getRolebyUser($operator);
									if ($auth->moduleReadAllowed("board", $operatorRole)&&$auth->moduleWriteAllowed("board", $operatorRole)&&$auth->locationReadAllowed($location, $operatorRole)&&$auth->locationWriteAllowed($location, $operatorRole)&&$this->readAllowed($board, $operatorRole)&&$this->writeAllowed($board, $operatorRole)&&$this->extendedAllowed($board, $operatorRole)) {
										$operatorNick = htmlentities($user->getNickbyID($operator));
										array_push($operators, array('user'=>$operator, 'nickname'=>$operatorNick));
									}
								}
								array_push($boards, array('board'=>$board, 'title'=>$boardTitle, 'description'=>$description, 'threadcount'=>$threadcount, 'postcount'=>$postcount, 'thread'=>$thread, 'threadTitle'=>$threadTitle, 'page'=>$page, 'post'=>$post, 'date'=>$postTime, 'user'=>$postAuthor, 'nickname'=>$authorName, 'operators'=>$operators));
							}
						}
						array_push($categories, array('category'=>$category, 'title'=>$catTitle, 'boards'=>$boards));
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
		$db = new DB();
		$location = $this->getLocation($boardID);
		$role = new Role();
		$roleID = $role->getRoleByUser($userID);
		$auth = new Authentication();
		if (($this->adminAllowed($boardID, $roleID)&&$this->writeAllowed($boardID, $roleID)&&$this->readAllowed($boardID, $roleID))||($auth->moduleReadAllowed("board", $roleID)&&$auth->moduleWriteAllowed("board", $roleID)&&$auth->locationReadAllowed($location, $roleID)&&$auth->locationWriteAllowed($location, $roleID)&&($auth->locationAdminAllowed($location, $roleID)||$auth->moduleAdminAllowed("board", $roleID)))) {
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
		$db = new DB();
		$boardID = mysql_real_escape_string($boardID);
		$userID = mysql_real_escape_string($userID);
		if ($db->isExisting("SELECT `user` FROM `board_operator` WHERE `board`='$boardID' AND `user`='$userID'")) {
			$role = new Role();
			$roleID = $role->getRolebyUser($userID);
			$location = $this->getLocation($boardID);
			$auth = new Authentication();
			if ($auth->moduleReadAllowed("board", $roleID)&&$auth->moduleWriteAllowed("board", $roleID)&&$auth->locationReadAllowed($location, $roleID)&&$auth->locationWriteAllowed($location, $roleID)&&$this->readAllowed($boardID, $roleID)&&$this->writeAllowed($boardID, $roleID)&&$this->extendedAllowed($boardID, $roleID)) {
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
		$auth = new Authentication();
		$role = new Role();
		if ($auth->moduleAdminAllowed("board", $role->getRole())) {
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
				$db = new DB();
				if (isset($_GET['action'])) {
					if ($_GET['action']=="change"&&(isset($_POST['board']))) {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$board = mysql_real_escape_string($_POST['board']);
							if ($this->adminAllowed($board, $role->getRole())) {
								$type = "2";
								$location = "-1";
								$newLocation = mysql_real_escape_string($_POST['location']);
								$result = $db->query("SELECT `type`, `location` FROM `board` WHERE `board`='$board'");
								while ($row = mysql_fetch_array($result)) {
									$type = $row['type'];
									$location = $row['location'];
								}
								if ($type=="0") {
									if ($db->isExisting("SELECT * FROM `navigation` WHERE `module`='board' AND `id`='$newLocation'")) {
										$location = $newLocation;
									}
								}
								if ($type=="1") {
									if ($db->isExisting("SELECT * FROM `board` WHERE `type`='0' AND `board`='$newLocation'")) {
										$location = $newLocation;
									}
								}
								$pos = mysql_real_escape_string($_POST['pos']);
								$title = mysql_real_escape_string($_POST['title']);
								$db->query("UPDATE `board` SET `title`='$title', `pos`='$pos', `location`='$location' WHERE `board`='$board'");
							}
						}
					}
					if ($_GET['action']=="del") {
						if ($auth->checkToken($_GET['time'], $_GET['token'])) {
							$board = mysql_real_escape_string($_GET['board']);
							if ($this->adminAllowed($board, $role->getRole())&&$this->extendedAllowed($board, $role->getRole())&&$this->writeAllowed($board, $role->getRole())&&$this->readAllowed($board, $role->getRole())) {
								$db->query("UPDATE `board` SET `type`='2' WHERE `board`='$board'");
							}
						}
					}
					if ($_GET['action']=="addcat") {
						if ($auth->checkToken($_GET['time'], $_GET['token'])) {
							$db->query("INSERT INTO `board`(`pos`, `title`,`type`) VALUES('0','Standard','0')");
							$board = mysql_insert_id();
							$this->setRights($role->getRole(), $board, '1', '1', '1', '1');
						}
					}
					if ($_GET['action']=="addboard") {
						if ($auth->checkToken($_GET['time'], $_GET['token'])) {
							$db->query("INSERT INTO `board`(`pos`, `title`,`type`,`threadcount`,`postcount`) VALUES('0','Standard','1','0','0')");
							$board = mysql_insert_id();
							$this->setRights($role->getRole(), $board, '1', '1', '1', '1');
						}
					}
				}
				
				$locations = array();
				$result = $db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='board' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
				while ($row = mysql_fetch_array($result)) {
					if ($auth->locationAdminAllowed($row['id'], $role->getRole())) {
						$id = htmlentities($row['id']);
						$name = htmlentities($row['name']);
						array_push($locations, array('id'=>$id, 'name'=>$name));
					}
				}
				
				$categories = array();
				$result = $db->query("SELECT `board`, `pos`, `location`, `title` FROM `board` WHERE `type`='0' ORDER BY `pos`");
				while ($row = mysql_fetch_array($result)) {
					if ($this->adminAllowed($row['board'], $role->getRole())) {
						$board = htmlentities($row['board']);
						$pos = htmlentities($row['pos']);
						$location = htmlentities($row['location']);
						$title = htmlentities($row['title']);
						$boardAdmin = ($this->adminAllowed($row['board'], $role->getRole())&&$this->extendedAllowed($row['board'], $role->getRole())&&$this->writeAllowed($row['board'], $role->getRole())&&$this->readAllowed($row['board'], $role->getRole()));
						array_push($categories, array('boardAdmin'=>$boardAdmin, 'board'=>$board, 'pos'=>$pos, 'location'=>$location, 'title'=>$title));
					}
				}
				
				$boards = array();
				$result = $db->query("SELECT `board`, `pos`, `location`, `title` FROM `board` WHERE `type`='1' ORDER BY `pos`");
				while ($row = mysql_fetch_array($result)) {
					if ($this->adminAllowed($row['board'], $role->getRole())) {
						$location = mysql_real_escape_string($row['location']);
						$board = htmlentities($row['board']);
						$pos = htmlentities($row['pos']);
						$location = htmlentities($row['location']);
						$title = htmlentities($row['title']);
						$boardAdmin = ($this->adminAllowed($row['board'], $role->getRole())&&$this->extendedAllowed($row['board'], $role->getRole())&&$this->writeAllowed($row['board'], $role->getRole())&&$this->readAllowed($row['board'], $role->getRole()));
						array_push($boards, array('boardAdmin'=>$boardAdmin, 'location'=>$location, 'board'=>$board, 'pos'=>$pos, 'title'=>$title));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				require_once("template/board.main.tpl.php");
			}
		}
	}
	
	/*
	 * Sets the rights of a board.
	 */
	public function setRights($role, $board, $read, $write, $extended, $admin) {
		$db = new DB();
		$role = mysql_real_escape_string($role);
		$board = mysql_real_escape_string($board);
		$read = mysql_real_escape_string($read);
		$write = mysql_real_escape_string($write);
		$extended = mysql_real_escape_string($extended);
		$admin = mysql_real_escape_string($admin);
		if ($db->isExisting("SELECT * FROM `rights_board` WHERE `role`= '$role' AND `board`='$board'")) {
			$db->query("UPDATE `rights_board` SET `read` = '$read', `write` = '$write', `extended` = '$extended', `admin` = '$admin' WHERE `role` = '$role' AND `board` = '$board'");
		}
		else {
			$db->query("INSERT INTO `rights_board`(`role`,`board`,`read`,`write`,`extended`,`admin`) VALUES('$role','$board','$read','$write','$extended','$admin')");
		}
	}
	
	/*
	 * Return the right matrix of a given board and role.
	 */
	private function right($board, $roleID) {
		$role = new Role();
		$board = mysql_real_escape_string($board);
		$db = new DB();
		$rights['read'] = 0;
		$rights['write'] = 0;
		$rights['extended'] = 0;
		$rights['admin'] = 0;
		$roles = $role->getPossibleRoles($roleID);
		foreach ($roles as $roleID) {
			$roleID = mysql_real_escape_string($roleID);
			if ($db->isExisting("SELECT * FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board' AND `read`='1'")) {
				$rights['read'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board' AND `write`='1'")) {
				$rights['write'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board' AND `extended`='1'")) {
				$rights['extended'] = 1;
			}
			if ($db->isExisting("SELECT * FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board' AND `admin`='1'")) {
				$rights['admin'] = 1;
			}
		}
		return $rights;
	}
	
	/*
	 * Manages which role has which right on a board.
	 */
	private function roleManagement() {
		$role = new Role();
		$board = mysql_real_escape_string($_GET['board']);
		if ($this->adminAllowed($board, $role->getRole())&&$this->extendedAllowed($board, $role->getRole())&&$this->writeAllowed($board, $role->getRole())&&$this->readAllowed($board, $role->getRole())) {
			$auth = new Authentication();
			$db = new DB();
			$name = $this->getNameById($board);
			$roles = $role->getPossibleRoles($role->getRole());
			if (isset($_POST['change'])) {
				if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					foreach ($roles as $roleID) {
						if ($roleID!=$role->getRole()) {
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
				if ($roleID!=$role->getRole()) {
					$roleID = mysql_real_escape_string($roleID);
					if ($db->isExisting("SELECT * FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board'")) {
						$result = $db->query("SELECT * FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board'");
						while ($row = mysql_fetch_array($result)) {
							$roleName = htmlentities($role->getNamebyID($row['role']));
							array_push($rights,array('name'=>$roleName,'role'=>htmlentities($row['role']),'read'=>$row['read'],'write'=>$row['write'],'extended'=>$row['extended'],'admin'=>$row['admin']));
						}
					}
					else {
						$roleName = htmlentities($role->getNamebyID($roleID));
						array_push($rights,array('name'=>$roleName,'role'=>htmlentities($roleID),'read'=>"0",'write'=>"0",'extended'=>"0",'admin'=>"0"));
					}
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/board.role.tpl.php");
		}
	}
	
	/*
	 * Manages the operators of a board.
	 */
	private function operatorManagement() {
		$role = new Role();
		$board = mysql_real_escape_string($_GET['board']);
		$auth = new Authentication();
		if ($this->adminAllowed($board, $role->getRole())&&$auth->moduleAdminAllowed("board", $role->getRole())) {
			$db = new DB();
			if (isset($_POST['add'])) {
				$operator = mysql_real_escape_string($_POST['operator']);
				if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					if ($db->isExisting("SELECT `nickname` FROM `user` JOIN `rights_board` USING(`role`) WHERE `extended`='1' AND `read`='1' AND `write`='1' AND `admin`='0' AND `board`='$board' AND `user`='$operator'")) {
						$db->query("INSERT INTO `board_operator`(`user`,`board`) VALUES('$operator','$board')");
					}
				}
			}
			if (isset($_GET['action'])) {
				if ($_GET['action']=="delete") {
					if ($auth->checkToken($_GET['time'], $_GET['token'])) {
						$operator = mysql_real_escape_string($_GET['operator']);
						$db->query("DELETE FROM `board_operator` WHERE `user`='$operator' AND `board`='$board'");
					}
				}
			}
			$name = $this->getNameById($board);
			$operators = array();
			$boardOperators = array();
			$result = $db->query("SELECT `user`, `nickname` FROM `user` JOIN `rights_board` USING(`role`) WHERE `extended`='1' AND `read`='1' AND `write`='1' AND `admin`='0' AND `board`='$board'");
			while ($row = mysql_fetch_array($result)) {
				$user = mysql_real_escape_string($row['user']);
				$nickname = htmlentities($row['nickname']);
				if ($db->isExisting("SELECT * FROM `board_operator` WHERE `user`='$user' AND `board`='$board'")) {
					array_push($boardOperators, array('user'=>$user, 'nickname'=>$nickname));
				}
				else {
					array_push($operators, array('user'=>$user, 'nickname'=>$nickname));
				}
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/board.operator.tpl.php");
		}
	}
	
	/*
	 * Changes the description of a board.
	 */
	public function changeDescription() {
		$role = new Role();
		$board = mysql_real_escape_string($_GET['board']);
		$auth = new Authentication();
		if ($this->adminAllowed($board, $role->getRole())) {
			$db = new DB();
			if (isset($_POST['action'])) {
				if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$description = mysql_real_escape_string($_POST['description']);
					$db->query("UPDATE `board` SET `description`='$description' WHERE `board`='$board' AND `type`='1'");
				}
			}
			$name = $this->getNameById($board);
			$description = "";
			$basic = new Basic();
			$result = $db->query("SELECT `description` FROM `board` WHERE `board`='$board'");
			while ($row = mysql_fetch_array($result)) {
				$description = $basic->cleanHTML($row['description']);
			}
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			require_once("template/board.description.tpl.php");
		}
	}
	
	/*
	 * Get the name of a board by a given board ID.
	 */
	public function getNameById($board) {
		$board = mysql_real_escape_string($board);
		$db = new DB();
		$result = $db->query("SELECT `title` FROM `board` WHERE `board`='$board'");
		while ($row = mysql_fetch_array($result)) {
			$title = $row['title'];
		}
		return $title;
	}
	
	/*
	 * Evaluates the right matrix.
	 */
	private function evalRights($right, $board, $roleID) {
		$rights = $this->right($board, $roleID);
		if (empty($rights)) {
			return 0;
		}
		else {
			return $rights[$right];
		}
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
		$board = mysql_real_escape_string($board);
		$location = "";
		$db = new DB();
		$result = $db->query("SELECT `location`, `type` FROM `board` WHERE `board`='$board'");
		while ($row = mysql_fetch_array($result)) {
			if ($row['type']=="1") {
				$board = $row['location'];
				$result2 = $db->query("SELECT `location` FROM `board` WHERE `board`='$board'");
				while ($row2 = mysql_fetch_array($result2)) {
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
	
}