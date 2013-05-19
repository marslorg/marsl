<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");

class EditPicture {
	
	/*
	 * Runs the edit picture dialog for changing the subtitles of a gallery picture.
	 */
	public function admin() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$db = new DB();
		$db->connect();
		$user = new User();
		$auth = new Authentication();
		$role = new Role();
		$basic = new Basic();
		$title = $basic->getTitle();
		$new = true;
		if ($user->isAdmin()&&$auth->moduleAdminAllowed("gallery", $role->getRole())&&$auth->moduleExtendedAllowed("gallery", $role->getRole())) {
			$picture = mysql_real_escape_string($_GET['id']);
			if (isset($_POST['action'])) {
				$new = false;
				if ($_POST['action']=="send"&&$auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$result = $db->query("SELECT `location` FROM `picture` JOIN `album` USING(`album`) WHERE `picture`='$picture'");
					while ($row = mysql_fetch_array($result)) {
						$subtitle = mysql_real_escape_string($basic->cleanHTML($_POST['subtitle']));
						$db->query("UPDATE `picture` SET `subtitle`='$subtitle' WHERE `picture`='$picture'");
					}
				}
			}
			$result = $db->query("SELECT `subtitle`, `location`, `folder`, `filename`, `picture` FROM `picture` JOIN `album` USING(`album`) WHERE `picture`='$picture'");
			while ($row = mysql_fetch_array($result)) {
				if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
					$subtitle = $row['subtitle'];
					$path = "../albums/".htmlentities($row['folder'], ENT_HTML5, "ISO-8859-1").htmlentities($row['filename'], ENT_HTML5, "ISO-8859-1");
					$picture = htmlentities($row['picture'], ENT_HTML5, "ISO-8859-1");
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					require_once("template/gallery.editpicture.tpl.php");
				}
			}
						
		}
		$db->close();
	}
}

$editpicture = new EditPicture();
$editpicture->admin();
?>