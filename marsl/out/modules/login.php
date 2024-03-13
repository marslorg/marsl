<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/navigation.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Login implements Module {

	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}
	
	public function display() {
		$user = new User($this->db, $this->role);
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$pageID = $navi->getPageID();
		$location = "";
		if ($pageID > -1) {
			$location = $pageID;
		}
		else {
			$location = $basic->getHomeLocation();
		}

		$uri = $navi->getRelativeURI($location, null, true);
		$forgotURI = $uri."action=forgot";

		if ($user->isGuest()||$user->isAdmin()) {
			
			if ($this->auth->moduleReadAllowed("login", $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())) {
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
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$pageID = $navi->getPageID();
		$location = "";
		if ($pageID > -1) {
			$location = $pageID;
		}
		else {
			$location = $basic->getHomeLocation();
		}

		$baseURI = $navi->getRelativeURI($location, null, true);
		$baseForgotURI = $baseURI."action=forgot";
		$baseRecoverURI = $baseForgotURI."&action2=recover";
		
		if (isset($_GET['status'])&&$_GET['status']=="success") {
			$init = false;
			$success = true;
			$recover = true;
			$basic = new Basic($this->db, $this->auth, $this->role);
			$title = $basic->convertToHTMLEntities($basic->getTitle());
			require_once("template/recover.tpl.php");
		}
		else {
			if (isset($_GET['subaction'])) {
				if ($_GET['subaction']=="set") {
					$time = $_GET['time'];

					$config = new Configuration();

					if ($time+172800 >= time()) {
						$uid = $_GET['uid'];
						$user = new User($this->db, $this->role);
						$password = $user->getPassbyID($uid);
						$auth_code = md5("admin".$uid.$time.$password);
						$authParameter = $_GET['auth'];
						if ($auth_code == $authParameter) {
							$password = $_POST['password'];
							$password2 = $_POST['password2'];
							if ($password==$password2) {
								$user->setPassword($uid, $password);
								header("Location: ".$baseRecoverURI."&status=success");
							}
							else {
								header("Location: ".$baseRecoverURI."&status=failed&uid=".$uid."&time=".$time."&auth=".$authParameter);
							}
						}
						else {
							header("Location: ".$baseRecoverURI."&uid=".$uid."&time=".$time."&auth=".$authParameter);
						}
					}
					else {
						header("Location: ".$baseRecoverURI."&uid=".$uid."&time=".$time."&auth=".$authParameter);
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
		$navi = new Navigation($this->db, $this->auth, $this->role);
		$pageID = $navi->getPageID();
		$location = "";
		if ($pageID > -1) {
			$location = $pageID;
		}
		else {
			$location = $basic->getHomeLocation();
		}
		
		$basic = new Basic($this->db, $this->auth, $this->role);
		$title = $basic->convertToHTMLEntities($basic->getTitle());
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
			$user = new User($this->db, $this->role);
			$password = $user->getPassbyID($uid);
			$auth_code = md5("admin".$uid.$time.$password);
			$authParameter = $_GET['auth'];
			if ($auth_code == $authParameter) {
				$recover = true;
			}
		}

		$baseURI = $navi->getRelativeURI($location, null, true);
		$baseForgotURI = $baseURI."action=forgot";
		$baseRecoverURI = $baseForgotURI."&action2=recover";
		$baseRecoverSetURI = $baseRecoverURI."&subaction=set&uid=".$uid."&time=".$time."&auth=".$authParameter;

		require_once("template/recover.tpl.php");
	}
	
	public function displayTag() {
	}
	
	public function getImage() {
		return null;
	}
	
	public function getTitle() {
		return null;
	}

	public function getRestfulURIPartFromOldURL() {
		return null;
	}

	public function getOldURIPartFromRestfulURL() {
		return null;
	}
}
?>