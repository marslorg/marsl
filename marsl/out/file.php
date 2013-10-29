<?php
include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/includes/dbsocket.php");
include_once(dirname(__FILE__)."/includes/encryption.php");
include_once(dirname(__FILE__)."/user/role.php");
include_once(dirname(__FILE__)."/user/auth.php");
include_once(dirname(__FILE__)."/modules/board.php");

class File {
	
	public function display() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		
		$db = new DB();
		
		$db->connect();
		
		$fileID = mysql_real_escape_string($_GET['file']);
		$scope = $_GET['scope'];
		$role = new Role();
		$auth = new Authentication();
		
		$crypt = new Encryption();
		
		if ($scope=="board") {
			$board = new Board();
			$result = $db->query("SELECT `board`, `servername`, `realname`, `key` FROM `attachment` JOIN `post_attachment` USING(`file`) JOIN `post` USING(`post`) JOIN `thread` USING(`thread`) WHERE `file`='$fileID'");
			while ($row = mysql_fetch_array($result)) {
				$boardID = $row['board'];
				$location = $board->getLocation($boardID);
				if ($board->readAllowed($boardID, $role->getRole())&&$auth->locationReadAllowed($location, $role->getRole())&&$auth->moduleAdminAllowed("board", $role->getRole())) {
					$servername = $row['servername'];
					$realname = $row['realname'];
					$key = $row['key'];
					$encContent = file_get_contents("files/".$servername);
					$fileContent = $crypt->decrypt($encContent, $key);
					header("Content-Type: application/octet-stream");
					header("Content-Disposition: attachment; filename=\"$realname\"");
					echo $fileContent; 
				}
			}
		}
		
		$db->close();
	}
	
}

$file = new File();
$file->display();
?>