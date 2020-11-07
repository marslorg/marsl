<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");

class EditPicture {
	
	private $db;

	public function __construct() {
		$this->db = new DB();
		$this->db->connect();
	}

	/*
	 * Runs the edit picture dialog for changing the subtitles of a gallery picture.
	 */
	public function admin() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$user = new User($this->db);
		$auth = new Authentication($this->db);
		$role = new Role($this->db);
		$basic = new Basic($this->db, $auth);
		$title = $basic->getTitle();
		$new = true;
		if ($user->isAdmin()&&$auth->moduleAdminAllowed("gallery", $role->getRole())&&$auth->moduleExtendedAllowed("gallery", $role->getRole())) {
			$picture = $this->db->escapeString($_GET['id']);
			if (isset($_POST['action'])) {
				$new = false;
				if ($_POST['action']=="send"&&$auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$result = $this->db->query("SELECT `location` FROM `picture` JOIN `album` USING(`album`) WHERE `picture`='$picture'");
					while ($row = $this->db->fetchArray($result)) {
						$subtitle = $this->db->escapeString($basic->cleanHTML($_POST['subtitle']));
						$this->db->query("UPDATE `picture` SET `subtitle`='$subtitle' WHERE `picture`='$picture'");
					}
				}
			}
			$result = $this->db->query("SELECT `subtitle`, `location`, `folder`, `filename`, `picture` FROM `picture` JOIN `album` USING(`album`) WHERE `picture`='$picture'");
			while ($row = $this->db->fetchArray($result)) {
				if ($auth->locationAdminAllowed($row['location'], $role->getRole())) {
					$subtitle = $row['subtitle'];
					$path = "../albums/".htmlentities($row['folder'], null, "ISO-8859-1").htmlentities($row['filename'], null, "ISO-8859-1");
					$picture = htmlentities($row['picture'], null, "ISO-8859-1");
					$authTime = time();
					$authToken = $auth->getToken($authTime);
					require_once("template/gallery.editpicture.tpl.php");
				}
			}
						
		}
		$this->db->close();
	}
}

$editpicture = new EditPicture();
$editpicture->admin();
?>