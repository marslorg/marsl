<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/mailer.php");
include_once(dirname(__FILE__)."/role.php");

class User {
	
	private $session;
	
	/*
	 * Constructs the session of the user.
	 */
	public function User() {

		if (isset($_COOKIE["sessionid"])) {
			$db = new DB();
			$session = $db->escape($_COOKIE["sessionid"]);
			if ($db->isExisting("SELECT * FROM `user` WHERE `sessionid`='$session'")) {
				$lastseen = $db->escape(time());
				$db->query("UPDATE `user` SET `lastseen` = '$lastseen' WHERE `sessionid` = '$session'");
				$this->session = $session;
			}
		}
	}
	
	/*
	 * Returns whether logged in user is root or directly under the root user.
	 */
	public function isHead() {
		$db = new DB();
		
		$role = new Role();
		$roleID = $db->escape($role->getRole());
		
		$headAdmin = 0;
		
		if ($db->isExisting("SELECT * FROM `role` WHERE `name`='root' AND `role`='$roleID'")) {
			$headAdmin = 1;
		}
		else if ($db->isExisting("SELECT * FROM `role_editor` JOIN `role` ON `master`=`role` WHERE `slave`='$roleID' AND `name`='root'")) {
			$headAdmin = 1;
		}
		
		return $headAdmin;
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
		$role = new Role();
		$db = new DB();
		$roleID = $db->escape($role->getRole());
		$location = $db->isExisting("SELECT * FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1'");
		$module = $db->isExisting("SELECT * FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1'");
		$master = $db->isExisting("SELECT * FROM `role_editor` WHERE `master` = '$roleID'");
		return ($location || $module || $master);
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
	public function logout() {
		$basic = new Basic();
		$db = new DB();
		$session = $db->escape($basic->session());
		$lastlogout = $db->escape(time());
		$oldsession = $db->escape($this->session);
		$db->query("UPDATE `user` SET `lastlogout`='$lastlogout', `sessionid`='$session' WHERE `sessionid`='$oldsession'");
		setcookie("sessionid", "destroyed", time()-3600);
	}
	
	/*
	 * Login a user.
	 */
	public function login($nickname, $password) {
		if (empty($nickname)||empty($password)) {
			return false;
		}
		else {
			$db = new DB();
			$nickname = $db->escape($nickname);
			if ($db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')")) {
				$result = $db->query("SELECT `regdate` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')");
				$regdate = "";
				while ($row = $db->fetchArray($result)) {
					$regdate = $row['regdate'];
				}
				$password = $db->escape($this->hashPassword($regdate, $password));
				if ($db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password'")) {
					$lastlogin = $db->escape(time());
					$basic = new Basic();
					$session = $db->escape($basic->session());
					
					$db->query("UPDATE `user` SET `lastlogin`='$lastlogin', `sessionid`='$session' WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password'");
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
		$user = "";
		$db = new DB();
		$sessionid = $db->escape($this->session);
		$result = $db->query("SELECT `user` FROM `user` WHERE `sessionid`='$sessionid'");
		while ($row = $db->fetchArray($result)) {
			$user = $row['user'];
		}
		return $user;
	}
	
	/*
	 * Gets a user ID by a given name.
	 */
	public function getIDbyName($name) {
		$user = "";
		$db = new DB();
		$name = $db->escape($name);
		$result = $db->query("SELECT `user` FROM `user` WHERE LOWER(`nickname`)=LOWER('$name')");
		while ($row = $db->fetchArray($result)) {
			$user = $row['user'];
		}
		return $user;
	}
	
	/*
	 * Get the password of a user.
	 */
	public function getPassbyID($id) {
		$password = "";
		$db = new DB();
		$user = $db->escape($id);
		$result = $db->query("SELECT `password` FROM `user` WHERE `user`='$user'");
		while ($row = $db->fetchArray($result)) {
			$password = $row['password'];
		}
		return $password;
	}
	
	/*
	 * Get the primary e-mail of a user.
	 */
	public function getMailbyID($id) {
		$mail = "";
		$db = new DB();
		$user = $db->escape($id);
		$result = $db->query("SELECT `email` FROM `email` NATURAL JOIN `user` WHERE `user`='$user' AND `confirmed`='1' AND `primary`='1'");
		while ($row = $db->fetchArray($result)) {
			$mail = htmlentities($row['email'], null, "ISO-8859-1");
		}
		return $mail;
	}
	
	/*
	 * Get the nickname of a user.
	 */
	public function getNickbyID($id) {
		$name = "";
		$db = new DB();
		$user = $db->escape($id);
		$result = $db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
		while ($row = $db->fetchArray($result)) {
			$name = htmlentities($row['nickname'], null, "ISO-8859-1");
		}
		return $name;
	}
	
	/*
	 * Get the acronym of a user.
	 */
	public function getAcronymbyID($id) {
		$acronym = "";
		$db = new DB();
		$user = $db->escape($id);
		$result = $db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
		while ($row = $db->fetchArray($result)) {
			$name = htmlentities($row['acronym'], null, "ISO-8859-1");
			if (empty($name)) {
				$name = $this->getNickbyID($user);
			}
		}
		return $name;
	}

	/*
	 * Get nickname by giving an e-mail adress.
	 */
	public function getNickbyMail($mail) {
		$name = "";
		$db = new DB();
		$mail = $db->escape($mail);
		$result = $db->query("SELECT `nickname` FROM `email` NATURAL JOIN `user` WHERE `email`='$mail' AND `confirmed`='1'");
		while ($row = $db->fetchArray($result)) {
			$name = htmlentities($row['nickname'], null, "ISO-8859-1");
		}
		return $name;
	}
	
	/*
	 * Change the role of a user.
	 */
	public function changeRole($user, $role) {
		$db = new DB();
		$user = $db->escape($user);
		$role = $db->escape($role);
		$db->query("UPDATE `user` SET `role`='$role' WHERE `user`='$user'");
	}
	
	/*
	 * Get the registration date of a user.
	 */
	public function getRegisterDate($user) {
		$regdate = "";
		$db = new DB();
		$user = $db->escape($user);
		$result = $db->query("SELECT `regdate` FROM `user` WHERE `user`='$user'");
		while ($row = $db->fetchArray($result)) {
			$regdate = $row['regdate'];
		}
		return $regdate;
	}
	
	/*
	 * Set the password of a user.
	 */
	public function setPassword($user, $password) {
		$db = new DB();
		$time = $this->getRegisterDate($user);
		$hash = $db->escape($this->hashPassword($time, $password));
		$user = $db->escape($user);
		$db->query("UPDATE `user` SET `password`='$hash' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Update a role of a user and check whether the destination role is a possible role of the user.
	 */
	public function updateRole($user, $roleID) {
		$db = new DB();
		$role = new Role();
		$ownRole = $role->getRole();
		if ($ownRole!=$roleID) {
			$possibleRoles = $role->getPossibleRoles($ownRole);
			foreach ($possibleRoles as $possibleRole) {
				if ($possibleRole == $roleID) {
					$user = $db->escape($user);
					$roleID = $db->escape($roleID);
					$db->query("UPDATE `user` SET `role` = '$roleID' WHERE `user`='$user'");
				}
			}
		}
	}
	
	/*
	 * Update the acronym of the user.
	 */
	public function updateAcronym($user, $acronym) {
		$db = new DB();
		$acronym = $db->escape($acronym);
		$user = $db->escape($user);
		if (empty($acronym)) {
			$db->query("UPDATE `user` SET `acronym` = NULL WHERE `user`='$user'");
			return true;
		}
		else {
			if ((!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`acronym`)=LOWER('$acronym') AND NOT (`user`='$user')"))&&(!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$acronym') AND NOT(`user`='$user')"))) {
				$db->query("UPDATE `user` SET `acronym`='$acronym' WHERE `user`='$user'");
				$proofacronym = null;
				$result = $db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
				while ($row = $db->fetchArray($result)) {
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
		$db = new DB();
		if (strlen($nickname)>=4) {
			$nickname = $db->escape($nickname);
			$user = $db->escape($user);
			if ((!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND NOT (`user`='$user')"))&&(!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname') AND NOT(`user`='$user')"))) {
				$db->query("UPDATE `user` SET `nickname`='$nickname' WHERE `user`='$user'");
				$proofnick = null;
				$result = $db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
				while ($row = $db->fetchArray($result)) {
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
	 * DEPRECATED
	 * Update the e-mail-adress of the user.
	public function updateMail($user, $email) {
		$db = new DB();
		$basic = new Basic();
		if ($basic->checkMail($email)) {
			$email = $db->escape($email);
			$user = $db->escape($user);
			if ($db->isExisting("SELECT `email` FROM `email` WHERE `user`='$user'")) {
				$db->query("UPDATE `email` SET `email`='$email' WHERE `user`='$user'");
			}
			else {
				$time = time();
				$confirmID = $db->escape($basic->confirmID());
				$db->query("INSERT INTO `email`(`email`,`user`,`confirmed`,`time`,`confirm_id`) VALUES('$email','$user','0','$time','$confirmID')");
			}
			$proofmail = null;
			$result = $db->query("SELECT `email` FROM `email` WHERE `user`='$user'");
			while ($row = $db->fetchArray($result)) {
				$proofmail = $row['email'];
			}
			if ($email == $proofmail) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}*/
	
	/*
	 * Update the prename of the user.
	 */
	public function updatePrename($user, $prename) {
		$db = new DB();
		$prename = $db->escape($prename);
		$user = $db->escape($user);
		$db->query("UPDATE `user` SET `prename`='$prename' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Update the family name of a user.
	 */
	public function updateName($user, $name) {
		$db = new DB();
		$name = $db->escape($name);
		$user = $db->escape($user);
		$db->query("UPDATE `user` SET `name`='$name' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Register a user.
	 */
	public function register($nickname, $password, $mail) {
		$db = new DB();
		$nickname = $db->escape($nickname);
		if (strlen($nickname)>=4) {
			if ((!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')"))&&(!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname')"))) {
				$regdate = $db->escape(time());
				$hashPassword = $db->escape($this->hashPassword($regdate, $password));
				$basic = new Basic();
				$session = $db->escape($basic->randomHash().$basic->randomHash());
				$db->query("INSERT INTO `user`(`nickname`,`password`,`postcount`,`regdate`,`sessionid`,`deleted`) VALUES('$nickname','$hashPassword','0','$regdate','$session','0')");
				$user = $db->escape($this->getIDbyName($nickname));
				$confirmID = $db->escape($basic->confirmID());
				$mail = $db->escape($mail);
				$result = $db->query("SELECT `user` FROM `stdroles`");
				while ($row = $db->fetchArray($result)) {
					$role = $db->escape($row['user']);
					$db->query("INSERT INTO `email`(`email`,`user`,`confirmed`,`time`,`confirm_id`,`primary`) VALUES('$mail','$user','0','$regdate','$confirmID','1')");
				}
				$mailer = new Mailer();
				$mailer->sendConfirmationMail($user, $mail);
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
			$intDigitSum = $intDigitSum + $strDigits{$i};
		}
		return $intDigitSum;
	}
	
	/*
	 * Get a list of all admin users.
	 */
	public function getAdminUsers() {
		$role = new Role();
		$admins = array();
		$adminRoles = $role->getAdminRoles();
		$db = new DB();
		foreach ($adminRoles as $adminRole) {
			$roleID = $db->escape($adminRole['role']);
			$result = $db->query("SELECT `user` FROM `user` WHERE `role`='$roleID' AND `deleted`='0'");
			while ($row = $db->fetchArray($result)) {
				array_push($admins, $row['user']);
			}
		}
		return $admins;
	}
}
?>