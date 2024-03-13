<?php
include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../includes/basic.php");
include_once(dirname(__FILE__)."/../board.php");
include_once(dirname(__FILE__)."/../navigation.php");
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
		$basic = new Basic($this->db, $this->auth, $this->role);
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$pageID = $navi->getPageID();
		$location = $pageID;
		$user = new User($this->db, $this->role);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())) {
			$thread = new Thread($this->db, $this->auth, $this->role);
			$board = new Board($this->db, $this->auth, $this->role);
			$threadID = $this->db->escapeString($thread->getThreadID());
			$threadTitle = $thread->getNameById($threadID);
			$boardID = $thread->getBoard($threadID);
			if (($location==$board->getLocation($boardID))&&($board->readAllowed($boardID, $this->role->getRole())&&$this->auth->locationReadAllowed($board->getLocation($boardID), $this->role->getRole()))) {
				if (isset($_GET['do'])||isset($_POST['do'])) {
					$this->doThings();
				}
				$posts = array();
				$page = $thread->getThreadPage();
				if (!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1")) {
					$result = $this->db->query("SELECT COUNT(`thread`) AS rowcount FROM `post` WHERE `thread`='$threadID' AND `deleted`='0'");
					$pages = $this->db->getRowCount($result)/10;
					$start = $page*10-10;
					$end = 10;
					$isAuthor = (($thread->getType($threadID)!=3)&&($board->readAllowed($boardID, $this->role->getRole())&&$board->writeAllowed($boardID, $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())&&$this->auth->locationWriteAllowed($location, $this->role->getRole())&&$this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->moduleWriteAllowed("board", $this->role->getRole())));
					$isOperator = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID()));
					$isAdmin = $board->isAdmin($boardID, $user->getID());
					$isGlobalAdmin = ($this->auth->moduleAdminAllowed("board", $this->role->getRole())&&$this->auth->locationAdminAllowed($location, $this->role->getRole()));
					$uri = $navi->getRelativeURI($location, null, false);
					$threadURI = $uri.$thread->getThreadURIPart($threadID, $threadTitle, 0);
					$answerURI = $uri.$this->getAnswerURIPart($threadID, $threadTitle, $page);
					$result = $this->db->query("SELECT `viewcount` FROM `thread` WHERE `thread`='$threadID'");
					while ($row = $this->db->fetchArray($result)) {
						$viewcount = $row['viewcount']+1;
						$this->db->query("UPDATE `thread` SET `viewcount`='$viewcount' WHERE `thread`='$threadID'");
					}

					$authTime = time();
					$authToken = $this->auth->getToken($authTime);

					$result = $this->db->query("SELECT `post`, `date`, `operator`, `lastedit`, `content`, `ip`, `author` FROM `post` WHERE `deleted`='0' AND `thread`='$threadID' ORDER BY `date` LIMIT $start,$end");
					while ($row = $this->db->fetchArray($result)) {
						$post = $row['post'];
						$dateTime->setTimestamp($row['date']);
						$date = $dateTime->format("\a\m d\.m\.Y\ \u\m H\:i\:s");
						$operator = $row['operator'];
						$operatorNickname = $basic->convertToHTMLEntities($user->getNickbyID($operator, $this->auth));
						$dateTime->setTimestamp($row['lastedit']);
						$lastedit = $dateTime->format("\a\m d\.m\.Y\ \u\m H\:i\:s");
						$content = $row['content'];
						$ip = $basic->convertToHTMLEntities($row['ip']);
						$author = $row['author'];
						$authorNickname = $basic->convertToHTMLEntities($user->getNickbyID($author, $this->auth));
						$editable = ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())||((($user->getID()==$author)&&($board->writeAllowed($boardID, $this->role->getRole())))));
						$files = array();
						$result2 = $this->db->query("SELECT `file`, `realname` FROM `post_attachment` NATURAL JOIN `attachment` WHERE `post`='$post'");
						while ($row2 = $this->db->fetchArray($result2)) {
							$filename = $basic->convertToHTMLEntities($row2['realname']);
							$file = $row2['file'];
							array_push($files, array('filename'=>$filename, 'file'=>$file));
						}
						$quoteURI = $answerURI.$this->getQuoteURIPart($post);
						$editURI = $uri.$this->getEditURIPart($threadID, $threadTitle, $post, $page);
						$deleteURI = $threadURI.$this->getDeleteURIPart($post, $authTime, $authToken);
						array_push($posts, array('post'=>$post, 'date'=>$date, 'operator'=>$operator, 'operatorNickname'=>$operatorNickname, 'quoteURI' => $quoteURI, 'editURI' => $editURI, 'deleteURI' => $deleteURI, 'lastedit'=>$lastedit, 'content'=>$content, 'ip'=>$ip, 'author'=>$author, 'authorNickname'=>$authorNickname, 'editable'=>$editable, 'files'=>$files));
					}
					$oldURIsEnabled = $config->getEnableOldURIs();
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
					if ($threadID == $thread->getThreadID()&&(!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1"))) {
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
		$threadID = $this->db->escapeString($thread->getThreadID());
		$threadTitle = $thread->getNameById($threadID);
		$boardID = $thread->getBoard($threadID);
		$basic = new Basic($this->db, $this->auth, $this->role);
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$pageID = $navi->getPageID();
		$location = $this->db->escapeString($pageID);
		$isAdmin = ($board->isAdmin($boardID, $user->getID())||$this->auth->moduleAdminAllowed("board", $this->role->getRole())||$this->auth->locationAdminAllowed($location, $this->role->getRole()));
		if (($location==$board->getLocation($boardID))&&(!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type` IN ('4','3') LIMIT 1"))) {
			if ($board->readAllowed($boardID, $this->role->getRole())&&$board->writeAllowed($boardID, $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())&&$this->auth->locationWriteAllowed($location, $this->role->getRole())&&$this->auth->moduleReadAllowed("board", $this->role->getRole())&&$this->auth->moduleWriteAllowed("board", $this->role->getRole())) {
				$uri = $navi->getRelativeURI($location, null, false);
				$page = $thread->getThreadPage();
				$postsURI = $uri.$thread->getThreadURIPart($threadID, $threadTitle, $page);
				$answerURI = $uri.$this->getAnswerURIPart($threadID, $threadTitle, $page);
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
							
							$link = $postsURI."#".$postID;
							echo "<div class=\"success\">Deine Antwort wurde erfolgreich gespeichert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
						}
					}
				}
				else {
					$quote = "";
					if (isset($_GET['quote'])) {
						$postID = $this->db->escapeString($_GET['quote']);
						$page = $thread->getThreadPage();
						if ($threadID==$this->getThread($postID)) {
							$result = $this->db->query("SELECT `content`, `author` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
							while ($row = $this->db->fetchArray($result)) {
								$authorNickname = $user->getNickbyID($row['author'], $this->auth);
								$content = $row['content'];
								$quote = "<blockquote>".$authorNickname." <a href=\"".$postsURI."\">schrieb</a>:<br /><br />".$content."</blockquote><br />";
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
		$postID = $this->db->escapeString($this->getPostID());
		$author = $this->getAuthor($postID);
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$pageID = $navi->getPageID();
		$location = $this->db->escapeString($pageID);
		$user = new User($this->db, $this->role);
		$threadID = $this->getThread($postID);
		$threadTitle = $thread->getNameById($threadID);
		$boardID = $thread->getBoard($threadID);
		$page = $thread->getThreadPage();
		$basic = new Basic($this->db, $this->auth, $this->role);
		if (($location==$board->getLocation($boardID))&&(!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1"))) {
			if ($board->isAdmin($boardID, $user->getID())||$board->isOperator($boardID, $user->getID())||((($user->getID()==$author)&&($board->writeAllowed($boardID, $this->role->getRole()))))) {
				$uri = $navi->getRelativeURI($location, null, false);
				$pagePostsURI = $uri.$thread->getThreadURIPart($threadID, $threadTitle, $page);
				$pagePostEditURI = $uri.$this->getEditURIPart($threadID, $threadTitle, $postID, $page);
				if (isset($_POST['do'])) {
					if ($_POST['do']=="edit") {
						if ($this->auth->checkToken($_POST['authTime'],$_POST['authToken'])) {
							$content = $this->db->escapeString($basic->cleanStrict($_POST['content']));
							$operator = $user->getID();
							$time = time();
							$link = $pagePostsURI."#".$postID;
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

	private function getQuoteURIPart($postID) {
		$config = new Configuration();
		$result = "";
		if ($config->getEnableOldURIs()) {
			$result = "&quote=".$postID;
		}
		else {
			$result = "?quote=".$postID;
		}
		return $result;
	}

	private function getDeleteURIPart($postID, $authTime, $authToken) {
		$config = new Configuration();
		$result = "";
		if ($config->getEnableOldURIs()) {
			$result = "&";
		}
		else {
			$result = "?";
		}
		$result = $result."do=del&post=".$postID."&time=".$authTime."&token=".$authToken;
		return $result;
	}

	private function getEditURIPart($threadID, $threadTitle, $postID, $page) {
		$config = new Configuration();
		$result = "";

		if ($config->getEnableOldURIs()) {
			$result = "&action=edit&thread=".$threadID."&post=".$postID."&page=".$page;
		}
		else {
			$navi = new Navigation($this->db, $this->auth, $this->role);
			$result = "/".$navi->generateRestfulURI($threadID, $threadTitle)."/edit/".$page."/".$postID;
		}
		
		return $result;
	}

	private function getAnswerURIPart($threadID, $threadTitle, $page) {
		$config = new Configuration();
		$result = "";
		if ($config->getEnableOldURIs()) {
			$result = "&action=answer&thread=".$threadID."&page=".$page;
		}
		else {
			$navi = new Navigation($this->db, $this->auth, $this->role);
			$result = "/".$navi->generateRestfulURI($threadID, $threadTitle)."/answer/".$page;
		}
		return $result;
	}

	private function getRestfulAnswerURIPart() {
		$result = "";
		$thread = new Thread($this->db, $this->auth, $this->role);
		$threadID = $thread->getThreadID();
		$result = "/".$thread->generateRestfulURIByID($threadID)."/answer";
		if (isset($_GET['page'])) {
			$result = $result."/".$_GET['page'];
		}
		if (isset($_GET['quote'])) {
			$result = $result."?quote=".$_GET['quote'];
		}
		return $result;
	}

	private function getOldAnswerURIPart() {
		$result = "";
		$thread = new Thread($this->db, $this->auth, $this->role);
		$threadID = $thread->getThreadID();
		$result = "&thread=".$threadID."&page=".$thread->getThreadPage();
		if (isset($_GET['quote'])) {
			$result = $result."&quote=".$_GET['quote'];
		}
		return $result;
	}

	private function getRestfulEditURIPart() {
		$result = "";
		$thread = new Thread($this->db, $this->auth, $this->role);
		$threadID = $thread->getThreadID();
		$result = "/".$thread->generateRestfulURIByID($threadID)."/edit";
		if (isset($_GET['page'])) {
			$result = $result."/".$_GET['page'];
		}
		if (isset($_GET['post'])) {
			$result = $result."/".$_GET['post'];
		}
		return $result;
	}

	private function getOldEditURIPart() {
		$result = "";
		$thread = new Thread($this->db, $this->auth, $this->role);
		$threadID = $thread->getThreadID();
		$result = "&thread=".$threadID."&page=".$thread->getThreadPage()."&post=".$this->getPostID();
		return $result;
	}

	private function getRestfulPostsURIPart() {
		$result = "";
		$thread = new Thread($this->db, $this->auth, $this->role);
		$threadID = $thread->getThreadID();
		$result = "/".$thread->generateRestfulURIByID($threadID)."/posts";
		$page = $thread->getThreadPage();
		if ($page > 0) {
			$result = $result."/".$page;
		}
		return $result;
	}

	private function getOldPostsURIPart() {
		$result = "";
		$thread = new Thread($this->db, $this->auth, $this->role);
		$threadID = $thread->getThreadID();
		$result = "&thread=".$threadID;
		$page = $thread->getThreadPage();
		if ($page > 0) {
			$result = $result."&page=".$page;
		}
		return $result;
	}

	private function getPageURIFormatted($page) {
		$board = new Board($this->db, $this->auth, $this->role);
		return $board->getPageURIFormatted($page);
	}

	private function getPostID() {
		$postID = 0;
		if (isset($_GET['post'])) {
			$postID = $_GET['post'];
		}
		else if (isset($_GET['request_uri']) && !empty($_GET['request_uri'])) {
			$requestURI = $_GET['request_uri'];
			$explodedRequestURI = explode('/', $requestURI);
			if (sizeof($explodedRequestURI) > 4) {
				$postID = $explodedRequestURI[4];
			}
		}
		return $postID;
	}

	public function getRestfulURIPartFromOldURL($action) {
		$result = "";
		
		if ($action=="posts") {
			$result = $this->getRestfulPostsURIPart();
		}
		else if ($action=="edit") {
			$result = $this->getRestfulEditURIPart();
		}
		else if ($action=="answer") {
			$result = $this->getRestfulAnswerURIPart();
		}

		return $result;
	}

	public function getOldURIPartFromRestfulURL($action) {
		$result = "";

		if ($action=="posts") {
			$result = $this->getOldPostsURIPart();
		}
		else if ($action=="edit") {
			$result = $this->getOldEditURIPart();
		}
		else if ($action=="answer") {
			$result = $this->getOldAnswerURIPart();
		}

		return $result;
	}
}

?>