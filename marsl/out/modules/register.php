<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/module.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/basic.php");

class Register implements Module {

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
		$basic = new Basic($this->db, $this->auth, $this->role);
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		$nickname = "";
		$mail = "";
		$mail2 = "";
		$success = false;
		$captcha = false;
		$mailFailure = false;
		$passwordFailure = false;
		$nicknameFailure = false;
		if ($user->isGuest()||$user->isAdmin()) {
			if ($this->auth->moduleReadAllowed("register", $this->role->getRole())&&$this->auth->locationReadAllowed($location, $this->role->getRole())) {
				if ($this->auth->moduleWriteAllowed("register", $this->role->getRole())&&$this->auth->locationWriteAllowed($location, $this->role->getRole())) {
					if (isset($_POST['action'])) {
						if ($_POST['action']=="send") {
							$mail = $this->db->escapeString($_POST['mail']);
							$mail2 = $this->db->escapeString($_POST['mail2']);
							if (($mail==$mail2)&&($basic->checkMail($mail))) {
								$password = $this->db->escapeString($_POST['password']);
								$password2 = $this->db->escapeString($_POST['password2']);
								if ($password==$password2) {
									$nickname = $this->db->escapeString($_POST['nickname']);
									if ($user->register($nickname, $password, $mail, $this->auth, true)) {
										$success = true;
									}
									else {
										$nicknameFailure = true;
									}
								}
								else {
									$passwordFailure = true;
								}
							}
							else {
								$mailFailure = true;
							}
							if (!$success) {
								$nickname = $basic->convertToHTMLEntities($_POST['nickname']);
								$mail = $basic->convertToHTMLEntities($_POST['mail']);
								$mail2 = $basic->convertToHTMLEntities($_POST['mail2']);
							}
							else {
								$nickname = "";
								$mail = "";
								$mail2 = "";
							}
						}
					}
				}
				require_once("template/register.tpl.php");
			}
		}
	}
	
	public function admin() {
		$basic = new Basic($this->db, $this->auth, $this->role);
		if ($this->auth->moduleAdminAllowed("register", $this->role->getRole())) {
			if (isset($_POST['action'])) {
				if ($_POST['action']=="send"&&$this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$newID = $this->db->escapeString($_POST['location']);
					if ($this->db->isExisting("SELECT `id` FROM `registration_tos` LIMIT 1")) {
						$this->db->query("UPDATE `registration_tos` SET `id`='$newID'");
					}
					else {
						$this->db->query("INSERT INTO `registration_tos`(`id`) VALUES('$newID')");
					}
				}
			}
			$id = "";
			$result = $this->db->query("SELECT `id` FROM `registration_tos`");
			while ($row = $this->db->fetchArray($result)) {
				$id = $row['id'];
			}
			
			$links = array();
			
			$result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `type` IN ('1','2')");
			while ($row = $this->db->fetchArray($result)) {
				$guestRole = $this->role->getGuestRole();
				$location = $row['id'];
				if ($this->auth->locationReadAllowed($location, $guestRole)) {
					$name = $basic->convertToHTMLEntities($row['name']);
					array_push($links, array('id'=>$location, 'name'=>$name));
				}
			}
		}
		$authTime = time();
		$authToken = $this->auth->getToken($authTime);
		require_once("template/register.tos.tpl.php");
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