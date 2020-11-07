<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/basic.php");

class Login implements Module {

	private $db;
	private $auth;

	public function __construct($db, $auth) {
		$this->db = $db;
		$this->auth = $auth;
	}
	
	public function display() {
		$user = new User($this->db);
		$role = new Role($this->db);
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		if ($user->isGuest()||$user->isAdmin()) {
			
			if ($this->auth->moduleReadAllowed("login", $role->getRole())&&$this->auth->locationReadAllowed($location, $role->getRole())) {
				if (isset($_GET['action'])) {
					if ($_GET['action']=="forgot") {
						if (isset($_GET['action2'])) {
							if ($_GET['action2']=="recover") {
								$this->recover();
							}
							else {
								$init = true;
								$success = false;
								if ($_GET['action2']=="success") {
									$init = false;
									$success = true;
									$topic = $_GET['topic'];
								}
								elseif ($_GET['action2']=="failed") {
									$init = false;
									$success = false;
									$topic = $_GET['topic'];
								}
								require_once("template/login.forgot.tpl.php");
							}
						}
						else {
							$init = true;
							$success = false;
							if (isset($_GET['action2'])) {
								if ($_GET['action2']=="success") {
									$init = false;
									$success = true;
									$topic = $_GET['topic'];
								}
								elseif ($_GET['action2']=="failed") {
									$init = false;
									$success = false;
									$topic = $_GET['topic'];
								}
							}
							require_once("template/login.forgot.tpl.php");
						}
					}
					else {
						$wrongpw = "";
						if (isset($_GET['wrongpw'])) {
							$wrongpw = $_GET['wrongpw'];
						}
						require_once("template/login.tpl.php");
					}
				}
				else {
					$wrongpw = "";
					if (isset($_GET['wrongpw'])) {
						$wrongpw = $_GET['wrongpw'];
					}
					require_once("template/login.tpl.php");
				}
			}
			
		}
	}
	
	public function admin() {
		echo "Nichts zu tun hier.";
	}
	
	public function isSearchable() {
		return false;
	}
	
	public function getSearchList() {
		return null;
	}
	
	public function search($query, $type) {
		return null;
	}
	
	public function isTaggable() {
		return false;
	}
	
	public function getTagList() {
		return null;
	}
	
	public function addTags($tagString, $type, $news) {
		
	}
	
	public function getTagString($type, $news) {
		return null;
	}
	
	public function getTags($type, $news) {
		return null;
	}

	private function recover() {
		
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		
		if (isset($_GET['status'])&&$_GET['status']=="success") {
			$init = false;
			$success = true;
			$recover = true;
			$basic = new Basic($this->db, $this->auth);
			$title = htmlentities($basic->getTitle(), null, "ISO-8859-1");
			require_once("template/recover.tpl.php");
		}
		else {
			if (isset($_GET['subaction'])) {
				if ($_GET['subaction']=="set") {
					$time = $_GET['time'];
					if ($time+172800 >= time()) {
						$uid = $_GET['uid'];
						$user = new User($this->db);
						$password = $user->getPassbyID($uid);
						$auth_code = md5("admin".$uid.$time.$password);
						$authParameter = $_GET['auth'];
						if ($auth_code == $authParameter) {
							$password = $_POST['password'];
							$password2 = $_POST['password2'];
							if ($password==$password2) {
								$user->setPassword($uid, $password);
								header("Location: index.php?id=".$location."&action=forgot&action2=recover&status=success");
							}
							else {
								header("Location: index.php?id=".$location."&action=forgot&action2=recover&status=failed&uid=".$uid."&time=".$time."&auth=".$authParameter);
							}
						}
						else {
							header("Location: index.php?id=".$location."&action=forgot&action2=recover&uid=".$uid."&time=".$time."&auth=".$authParameter);
						}
					}
					else {
						header("Location: index.php?id=".$location."&action=forgot&action2=recover&uid=".$uid."&time=".$time."&auth=".$authParameter);
					}
						
				}
				else {
					$this->recoverBox();
				}
			}
			else {
				$this->recoverBox();
			}
		}
	}
	
	private function recoverBox() {
		
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		
		$basic = new Basic($this->db, $this->auth);
		$title = htmlentities($basic->getTitle(), null, "ISO-8859-1");
		$time = $_GET['time'];
		$recover = false;
		$uid = "";
		$authParameter = "";
		$init = true;
		$success = false;
		if (isset($_GET['status'])) {
			if ($_GET['status']=="failed") {
				$init = false;
				$success = false;
			}
		}
		if ($time+172800 >= time()) {
			$uid = $_GET['uid'];
			$user = new User($this->db);
			$password = $user->getPassbyID($uid);
			$auth_code = md5("admin".$uid.$time.$password);
			$authParameter = $_GET['auth'];
			if ($auth_code == $authParameter) {
				$recover = true;
			}
		}
		require_once("template/recover.tpl.php");
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