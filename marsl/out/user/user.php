<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/mailer.php");
include_once(dirname(__FILE__)."/role.php");

class User {
	
	private $session;
	private $db;
	private $role;
	private $headAdmin;
	private $headAdminSet;
	private $root;
	private $rootSet;
	private $isAdmin;
	private $isAdminSet;
	private $userID;
	private $userIDSet;
	private $userIDsByName;
	private $passwordsByID;
	private $mailsByID;
	private $nicksByID;
	private $acronymsByID;
	private $nicksByMail;
	private $registerDatesByUser;
	private $auth;
	
	/*
	 * Constructs the session of the user.
	 */
	public function __construct($db, $role) {
		$this->db = $db;
		$this->role = $role;
		$this->headAdminSet = false;
		$this->headAdmin = 0;
		$this->root = false;
		$this->rootSet = false;
		$this->isAdmin = false;
		$this->isAdminSet = false;
		$this->userID = "";
		$this->userIDSet = false;
		$this->userIDsByName = array();
		$this->passwordsByID = array();
		$this->mailsByID = array();
		$this->nicksByID = array();
		$this->acronymsByID = array();
		$this->nicksByMail = array();
		$this->registerDatesByUser = array();
		if (isset($_COOKIE["sessionid"])) {
			$session = $this->db->escapeString($_COOKIE["sessionid"]);
			if ($this->db->isExisting("SELECT `sessionid` FROM `user` WHERE `sessionid`='$session' LIMIT 1")) {
				$lastseen = $this->db->escapeString(time());
				$this->db->query("UPDATE `user` SET `lastseen` = '$lastseen' WHERE `sessionid` = '$session'");
				$this->session = $session;
			}
		}
	}
	
	/*
	 * Returns whether logged in user is root or directly under the root user.
	 */
	public function isHead() {	
        if (!$this->headAdminSet) {
			$roleID = $this->db->escapeString($this->role->getRole());
            if ($this->db->isExisting("SELECT `role` FROM `role` WHERE `name`='root' AND `role`='$roleID' LIMIT 1")) {
                $this->headAdmin = 1;
            } elseif ($this->db->isExisting("SELECT `name` FROM `role_editor` JOIN `role` ON `master`=`role` WHERE `slave`='$roleID' AND `name`='root' LIMIT 1")) {
                $this->headAdmin = 1;
            }
        }

		$this->headAdminSet = true;
		
		return $this->headAdmin;
	}

	public function isRoot() {
        if (!$this->rootSet) {
            $roleID = $this->db->escapeString($this->role->getRole());

            if ($this->db->isExisting("SELECT `role` FROM `role` WHERE `name`='root' AND `role`='$roleID' LIMIT 1")) {
                $this->root = true;
            }
        }

		$this->rootSet = true;

		return $this->root;
	}
	
	/*
	 * Returns whether the logged in user is a guest.
	 */
	public function isGuest() {
		return empty($this->session);
	}
	
	/*
	 * Returns whether the logged in user has access rights on the admin panel.
	 */
	public function isAdmin() {
        if (!$this->isAdminSet) {
            $roleID = $this->db->escapeString($this->role->getRole());
            $location = $this->db->isExisting("SELECT `role` FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
            $module = $this->db->isExisting("SELECT `role` FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
            $master = $this->db->isExisting("SELECT `master` FROM `role_editor` WHERE `master` = '$roleID' LIMIT 1");
            $this->isAdmin = $location || $module || $master;
        }

		$this->isAdminSet = true;

		return $this->isAdmin;
	}
	
	/*
	 * Returns the session.
	 */
	public function getSession() {
		return $this->session;
	}
	
	/*
	 * Logout a user.
	 */
	public function logout($auth) {
		$basic = new Basic($this->db, $auth, $this->role);
		$session = $this->db->escapeString($basic->session());
		$lastlogout = $this->db->escapeString(time());
		$oldsession = $this->db->escapeString($this->session);
		$this->db->query("UPDATE `user` SET `lastlogout`='$lastlogout', `sessionid`='$session' WHERE `sessionid`='$oldsession'");
		setcookie("sessionid", "destroyed", time()-3600);
	}
	
	/*
	 * Login a user.
	 */
	public function login($nickname, $password, $auth) {
		if (empty($nickname)||empty($password)) {
			return false;
		}
		else {
			$nickname = $this->db->escapeString($nickname);
			if ($this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') LIMIT 1")) {
				$result = $this->db->query("SELECT `regdate` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')");
				$regdate = "";
				while ($row = $this->db->fetchArray($result)) {
					$regdate = $row['regdate'];
				}
				$password = $this->db->escapeString($this->hashPassword($regdate, $password));
				if ($this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password' LIMIT 1")) {
					$lastlogin = $this->db->escapeString(time());
					$basic = new Basic($this->db, $auth, $this->role);
					$session = $this->db->escapeString($basic->session());
					
					$this->db->query("UPDATE `user` SET `lastlogin`='$lastlogin', `sessionid`='$session' WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password'");
					setcookie("sessionid",$session,time()+(3600*24*365));
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
	}
	
	/*
	 * Gets the ID of the logged in user.
	 */
	public function getID() {
        if (!$this->userIDSet) {
            $sessionid = $this->db->escapeString($this->session);
            $result = $this->db->query("SELECT `user` FROM `user` WHERE `sessionid`='$sessionid'");
            while ($row = $this->db->fetchArray($result)) {
                $this->userID = $row['user'];
            }
        }

		$this->userIDSet = true;

		return $this->userID;
	}
	
	/*
	 * Gets a user ID by a given name.
	 */
	public function getIDbyName($name) {
		if (!array_key_exists($name, $this->userIDsByName)) {
            $name = $this->db->escapeString($name);
			$user = "";
            $result = $this->db->query("SELECT `user` FROM `user` WHERE LOWER(`nickname`)=LOWER('$name')");
            while ($row = $this->db->fetchArray($result)) {
                $user = $row['user'];
            }
			$this->userIDsByName[$name]  = $user;
        }
		return $this->userIDsByName[$name];
	}
	
	/*
	 * Get the password of a user.
	 */
	public function getPassbyID($id) {
        if (!array_key_exists($id, $this->passwordsByID)) {
            $password = "";
            $user = $this->db->escapeString($id);
            $result = $this->db->query("SELECT `password` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                $password = $row['password'];
            }
			$this->passwordsByID[$id] = $password;
        }
		return $this->passwordsByID[$id];
	}
	
	/*
	 * Get the primary e-mail of a user.
	 */
	public function getMailbyID($id, $auth) {
		$basic = new Basic($this->db, $auth, $this->role);
        if (!array_key_exists($id, $this->mailsByID)) {
            $mail = "";
            $user = $this->db->escapeString($id);
            $result = $this->db->query("SELECT `email` FROM `email` NATURAL JOIN `user` WHERE `user`='$user' AND `confirmed`='1' AND `primary`='1'");
            while ($row = $this->db->fetchArray($result)) {
                $mail = $basic->convertToHTMLEntities($row['email']);
            }
			$this->mailsByID[$id] = $mail;
        }
		return $this->mailsByID[$id];
	}
	
	/*
	 * Get the nickname of a user.
	 */
	public function getNickbyID($id, $auth) {
		$basic = new Basic($this->db, $auth, $this->role);
        if (!array_key_exists($id, $this->nicksByID)) {
            $name = "";
            $user = $this->db->escapeString($id);
            $result = $this->db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                $name = $basic->convertToHTMLEntities($row['nickname']);
            }
			$this->nicksByID[$id] = $name;
        }
		return $this->nicksByID[$id];
	}
	
	/*
	 * Get the acronym of a user.
	 */
	public function getAcronymbyID($id, $auth) {
		$basic = new Basic($this->db, $auth, $this->role);
        if (!array_key_exists($id, $this->acronymsByID)) {
            $acronym = "";
            $user = $this->db->escapeString($id);
            $result = $this->db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                $acronym = $basic->convertToHTMLEntities($row['acronym']);
                if (empty($acronym)) {
                    $acronym = $this->getNickbyID($user, $auth);
                }
            }
			$this->acronymsByID[$id] = $acronym;
        }
		return $this->acronymsByID[$id];
	}

	/*
	 * Get nickname by giving an e-mail adress.
	 */
	public function getNickbyMail($mail, $auth) {
		$basic = new Basic($this->db, $auth, $this->role);
        if (!array_key_exists($mail, $this->nicksByMail)) {
            $name = "";
            $mail = $this->db->escapeString($mail);
            $result = $this->db->query("SELECT `nickname` FROM `email` NATURAL JOIN `user` WHERE `email`='$mail' AND `confirmed`='1'");
            while ($row = $this->db->fetchArray($result)) {
                $name = $basic->convertToHTMLEntities($row['nickname']);
            }
			$this->nicksByMail[$mail]  = $name;
        }
		return $this->nicksByMail[$mail];
	}
	
	/*
	 * Change the role of a user.
	 */
	public function changeRole($user, $role) {
		$user = $this->db->escapeString($user);
		$role = $this->db->escapeString($role);
		$this->db->query("UPDATE `user` SET `role`='$role' WHERE `user`='$user'");
	}
	
	/*
	 * Get the registration date of a user.
	 */
	public function getRegisterDate($user) {
        if (!array_key_exists($user, $this->registerDatesByUser)) {
            $regdate = "";
            $user = $this->db->escapeString($user);
            $result = $this->db->query("SELECT `regdate` FROM `user` WHERE `user`='$user'");
            while ($row = $this->db->fetchArray($result)) {
                $regdate = $row['regdate'];
            }
			$this->registerDatesByUser[$user] = $regdate;
        }
		return $this->registerDatesByUser[$user];
	}
	
	/*
	 * Set the password of a user.
	 */
	public function setPassword($user, $password) {
		$time = $this->getRegisterDate($user);
		$hash = $this->db->escapeString($this->hashPassword($time, $password));
		$user = $this->db->escapeString($user);
		$this->db->query("UPDATE `user` SET `password`='$hash' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Update a role of a user and check whether the destination role is a possible role of the user.
	 */
	public function updateRole($user, $roleID) {
		$ownRole = $this->role->getRole();
		if ($ownRole!=$roleID) {
			$possibleRoles = $this->role->getPossibleRoles($ownRole);
			foreach ($possibleRoles as $possibleRole) {
				if ($possibleRole == $roleID) {
					$user = $this->db->escapeString($user);
					$roleID = $this->db->escapeString($roleID);
					$this->db->query("UPDATE `user` SET `role` = '$roleID' WHERE `user`='$user'");
				}
			}
		}
	}
	
	/*
	 * Update the acronym of the user.
	 */
	public function updateAcronym($user, $acronym) {
		$acronym = $this->db->escapeString($acronym);
		$user = $this->db->escapeString($user);
		if (empty($acronym)) {
			$this->db->query("UPDATE `user` SET `acronym` = NULL WHERE `user`='$user'");
			return true;
		}
		else {
			if ((!$this->db->isExisting("SELECT `acronym` FROM `user` WHERE LOWER(`acronym`)=LOWER('$acronym') AND NOT (`user`='$user') LIMIT 1"))&&(!$this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$acronym') AND NOT(`user`='$user') LIMIT 1"))) {
				$this->db->query("UPDATE `user` SET `acronym`='$acronym' WHERE `user`='$user'");
				$proofacronym = null;
				$result = $this->db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
				while ($row = $this->db->fetchArray($result)) {
					$proofacronym = $row['acronym'];
				}
				if ($acronym==$proofacronym) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
	}
	
	/*
	 * Update the nickname of the user.
	 */
	public function updateNickname($user, $nickname) {
		if (strlen($nickname)>=4) {
			$nickname = $this->db->escapeString($nickname);
			$user = $this->db->escapeString($user);
			if ((!$this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND NOT (`user`='$user') LIMIT 1"))&&(!$this->db->isExisting("SELECT `acronym` FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname') AND NOT(`user`='$user') LIMIT 1"))) {
				$this->db->query("UPDATE `user` SET `nickname`='$nickname' WHERE `user`='$user'");
				$proofnick = null;
				$result = $this->db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
				while ($row = $this->db->fetchArray($result)) {
					$proofnick = $row['nickname'];
				}
				if ($nickname==$proofnick) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	/*
	 * Update the prename of the user.
	 */
	public function updatePrename($user, $prename) {
		$prename = $this->db->escapeString($prename);
		$user = $this->db->escapeString($user);
		$this->db->query("UPDATE `user` SET `prename`='$prename' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Update the family name of a user.
	 */
	public function updateName($user, $name) {
		$name = $this->db->escapeString($name);
		$user = $this->db->escapeString($user);
		$this->db->query("UPDATE `user` SET `name`='$name' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Register a user.
	 */
	public function register($nickname, $password, $mail, $auth, $shouldSendConfirmationMail) {
		$nickname = $this->db->escapeString($nickname);
		if (strlen($nickname)>=4) {
			if ((!$this->db->isExisting("SELECT `nickname` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') LIMIT 1"))&&(!$this->db->isExisting("SELECT `acronym` FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname') LIMIT 1"))) {
				$regdate = $this->db->escapeString(time());
				$hashPassword = $this->db->escapeString($this->hashPassword($regdate, $password));
				$basic = new Basic($this->db, $auth, $this->role);
				$session = $this->db->escapeString($basic->randomHash().$basic->randomHash());
				$roleID = $this->role->getUserRole();
				$this->db->query("INSERT INTO `user`(`nickname`,`password`,`postcount`,`regdate`,`sessionid`,`deleted`,`role`) VALUES('$nickname','$hashPassword','0','$regdate','$session','0','$roleID')");
				$user = $this->db->escapeString($this->getIDbyName($nickname));
				$confirmID = $this->db->escapeString($basic->confirmID());
				$mail = $this->db->escapeString($mail);
				$this->db->query("INSERT INTO `email`(`email`,`user`,`confirmed`,`time`,`confirm_id`,`primary`) VALUES('$mail','$user','0','$regdate','$confirmID','1')");
                if ($shouldSendConfirmationMail) {
                    $mailer = new Mailer($this->db, $this->role);
                    $mailer->sendConfirmationMail($user, $mail);
                }
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	/*
	 * Hash a password.
	 */
	public function hashPassword($time, $password) {
		$digitSum = $this->digitSum($time);
		$hash = $password;
		for ($i = 0; $i <= $digitSum; $i++) {
			$hash = hash("sha512", $hash.$time);
		}
		return $hash;
	}
	
	/*
	 * Calculate the digit sum of a UNIX Timestamp or other integer.
	 */
	private function digitSum($time) {
		if ($time < 0) {
			$time = (-1) * $time;
		}
		else if ($time == 0) {
			$time = 1;
		}
		$strDigits = (string) $time;
		$intDigitSum = 0;
		for ($i = 0; $i < strlen($strDigits); $i++) {
			$intDigitSum = $intDigitSum + $strDigits[$i];
		}
		return $intDigitSum;
	}
	
	/*
	 * Get a list of all admin users.
	 */
	public function getAdminUsers() {
		$admins = array();
		$adminRoles = $this->role->getAdminRoles();
		foreach ($adminRoles as $adminRole) {
			$roleID = $this->db->escapeString($adminRole['role']);
			$result = $this->db->query("SELECT `user` FROM `user` WHERE `role`='$roleID' AND `deleted`='0'");
			while ($row = $this->db->fetchArray($result)) {
				array_push($admins, $row['user']);
			}
		}
		return $admins;
	}
}
?>