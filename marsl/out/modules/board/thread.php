<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../board.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");

class Thread {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}
	
	/*
	 * Displays the thread overview in a board.
	 */
	public function display() {
		$location = $_GET['id'];
		$boardID = $this->db->escapeString($_GET['board']);
		$user = new User($this->db);
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$board = new Board($this->db);
		if (($location==$board->getLocation($boardID))&&($auth->moduleReadAllowed("board", $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$board->readAllowed($boardID, $role->getRole()))) {
			$writeAllowed = $auth->moduleWriteAllowed("board", $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$board->readAllowed($boardID, $role->getRole());
			$globals = array();
			$fixeds = array();
			$page = 1;
			if (!isset($_GET['page'])) {
				$globals = $this->getGlobals();
				$fixeds = $this->getFixeds();
			}
			else {
				$page = $_GET['page'];
				if ($_GET['page']=="1") {
					$globals = $this->getGlobals();
					$fixeds = $this->getFixeds();
				}
			}
			$threads = array();
			$result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date`, `type` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE (`type`='0' OR `type`='3') AND `board`='$boardID'");
			$pages = $this->db->getRowCount($result)/15;
			$start = $page*15-15;
			$end = 15;
			$result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date`, `type` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE (`type`='0' OR `type`='3') AND `board`='$boardID' ORDER BY `date` DESC LIMIT $start,$end");
			while ($row = $this->db->fetchArray($result)) {
				$thread = $row['thread'];
				$post = $row['post'];
				$postcount = $row['postcount']-1;
				$title = htmlentities($row['title'], null, "ISO-8859-1");
				$postAuthor = $row['postauthor'];
				$threadAuthor = $row['threadauthor'];
				$postNickname = htmlentities($user->getNickbyID($postAuthor), null, "ISO-8859-1");
				$threadNickname = htmlentities($user->getNickbyID($threadAuthor), null, "ISO-8859-1");
				$viewcount = $row['viewcount'];
				$date = date("d\.m\.Y\, H\:i\:s", $row['date']);
				$type = "closed";
				if ($row['type']=="0") {
					$type = "open";
				}
				$curPage = $this->getPageNumber($thread);
				array_push($threads, array('page'=>$curPage, 'post'=>$post, 'thread'=>$thread, 'postcount'=>$postcount, 'title'=>$title, 'postAuthor'=>$postAuthor, 'threadAuthor'=>$threadAuthor, 'postNickname'=>$postNickname, 'threadNickname'=>$threadNickname, 'viewcount'=>$viewcount, 'date'=>$date, 'type'=>$type));
			}
			require_once("template/board.threads.tpl.php");
		}
	}
	
	/*
	 * Get type of the thread.
	 * 0 = normal
	 * 1 = fixed
	 * 2 = globally fixed
	 * 3 = closed
	 * 4 = deleted
	 */
	public function getType($thread) {
		$type = "4";
		$thread = $this->db->escapeString($thread);
		$result = $this->db->query("SELECT `type` FROM `thread` WHERE `thread`='$thread'");
		while ($row = $this->db->fetchArray($result)) {
			$type = $row['type'];
		}
		return $type;
	}
	
	/*
	 * Get the number of pages a thread is containing.
	 */
	public function getPageNumber($thread) {
		$thread = $this->db->escapeString($thread);
		$result = $this->db->query("SELECT `post` FROM `post` WHERE `thread`='$thread' AND `deleted`='0'");
		$pages = ceil($this->db->getRowCount($result)/10);
		return $pages;
	}
	
	/*
	 * Get the title of a thread.
	 */
	public function getTitle($thread) {
		$thread = $this->db->escapeString($thread);
		$title = "";
		$result = $this->db->query("SELECT `title` FROM `thread` WHERE `thread`='$thread' AND NOT (`type`='4')");
		while ($row = $this->db->fetchArray($result)) {
			$title = htmlentities($row['title'], null, "ISO-8859-1");
		}
		return $title;
	}
	
	/*
	 * Get globally fixed threads.
	 */
	private function getGlobals() {
		$user = new User($this->db);
		$role = new Role($this->db);
		$auth = new Authentication($this->db);
		$board = new Board($this->db);
		$globals = array();
		$result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type`='2' ORDER BY `date` DESC");
		while ($row = $this->db->fetchArray($result)) {
			if ($auth->locationReadAllowed($board->getLocation($row['board']), $role->getRole())&&$board->readAllowed($row['board'], $role->getRole())) {
				$thread = $row['thread'];
				$post = $row['post'];
				$postcount = $row['postcount']-1;
				$title = htmlentities($row['title'], null, "ISO-8859-1");
				$postAuthor = $row['postauthor'];
				$threadAuthor = $row['threadauthor'];
				$postNickname = htmlentities($user->getNickbyID($postAuthor), null, "ISO-8859-1");
				$threadNickname = htmlentities($user->getNickbyID($threadAuthor), null, "ISO-8859-1");
				$viewcount = $row['viewcount'];
				$date = date("d\.m\.Y\, H\:i\:s", $row['date']);
				$page = $this->getPageNumber($thread);
				array_push($globals, array('page'=>$page, 'post'=>$post, 'thread'=>$thread, 'postcount'=>$postcount, 'title'=>$title, 'postAuthor'=>$postAuthor, 'threadAuthor'=>$threadAuthor, 'postNickname'=>$postNickname, 'threadNickname'=>$threadNickname, 'viewcount'=>$viewcount, 'date'=>$date));
			}
		}
		return $globals;
	}
	
	/*
	 * Get fixed threads.
	 */
	private function getFixeds() {
		$board = $this->db->escapeString($_GET['board']);
		$user = new User($this->db);
		$fixeds = array();
		$result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type`='1' AND `board`='$board' ORDER BY `date` DESC");
		while ($row = $this->db->fetchArray($result)) {
			$thread = $row['thread'];
			$post = $row['post'];
			$postcount = $row['postcount']-1;
			$title = htmlentities($row['title'], null, "ISO-8859-1");
			$postAuthor = $row['postauthor'];
			$threadAuthor = $row['threadauthor'];
			$postNickname = htmlentities($user->getNickbyID($postAuthor), null, "ISO-8859-1");
			$threadNickname = htmlentities($user->getNickbyID($threadAuthor), null, "ISO-8859-1");
			$viewcount = $row['viewcount'];
			$date = date("d\.m\.Y\, H\:i\:s", $row['date']);
			$page = $this->getPageNumber($thread);
			array_push($fixeds, array('page'=>$page, 'post'=>$post, 'thread'=>$thread, 'postcount'=>$postcount, 'title'=>$title, 'postAuthor'=>$postAuthor, 'threadAuthor'=>$threadAuthor, 'postNickname'=>$postNickname, 'threadNickname'=>$threadNickname, 'viewcount'=>$viewcount, 'date'=>$date));
		}
		return $fixeds;
	}
	
	/*
	 * Get the parent board of a thread.
	 */
	public function getBoard($thread) {
		$thread = $this->db->escapeString($thread);
		$board = "";
		$result = $this->db->query("SELECT `board` FROM `thread` WHERE `thread`='$thread'");
		while ($row = $this->db->fetchArray($result)) {
			$board = $row['board'];
		}
		return $board;
	}
	
	/*
	 * Move thread to another board.
	 */
	public function moveThread() {
		$auth = new Authentication($this->db);
		$location = $_GET['id'];
		$threadID = $this->db->escapeString($_GET['thread']);
		$board = new Board($this->db);
		$boardID = $this->getBoard($threadID);
		$role = new Role($this->db);
		$user = new User($this->db);
		$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
		$isAdmin = $board->isAdmin($boardID, $user->getID());
		$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
			if (isset($_POST['do'])) {
				if ($_POST['do']=="move") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$destinationID = $this->db->escapeString($_POST['destination']);
						if ($board->readAllowed($destinationID, $role->getRole())&&$board->writeAllowed($destinationID, $role->getRole())&&$auth->locationReadAllowed($board->getLocation($destinationID), $role->getRole())&&$auth->locationWriteAllowed($board->getLocation($destinationID), $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())) {
							$this->db->query("UPDATE `thread` SET `board`='$destinationID' WHERE `thread`='$threadID'");
							
							$result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
							while ($row = $this->db->fetchArray($result)) {
								$postcount = $row['postcount'];
								$result2 = $this->db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$boardID'");
								while ($row2 = $this->db->fetchArray($result2)) {
									$threadcount = $row2['threadcount']-1;
									$newPostcount = $row2['postcount']-$postcount;
									$this->db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$newPostcount' WHERE `board`='$boardID'");
								}
								$result2 = $this->db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$destinationID'");
								while ($row2 = $this->db->fetchArray($result2)) {
									$threadcount = $row2['threadcount']+1;
									$newPostcount = $row2['postcount']+$postcount;
									$this->db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$newPostcount' WHERE `board`='$destinationID'");
								}
							}
														
							$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
							echo "<div class=\"success\">Das Thema wurde verschoben! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
						}
					}
				}
			}
			else {
				$boards = array();
				$result = $this->db->query("SELECT `board`, `title` FROM `board` WHERE `type`='1'");
				while ($row = $this->db->fetchArray($result)) {
					$destinationID = $row['board'];
					$destinationTitle = htmlentities($row['title'], null, "ISO-8859-1");
					if ($board->readAllowed($destinationID, $role->getRole())&&$board->writeAllowed($destinationID, $role->getRole())&&$auth->locationReadAllowed($board->getLocation($destinationID), $role->getRole())&&$auth->locationWriteAllowed($board->getLocation($destinationID), $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())) {
						array_push($boards, array('board'=>$destinationID, 'title'=>$destinationTitle));
					}
				}
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				$title = $this->getTitle($threadID);
				require_once("template/board.move.tpl.php");
			}
		}
	}
	
	/*
	 * Change the title of a thread.
	 */
	public function changeTitle() {
		$auth = new Authentication($this->db);
		$location = $_GET['id'];
		$threadID = $this->db->escapeString($_GET['thread']);
		$board = new Board($this->db);
		$boardID = $this->getBoard($threadID);
		$role = new Role($this->db);
		$user = new User($this->db);
		$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
		$isAdmin = $board->isAdmin($boardID, $user->getID());
		$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
			if (isset($_POST['do'])) {
				if ($_POST['do']=="change") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$this->db = new DB();
						$title = $this->db->escapeString($_POST['title']);
						$this->db->query("UPDATE `thread` SET `title`='$title' WHERE `thread`='$threadID'");
						$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
						echo "<div class=\"success\">Der Titel wurde ge&auml;ndert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
					}
				}
			}
			else {
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				$title = $this->getTitle($threadID);
				require_once("template/board.change.tpl.php");
			}
		}
	}
	
	/*
	 * Open up a closed thread.
	 */
	public function open() {
		$auth = new Authentication($this->db);
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $this->db->escapeString($_GET['thread']);
			$board = new Board($this->db);
			$boardID = $this->getBoard($threadID);
			$role = new Role($this->db);
			$user = new User($this->db);
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$this->db->query("UPDATE `thread` SET `type`='0' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde ge&ouml;ffnet! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Close a thread.
	 */
	public function close() {
		$auth = new Authentication($this->db);
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $this->db->escapeString($_GET['thread']);
			$board = new Board($this->db);
			$boardID = $this->getBoard($threadID);
			$role = new Role($this->db);
			$user = new User($this->db);
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$this->db->query("UPDATE `thread` SET `type`='3' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde geschlossen! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Delete a thread.
	 */
	public function delete() {
		$auth = new Authentication($this->db);
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $this->db->escapeString($_GET['thread']);
			$board = new Board($this->db);
			$boardID = $this->getBoard($threadID);
			$role = new Role($this->db);
			$user = new User($this->db);
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$this->db->query("UPDATE `thread` SET `type`='4' WHERE `thread`='$threadID'");
				$result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
				while ($row = $this->db->fetchArray($result)) {
					$postcount = $row['postcount'];
					$result2 = $this->db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$boardID'");
					while ($row2 = $this->db->fetchArray($result2)) {
						$threadcount = $row2['threadcount']-1;
						$postcount = $row2['postcount']-$postcount;
						$this->db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$postcount' WHERE `board`='$boardID'");
					}
				}
				$link = "index.php?id=".$location."&action=threads&board=".$boardID;
				echo "<div class=\"success\">Das Thema wurde gel&ouml;scht! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Globally fix a thread.
	 */
	public function fixGlobal() {
		$auth = new Authentication($this->db);
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $this->db->escapeString($_GET['thread']);
			$board = new Board($this->db);
			$boardID = $this->getBoard($threadID);
			$role = new Role($this->db);
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&$isGlobalAdmin) {
				$this->db->query("UPDATE `thread` SET `type`='2' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde global fixiert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Locally fix a thread.
	 */
	public function fixLocal() {
		$auth = new Authentication($this->db);
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $this->db->escapeString($_GET['thread']);
			$board = new Board($this->db);
			$boardID = $this->getBoard($threadID);
			$role = new Role($this->db);
			$user = new User($this->db);
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$this->db->query("UPDATE `thread` SET `type`='1' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde fixiert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Remove all fixations of a thread.
	 */
	public function removeFixation() {
		$auth = new Authentication($this->db);
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $this->db->escapeString($_GET['thread']);
			$board = new Board($this->db);
			$boardID = $this->getBoard($threadID);
			$role = new Role($this->db);
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&$isGlobalAdmin) {
				$this->db->query("UPDATE `thread` SET `type`='0' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Die Ank&uuml;ndigung wurde aufgehoben! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Create a new thread.
	 */
	public function newThread() {
		$board = new Board($this->db);
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		$user = new User($this->db);
		$boardID = $this->db->escapeString($_GET['board']);
		$basic = new Basic($this->db);
		$location = $this->db->escapeString($_GET['id']);
		$isAdmin = ($board->isAdmin($boardID, $user->getID())||$auth->moduleAdminAllowed("board", $role->getRole())||$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&$board->readAllowed($boardID, $role->getRole())&&$board->writeAllowed($boardID, $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$auth->locationWriteAllowed($location, $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())) {
			if (isset($_POST['do'])) {
				if ($_POST['do']=="newthread") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$title = $this->db->escapeString($_POST['title']);
						$content = $this->db->escapeString($basic->cleanStrict($_POST['content']));
						$author = $this->db->escapeString($user->getID());
						$time = $this->db->escapeString(time());
						$ip = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
						$this->db->query("INSERT INTO `thread`(`board`,`postcount`,`type`,`title`,`author`,`viewcount`) VALUES('$boardID','0','0','$title','$author','0')");
						$threadID = $this->db->lastInsertedID();
						$result = $this->db->query("SELECT `threadcount` FROM `board` WHERE `board`='$boardID'");
						while ($row = $this->db->fetchArray($result)) {
							$threadcount = $row['threadcount']+1;
							$this->db->query("UPDATE `board` SET `threadcount`='$threadcount' WHERE `board`='$boardID'");
						}
						
						$this->db->query("INSERT INTO `post`(`author`, `thread`, `date`, `operator`, `lastedit`, `content`, `ip`, `deleted`) VALUES('$author','$threadID','$time','0','0','$content','$ip','0')");
						$postID = $this->db->lastInsertedID();
						$result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
						while ($row = $this->db->fetchArray($result)) {
							$postcount = $row['postcount']+1;
							$this->db->query("UPDATE `thread` SET `postcount`='$postcount', `lastpost`='$postID' WHERE `thread`='$threadID'");
						}
						$result = $this->db->query("SELECT `postcount` FROM `board` WHERE `board`='$boardID'");
						while ($row = $this->db->fetchArray($result)) {
							$postcount = $row['postcount']+1;
							$this->db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$boardID'");
						}
						
						$temporary = $this->db->escapeString($_POST['temporary']);
						$result = $this->db->query("SELECT `file` FROM `attachment` WHERE `temporary`='$temporary'");
						while ($row = $this->db->fetchArray($result)) {
							$newTemporary = $basic->tempFileKey();
							$file = $row['file'];
							$this->db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
							$this->db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
						}
						
						$page = $this->getPageNumber($threadID);
						$link = "index.php?id=".$location."&action=posts&thread=".$threadID."&page=".$page."#".$postID;
						echo "<div class=\"success\">Das Thema wurde erfolgreich erstellt! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
					}
				}
			}
			else {
				$authTime = time();
				$authToken = $auth->getToken($authTime);
				$temporaryKey = $basic->tempFileKey();
				require_once("template/board.newthread.tpl.php");
			}
		}
	}
}

?>