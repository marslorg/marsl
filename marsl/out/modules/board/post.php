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

	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}
	
	/*
	 * Displays the content of a thread.
	 */
	public function display() {
		$location = $_GET['id'];
		$user = new User($this->db, $this->role);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())) {
			$thread = new Thread($this->db, $this->auth, $this->role);
			$board = new Board($this->db, $this->auth, $this->role);
			$threadID = $this->db->escapeString($_GET['thread']);
			$boardID = $thread->getBoard($threadID);
			if (($location==$board->getLocation($boardID))&&($board->readAllowed($boardID, $this->role->getRole())&&$this->auth->locationReadAllowed($board->getLocation($boardID), $this->role->getRole()))) {
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
				if (!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1")) {
					$result = $this->db->query("SELECT COUNT(`thread`) AS rowcount FROM `post` WHERE `thread`='$threadID' AND `deleted`='0'");
					$pages = $this->db->getRowCount($result)/10;
					$start = $page*10-10;
					$end = 10;
					$isAuthor = (($thread->getType($threadID)!=3)&&($board->readAllowed($boardID, $this->role->getRole())&&$board->writeAllowed($boardID, $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())&&$this->auth->locationWriteAllowed($location, $this->role->getRole())&&$this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->moduleWriteAllowed("board", $this->role->getRole())));
					$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
					$isAdmin = $board->isAdmin($boardID, $user->getID());
					$isGlobalAdmin = ($this->auth->moduleAdminAllowed("board", $this->role->getRole())&&$this->auth->locationAdminAllowed($location, $this->role->getRole()));
					$result = $this->db->query("SELECT `viewcount` FROM `thread` WHERE `thread`='$threadID'");
					while ($row = $this->db->fetchArray($result)) {
						$viewcount = $row['viewcount']+1;
						$this->db->query("UPDATE `thread` SET `viewcount`='$viewcount' WHERE `thread`='$threadID'");
					}
					$result = $this->db->query("SELECT `post`, `date`, `operator`, `lastedit`, `content`, `ip`, `author` FROM `post` WHERE `deleted`='0' AND `thread`='$threadID' ORDER BY `date` LIMIT $start,$end");
					while ($row = $this->db->fetchArray($result)) {
						$post = $row['post'];
						$dateTime->setTimestamp($row['date']);
						$date = $dateTime->format("\a\m d\.m\.Y\ \u\m H\:i\:s");
						$operator = $row['operator'];
						$operatorNickname = htmlentities($user->getNickbyID($operator), null, "ISO-8859-1");
						$dateTime->setTimestamp($row['lastedit']);
						$lastedit = $dateTime->format("\a\m d\.m\.Y\ \u\m H\:i\:s");
						$content = $row['content'];
						$ip = htmlentities($row['ip'], null, "ISO-8859-1");
						$author = $row['author'];
						$authorNickname = htmlentities($user->getNickbyID($author), null, "ISO-8859-1");
						$editable = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())||((($user->getID()==$author)&&($board->writeAllowed($boardID, $this->role->getRole())))));
						$files = array();
						$result2 = $this->db->query("SELECT `file`, `realname` FROM `post_attachment` NATURAL JOIN `attachment` WHERE `post`='$post'");
						while ($row2 = $this->db->fetchArray($result2)) {
							$filename = htmlentities($row2['realname'], null, "ISO-8859-1");
							$file = $row2['file'];
							array_push($files, array('filename'=>$filename, 'file'=>$file));
						}
						array_push($posts, array('post'=>$post, 'date'=>$date, 'operator'=>$operator, 'operatorNickname'=>$operatorNickname, 'lastedit'=>$lastedit, 'content'=>$content, 'ip'=>$ip, 'author'=>$author, 'authorNickname'=>$authorNickname, 'editable'=>$editable, 'files'=>$files));
					}
					$authTime = time();
					$authToken = $this->auth->getToken($authTime);
					require_once("template/board.posts.tpl.php");
				}
			}
		}
	}
	
	/*
	 * Do small functions which can be applied to a post.
	 */
	private function doThings() {
		$board = new Board($this->db, $this->auth, $this->role);
		$thread = new Thread($this->db, $this->auth, $this->role);
		$user = new User($this->db, $this->role);
		if (isset($_GET['do'])) {
			if ($_GET['do']=="del") {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$postID = $this->db->escapeString($_GET['post']);
					$threadID = $this->getThread($postID);
					if ($threadID == $_GET['thread']&&(!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1"))) {
						$boardID = $thread->getBoard($threadID);
						if ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())) {
							$this->db->query("UPDATE `post` SET `deleted`='1' WHERE `post`='$postID'");
							if ($this->db->isExisting("SELECT `post` FROM `post` WHERE `deleted`='1' AND `post`='$postID' LIMIT 1")) {
								$result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
								while ($row = $this->db->fetchArray($result)) {
									$postcount = $row['postcount']-1;
									$this->db->query("UPDATE `thread` SET `postcount`='$postcount' WHERE `thread`='$threadID'");
								}
								$result = $this->db->query("SELECT `postcount` FROM `board` WHERE `board`='$boardID'");
								while ($row = $this->db->fetchArray($result)) {
									$postcount = $row['postcount']-1;
									$this->db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$boardID'");
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
		$post = $this->db->escapeString($post);
		$thread = -1;
		$result = $this->db->query("SELECT `thread` FROM `post` WHERE `post`='$post'");
		while ($row = $this->db->fetchArray($result)) {
			$thread = $row['thread'];
		}
		return $thread;
	}
	
	/*
	 * Get the author of a post.
	 */
	public function getAuthor($post) {
		$post = $this->db->escapeString($post);
		$author = -1;
		$result = $this->db->query("SELECT `author` FROM `post` WHERE `post`='$post'");
		while ($row = $this->db->fetchArray($result)) {
			$author = $row['author'];
		}
		return $author;
	}
	
	/*
	 * Dialog to insert a new post.
	 */
	public function answer() {
		$board = new Board($this->db, $this->auth, $this->role);
		$thread = new Thread($this->db, $this->auth, $this->role);
		$user = new User($this->db, $this->role);
		$threadID = $this->db->escapeString($_GET['thread']);
		$boardID = $thread->getBoard($threadID);
		$basic = new Basic($this->db, $this->auth, $this->role);
		$location = $this->db->escapeString($_GET['id']);
		$isAdmin = ($board->isAdmin($boardID, $user->getID())||$this->auth->moduleAdminAllowed("board", $this->role->getRole())||$this->auth->locationAdminAllowed($location, $this->role->getRole()));
		if (($location==$board->getLocation($boardID))&&(!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type` IN ('4','3') LIMIT 1"))) {
			if ($board->readAllowed($boardID, $this->role->getRole())&&$board->writeAllowed($boardID, $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())&&$this->auth->locationWriteAllowed($location, $this->role->getRole())&&$this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->moduleWriteAllowed("board", $this->role->getRole())) {
				if (isset($_POST['do'])) {
					if ($_POST['do']=="answer") {
						if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
							$content = $this->db->escapeString($basic->cleanStrict($_POST['content']));
							$author = $this->db->escapeString($user->getID());
							$time = $this->db->escapeString(time());
							$ip = $this->db->escapeString($_SERVER['REMOTE_ADDR']);
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
							
							$page = $thread->getPageNumber($threadID);
							$link = "index.php?id=".$location."&action=posts&thread=".$threadID."&page=".$page."#".$postID;
							echo "<div class=\"success\">Deine Antwort wurde erfolgreich gespeichert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
						}
					}
				}
				else {
					$quote = "";
					if (isset($_GET['quote'])) {
						$postID = $this->db->escapeString($_GET['quote']);
						$page = $this->db->escapeString($_GET['page']);
						if ($threadID==$this->getThread($postID)) {
							$result = $this->db->query("SELECT `content`, `author` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
							while ($row = $this->db->fetchArray($result)) {
								$authorNickname = $user->getNickbyID($row['author']);
								$content = $row['content'];
								$quote = "<blockquote>".$authorNickname." <a href=\"index.php?id=".$location."&amp;action=posts&amp;thread=".$threadID."&amp;page=".$page."#".$postID."\">schrieb</a>:<br /><br />".$content."</blockquote><br />";
							}
						}
					}
					$title = $thread->getTitle($threadID);
					$authTime = time();
					$authToken = $this->auth->getToken($authTime);
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
		$board = new Board($this->db, $this->auth, $this->role);
		$thread = new Thread($this->db, $this->auth, $this->role);
		$postID = $this->db->escapeString($_GET['post']);
		$author = $this->getAuthor($postID);
		$location = $this->db->escapeString($_GET['id']);
		$user = new User($this->db, $this->role);
		$threadID = $this->getThread($postID);
		$boardID = $thread->getBoard($threadID);
		$page = $_GET['page'];
		$basic = new Basic($this->db, $this->auth, $this->role);
		if (($location==$board->getLocation($boardID))&&(!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1"))) {
			if ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())||((($user->getID()==$author)&&($board->writeAllowed($boardID, $this->role->getRole()))))) {
				if (isset($_POST['do'])) {
					if ($_POST['do']=="edit") {
						if ($this->auth->checkToken($_POST['authTime'],$_POST['authToken'])) {
							$content = $this->db->escapeString($basic->cleanStrict($_POST['content']));
							$operator = $user->getID();
							$time = time();
							$link = "index.php?id=".$location."&action=posts&thread=".$threadID."&page=".$page."#".$postID;
							$this->db->query("UPDATE `post` SET `content`='$content', `operator`='$operator', `lastedit`='$time' WHERE `post`='$postID'");
							if ($this->db->isExisting("SELECT `post` FROM `post` WHERE `post`='$postID' AND `content`='$content' AND `operator`='$operator' AND `lastedit`='$time' LIMIT 1")) {
								
								$temporary = $this->db->escapeString($_POST['temporary']);
								$result = $this->db->query("SELECT `file` FROM `attachment` WHERE `temporary`='$temporary'");
								while ($row = $this->db->fetchArray($result)) {
									$newTemporary = $basic->tempFileKey();
									$file = $row['file'];
									$this->db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
									$this->db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
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
					$isAdmin = ($board->isAdmin($boardID, $user->getID())||$this->auth->moduleAdminAllowed("board", $this->role->getRole())||$this->auth->locationAdminAllowed($location, $this->role->getRole()));
					$content = "";
					$result = $this->db->query("SELECT `content` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
					while ($row = $this->db->fetchArray($result)) {
						$content = $row['content'];
						$authTime = time();
						$authToken = $this->auth->getToken($authTime);
						$temporaryKey = $basic->tempFileKey();
						require_once("template/board.edit.tpl.php");
					}
				}
			}
		}
	}
}

?>