<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../board.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");

class Thread {
	
	/*
	 * Displays the thread overview in a board.
	 */
	public function display() {
		$location = $_GET['id'];
		$db = new DB();
		$boardID = $db->escape($_GET['board']);
		$user = new User();
		$role = new Role();
		$auth = new Authentication();
		$board = new Board();
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
			$result = $db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date`, `type` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE (`type`='0' OR `type`='3') AND `board`='$boardID'");
			$pages = $db->getCount($result)/15;
			$start = $page*15-15;
			$end = 15;
			$result = $db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date`, `type` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE (`type`='0' OR `type`='3') AND `board`='$boardID' ORDER BY `date` DESC LIMIT $start,$end");
			while ($row = $db->fetchArray($result)) {
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
		$db = new DB();
		$type = "4";
		$thread = $db->escape($thread);
		$result = $db->query("SELECT `type` FROM `thread` WHERE `thread`='$thread'");
		while ($row = $db->fetchArray($result)) {
			$type = $row['type'];
		}
		return $type;
	}
	
	/*
	 * Get the number of pages a thread is containing.
	 */
	public function getPageNumber($thread) {
		$db = new DB();
		$thread = $db->escape($thread);
		$result = $db->query("SELECT `post` FROM `post` WHERE `thread`='$thread' AND `deleted`='0'");
		$pages = ceil($db->getCount($result)/10);
		return $pages;
	}
	
	/*
	 * Get the title of a thread.
	 */
	public function getTitle($thread) {
		$db = new DB();
		$thread = $db->escape($thread);
		$title = "";
		$result = $db->query("SELECT `title` FROM `thread` WHERE `thread`='$thread' AND NOT (`type`='4')");
		while ($row = $db->fetchArray($result)) {
			$title = htmlentities($row['title'], null, "ISO-8859-1");
		}
		return $title;
	}
	
	/*
	 * Get globally fixed threads.
	 */
	private function getGlobals() {
		$user = new User();
		$role = new Role();
		$auth = new Authentication();
		$board = new Board();
		$db = new DB();
		$globals = array();
		$result = $db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type`='2' ORDER BY `date` DESC");
		while ($row = $db->fetchArray($result)) {
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
		$user = new User();
		$db = new DB();
		$board = $db->escape($_GET['board']);
		$fixeds = array();
		$result = $db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type`='1' AND `board`='$board' ORDER BY `date` DESC");
		while ($row = $db->fetchArray($result)) {
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
		$db = new DB();
		$thread = $db->escape($thread);
		$board = "";
		$result = $db->query("SELECT `board` FROM `thread` WHERE `thread`='$thread'");
		while ($row = $db->fetchArray($result)) {
			$board = $row['board'];
		}
		return $board;
	}
	
	/*
	 * Move thread to another board.
	 */
	public function moveThread() {
		$db = new DB();
		$auth = new Authentication();
		$location = $_GET['id'];
		$threadID = $db->escape($_GET['thread']);
		$board = new Board();
		$boardID = $this->getBoard($threadID);
		$role = new Role();
		$user = new User();
		$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
		$isAdmin = $board->isAdmin($boardID, $user->getID());
		$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
			if (isset($_POST['do'])) {
				if ($_POST['do']=="move") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$destinationID = $db->escape($_POST['destination']);
						if ($board->readAllowed($destinationID, $role->getRole())&&$board->writeAllowed($destinationID, $role->getRole())&&$auth->locationReadAllowed($board->getLocation($destinationID), $role->getRole())&&$auth->locationWriteAllowed($board->getLocation($destinationID), $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())) {
							$db->query("UPDATE `thread` SET `board`='$destinationID' WHERE `thread`='$threadID'");
							
							$result = $db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
							while ($row = $db->fetchArray($result)) {
								$postcount = $row['postcount'];
								$result2 = $db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$boardID'");
								while ($row2 = $db->fetchArray($result2)) {
									$threadcount = $row2['threadcount']-1;
									$newPostcount = $row2['postcount']-$postcount;
									$db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$newPostcount' WHERE `board`='$boardID'");
								}
								$result2 = $db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$destinationID'");
								while ($row2 = $db->fetchArray($result2)) {
									$threadcount = $row2['threadcount']+1;
									$newPostcount = $row2['postcount']+$postcount;
									$db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$newPostcount' WHERE `board`='$destinationID'");
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
				$result = $db->query("SELECT `board`, `title` FROM `board` WHERE `type`='1'");
				while ($row = $db->fetchArray($result)) {
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
		$db = new DB();
		$auth = new Authentication();
		$location = $_GET['id'];
		$threadID = $db->escape($_GET['thread']);
		$board = new Board();
		$boardID = $this->getBoard($threadID);
		$role = new Role();
		$user = new User();
		$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
		$isAdmin = $board->isAdmin($boardID, $user->getID());
		$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
			if (isset($_POST['do'])) {
				if ($_POST['do']=="change") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$title = $db->escape($_POST['title']);
						$db->query("UPDATE `thread` SET `title`='$title' WHERE `thread`='$threadID'");
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
		$db = new DB();
		$auth = new Authentication();
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $db->escape($_GET['thread']);
			$board = new Board();
			$boardID = $this->getBoard($threadID);
			$role = new Role();
			$user = new User();
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$db->query("UPDATE `thread` SET `type`='0' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde ge&ouml;ffnet! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Close a thread.
	 */
	public function close() {
		$db = new DB();
		$auth = new Authentication();
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $db->escape($_GET['thread']);
			$board = new Board();
			$boardID = $this->getBoard($threadID);
			$role = new Role();
			$user = new User();
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$db->query("UPDATE `thread` SET `type`='3' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde geschlossen! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Delete a thread.
	 */
	public function delete() {
		$db = new DB();
		$auth = new Authentication();
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $db->escape($_GET['thread']);
			$board = new Board();
			$boardID = $this->getBoard($threadID);
			$role = new Role();
			$user = new User();
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$db->query("UPDATE `thread` SET `type`='4' WHERE `thread`='$threadID'");
				$result = $db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
				while ($row = $db->fetchArray($result)) {
					$postcount = $row['postcount'];
					$result2 = $db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$boardID'");
					while ($row2 = $db->fetchArray($result2)) {
						$threadcount = $row2['threadcount']-1;
						$postcount = $row2['postcount']-$postcount;
						$db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$postcount' WHERE `board`='$boardID'");
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
		$db = new DB();
		$auth = new Authentication();
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $db->escape($_GET['thread']);
			$board = new Board();
			$boardID = $this->getBoard($threadID);
			$role = new Role();
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&$isGlobalAdmin) {
				$db->query("UPDATE `thread` SET `type`='2' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde global fixiert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Locally fix a thread.
	 */
	public function fixLocal() {
		$db = new DB();
		$auth = new Authentication();
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $db->escape($_GET['thread']);
			$board = new Board();
			$boardID = $this->getBoard($threadID);
			$role = new Role();
			$user = new User();
			$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
			$isAdmin = $board->isAdmin($boardID, $user->getID());
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&($isGlobalAdmin||$isOperator||$isAdmin)) {
				$db->query("UPDATE `thread` SET `type`='1' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Das Thema wurde fixiert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Remove all fixations of a thread.
	 */
	public function removeFixation() {
		$db = new DB();
		$auth = new Authentication();
		if ($auth->checkToken($_GET['time'], $_GET['token'])) {
			$location = $_GET['id'];
			$threadID = $db->escape($_GET['thread']);
			$board = new Board();
			$boardID = $this->getBoard($threadID);
			$role = new Role();
			$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
			if (($location==$board->getLocation($boardID))&&$isGlobalAdmin) {
				$db->query("UPDATE `thread` SET `type`='0' WHERE `thread`='$threadID'");
				$link = "index.php?id=".$location."&action=posts&thread=".$threadID;
				echo "<div class=\"success\">Die Ank&uuml;ndigung wurde aufgehoben! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
			}
		}
	}
	
	/*
	 * Create a new thread.
	 */
	public function newThread() {
		$db = new DB();
		$board = new Board();
		$auth = new Authentication();
		$role = new Role();
		$user = new User();
		$boardID = $db->escape($_GET['board']);
		$basic = new Basic();
		$location = $db->escape($_GET['id']);
		$isAdmin = ($board->isAdmin($boardID, $user->getID())||$auth->moduleAdminAllowed("board", $role->getRole())||$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&$board->readAllowed($boardID, $role->getRole())&&$board->writeAllowed($boardID, $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$auth->locationWriteAllowed($location, $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())) {
			if (isset($_POST['do'])) {
				if ($_POST['do']=="newthread") {
					if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
						$title = $db->escape($_POST['title']);
						$content = $db->escape($basic->cleanStrict($_POST['content']));
						$author = $db->escape($user->getID());
						$time = $db->escape(time());
						$ip = $db->escape($_SERVER['REMOTE_ADDR']);
						$db->query("INSERT INTO `thread`(`board`,`postcount`,`type`,`title`,`author`,`viewcount`) VALUES('$boardID','0','0','$title','$author','0')");
						$threadID = $db->getLastID();
						$result = $db->query("SELECT `threadcount` FROM `board` WHERE `board`='$boardID'");
						while ($row = $db->fetchArray($result)) {
							$threadcount = $row['threadcount']+1;
							$db->query("UPDATE `board` SET `threadcount`='$threadcount' WHERE `board`='$boardID'");
						}
						
						$db->query("INSERT INTO `post`(`author`, `thread`, `date`, `operator`, `lastedit`, `content`, `ip`, `deleted`) VALUES('$author','$threadID','$time','0','0','$content','$ip','0')");
						$postID = $db->getLastID();
						$result = $db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
						while ($row = $db->fetchArray($result)) {
							$postcount = $row['postcount']+1;
							$db->query("UPDATE `thread` SET `postcount`='$postcount', `lastpost`='$postID' WHERE `thread`='$threadID'");
						}
						$result = $db->query("SELECT `postcount` FROM `board` WHERE `board`='$boardID'");
						while ($row = $db->fetchArray($result)) {
							$postcount = $row['postcount']+1;
							$db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$boardID'");
						}
						
						$temporary = $db->escape($_POST['temporary']);
						$result = $db->query("SELECT `file` FROM `attachment` WHERE `temporary`='$temporary'");
						while ($row = $db->fetchArray($result)) {
							$newTemporary = $basic->tempFileKey();
							$file = $row['file'];
							$db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
							$db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
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