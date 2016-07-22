<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");
include_once(dirname(__FILE__)."/../board.php");
include_once(dirname(__FILE__)."/thread.php");
include_once(dirname(__FILE__)."/../../user/user.php");
include_once(dirname(__FILE__)."/../../user/role.php");
include_once(dirname(__FILE__)."/../../user/auth.php");
include_once(dirname(__FILE__)."/../../includes/dbsocket.php");

class Post {
	
	/*
	 * Displays the content of a thread.
	 */
	public function display() {
		$location = $_GET['id'];
		$auth = new Authentication();
		$role = new Role();
		$user = new User();
		$db = new DB();
		if ($auth->moduleReadAllowed("board", $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())) {
			$thread = new Thread();
			$board = new Board();
			$threadID = $db->escape($_GET['thread']);
			$boardID = $thread->getBoard($threadID);
			if (($location==$board->getLocation($boardID))&&($board->readAllowed($boardID, $role->getRole())&&$auth->locationReadAllowed($board->getLocation($boardID), $role->getRole()))) {
				if (isset($_GET['do'])||isset($_POST['do'])) {
					$this->doThings();
				}
				$posts = array();
				$page = 0;
				if (!isset($_GET['page'])) {
					$page = 1;
				}
				else {
					$page = $_GET['page'];
				}
				if (!$db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4'")) {
					$result = $db->query("SELECT `post` FROM `post` WHERE `thread`='$threadID' AND `deleted`='0'");
					$pages = $db->getCount($result)/10;
					$start = $page*10-10;
					$end = 10;
					$isAuthor = (($thread->getType($threadID)!=3)&&($board->readAllowed($boardID, $role->getRole())&&$board->writeAllowed($boardID, $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$auth->locationWriteAllowed($location, $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())));
					$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
					$isAdmin = $board->isAdmin($boardID, $user->getID());
					$isGlobalAdmin = ($auth->moduleAdminAllowed("board", $role->getRole())&&$auth->locationAdminAllowed($location, $role->getRole()));
					$result = $db->query("SELECT `viewcount` FROM `thread` WHERE `thread`='$threadID'");
					while ($row = $db->fetchArray($result)) {
						$viewcount = $row['viewcount']+1;
						$db->query("UPDATE `thread` SET `viewcount`='$viewcount' WHERE `thread`='$threadID'");
					}
					$result = $db->query("SELECT `post`, `date`, `operator`, `lastedit`, `content`, `ip`, `author` FROM `post` WHERE `deleted`='0' AND `thread`='$threadID' ORDER BY `date` LIMIT $start,$end");
					while ($row = $db->fetchArray($result)) {
						$post = $row['post'];
						$date = date("\a\m d\.m\.Y\ \u\m H\:i\:s", $row['date']);
						$operator = $row['operator'];
						$operatorNickname = htmlentities($user->getNickbyID($operator), null, "ISO-8859-1");
						$lastedit = date("\a\m d\.m\.Y\ \u\m H\:i\:s", $row['lastedit']);
						$content = $row['content'];
						$ip = htmlentities($row['ip'], null, "ISO-8859-1");
						$author = $row['author'];
						$authorNickname = htmlentities($user->getNickbyID($author), null, "ISO-8859-1");
						$editable = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())||((($user->getID()==$author)&&($board->writeAllowed($boardID, $role->getRole())))));
						$files = array();
						$result2 = $db->query("SELECT `file`, `realname` FROM `post_attachment` NATURAL JOIN `attachment` WHERE `post`='$post'");
						while ($row2 = $db->fetchArray($result2)) {
							$filename = htmlentities($row2['realname'], null, "ISO-8859-1");
							$file = $row2['file'];
							array_push($files, array('filename'=>$filename, 'file'=>$file));
						}
						array_push($posts, array('post'=>$post, 'date'=>$date, 'operator'=>$operator, 'operatorNickname'=>$operatorNickname, 'lastedit'=>$lastedit, 'content'=>$content, 'ip'=>$ip, 'author'=>$author, 'authorNickname'=>$authorNickname, 'editable'=>$editable, 'files'=>$files));
					}
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					require_once("template/board.posts.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Do small functions which can be applied to a post.
	 */
	private function doThings() {
		$auth = new Authentication();
		$board = new Board();
		$thread = new Thread();
		$user = new User();
		$role = new Role();
		$db = new DB();
		if (isset($_GET['do'])) {
			if ($_GET['do']=="del") {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$postID = $db->escape($_GET['post']);
					$threadID = $this->getThread($postID);
					if ($threadID == $_GET['thread']&&(!$db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4'"))) {
						$boardID = $thread->getBoard($threadID);
						if ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())) {
							$db->query("UPDATE `post` SET `deleted`='1' WHERE `post`='$postID'");
							if ($db->isExisting("SELECT `post` FROM `post` WHERE `deleted`='1' AND `post`='$postID'")) {
								$result = $db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
								while ($row = $db->fetchArray($result)) {
									$postcount = $row['postcount']-1;
									$db->query("UPDATE `thread` SET `postcount`='$postcount' WHERE `thread`='$threadID'");
								}
								$result = $db->query("SELECT `postcount` FROM `board` WHERE `board`='$boardID'");
								while ($row = $db->fetchArray($result)) {
									$postcount = $row['postcount']-1;
									$db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$boardID'");
								}
								echo "<div class=\"success\">Der Post wurde erfolgreich gel&ouml;scht!</div>";
							}
							else {
								echo "<div class=\"caution\">Achtung, der Post wurde nicht gel&ouml;scht!</div>";
							}
						}
					}
				}
			}
		}
	}
	
	/*
	 * Get the thread ID of a post.
	 */
	public function getThread($post) {
		$db = new DB();
		$post = $db->escape($post);
		$thread = -1;
		$result = $db->query("SELECT `thread` FROM `post` WHERE `post`='$post'");
		while ($row = $db->fetchArray($result)) {
			$thread = $row['thread'];
		}
		return $thread;
	}
	
	/*
	 * Get the author of a post.
	 */
	public function getAuthor($post) {
		$db = new DB();
		$post = $db->escape($post);
		$author = -1;
		$result = $db->query("SELECT `author` FROM `post` WHERE `post`='$post'");
		while ($row = $db->fetchArray($result)) {
			$author = $row['author'];
		}
		return $author;
	}
	
	/*
	 * Dialog to insert a new post.
	 */
	public function answer() {
		$db = new DB();
		$board = new Board();
		$thread = new Thread();
		$auth = new Authentication();
		$role = new Role();
		$user = new User();
		$threadID = $db->escape($_GET['thread']);
		$boardID = $thread->getBoard($threadID);
		$basic = new Basic();
		$location = $db->escape($_GET['id']);
		$isAdmin = ($board->isAdmin($boardID, $user->getID())||$auth->moduleAdminAllowed("board", $role->getRole())||$auth->locationAdminAllowed($location, $role->getRole()));
		if (($location==$board->getLocation($boardID))&&(!$db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND (`type`='4' OR `type`='3')"))) {
			if ($board->readAllowed($boardID, $role->getRole())&&$board->writeAllowed($boardID, $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$auth->locationWriteAllowed($location, $role->getRole())&&$auth->moduleReadAllowed("board", $role->getRole())&&$auth->moduleWriteAllowed("board", $role->getRole())) {
				if (isset($_POST['do'])) {
					if ($_POST['do']=="answer") {
						if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$content = $db->escape($basic->cleanStrict($_POST['content']));
							$author = $db->escape($user->getID());
							$time = $db->escape(time());
							$ip = $db->escape($_SERVER['REMOTE_ADDR']);
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
							
							$page = $thread->getPageNumber($threadID);
							$link = "index.php?id=".$location."&action=posts&thread=".$threadID."&page=".$page."#".$postID;
							echo "<div class=\"success\">Deine Antwort wurde erfolgreich gespeichert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
						}
					}
				}
				else {
					$quote = "";
					if (isset($_GET['quote'])) {
						$postID = $db->escape($_GET['quote']);
						$page = $db->escape($_GET['page']);
						if ($threadID==$this->getThread($postID)) {
							$result = $db->query("SELECT `content`, `author` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
							while ($row = $db->fetchArray($result)) {
								$authorNickname = $user->getNickbyID($row['author']);
								$content = $row['content'];
								$quote = "<blockquote>".$authorNickname." <a href=\"index.php?id=".$location."&amp;action=posts&amp;thread=".$threadID."&amp;page=".$page."#".$postID."\">schrieb</a>:<br /><br />".$content."</blockquote><br />";
							}
						}
					}
					$title = $thread->getTitle($threadID);
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					$temporaryKey = $basic->tempFileKey();
					require_once("template/board.answer.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Dialog to edit a post.
	 */
	public function edit() {
		$db = new DB();
		$board = new Board();
		$thread = new Thread();
		$auth = new Authentication();
		$role = new Role();
		$postID = $db->escape($_GET['post']);
		$author = $this->getAuthor($postID);
		$location = $db->escape($_GET['id']);
		$user = new User();
		$threadID = $this->getThread($postID);
		$boardID = $thread->getBoard($threadID);
		$page = $_GET['page'];
		$basic = new Basic();
		if (($location==$board->getLocation($boardID))&&(!$db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4'"))) {
			if ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())||((($user->getID()==$author)&&($board->writeAllowed($boardID, $role->getRole()))))) {
				if (isset($_POST['do'])) {
					if ($_POST['do']=="edit") {
						if ($auth->checkToken($_POST['authTime'],$_POST['authToken'])) {
							$content = $db->escape($basic->cleanStrict($_POST['content']));
							$operator = $user->getID();
							$time = time();
							$link = "index.php?id=".$location."&action=posts&thread=".$threadID."&page=".$page."#".$postID;
							$db->query("UPDATE `post` SET `content`='$content', `operator`='$operator', `lastedit`='$time' WHERE `post`='$postID'");
							if ($db->isExisting("SELECT `post` FROM `post` WHERE `post`='$postID' AND `content`='$content' AND `operator`='$operator' AND `lastedit`='$time'")) {
								
								$temporary = $db->escape($_POST['temporary']);
								$result = $db->query("SELECT `file` FROM `attachment` WHERE `temporary`='$temporary'");
								while ($row = $db->fetchArray($result)) {
									$newTemporary = $basic->tempFileKey();
									$file = $row['file'];
									$db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
									$db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
								}
								
								echo "<div class=\"success\">Der Post wurde erfolgreich ge&auml;ndert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
							}
							else {
								echo "<div class=\"caution\">Achtung, der Post wurde nicht ge&auml;ndert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"javascript:history.back()\">hier</a>.</div><script>top.location.href='javascript:history.back()'</script>";
							}
						}
					}
				}
				else {
					$isAdmin = ($board->isAdmin($boardID, $user->getID())||$auth->moduleAdminAllowed("board", $role->getRole())||$auth->locationAdminAllowed($location, $role->getRole()));
					$content = "";
					$result = $db->query("SELECT `content` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
					while ($row = $db->fetchArray($result)) {
						$content = $row['content'];
						$authTime = time();
						$authToken = $auth->getToken($authTime);
						$temporaryKey = $basic->tempFileKey();
						require_once("template/board.edit.tpl.php");
					}
				}
			}
		}
	}
}

?>