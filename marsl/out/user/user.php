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
			$session = mysql_real_escape_string($_COOKIE["sessionid"]);
			$db = new DB();
			if ($db->isExisting("SELECT * FROM `user` WHERE `sessionid`='$session'")) {
				$lastseen = mysql_real_escape_string(time());
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
		$roleID = mysql_real_escape_string($role->getRole());
		
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
		$roleID = mysql_real_escape_string($role->getRole());
		$db = new DB();
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
		$session = mysql_real_escape_string($basic->session());
		$lastlogout = mysql_real_escape_string(time());
		$oldsession = mysql_real_escape_string($this->session);
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
			$nickname = mysql_real_escape_string($nickname);
			$db = new DB();
			if ($db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')")) {
				$result = $db->query("SELECT `regdate` FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')");
				$regdate = "";
				while ($row = mysql_fetch_array($result)) {
					$regdate = $row['regdate'];
				}
				$password = mysql_real_escape_string($this->hashPassword($regdate, $password));
				if ($db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND `password`='$password'")) {
					$lastlogin = mysql_real_escape_string(time());
					$basic = new Basic();
					$session = mysql_real_escape_string($basic->session());
					
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
		$sessionid = mysql_real_escape_string($this->session);
		$result = $db->query("SELECT `user` FROM `user` WHERE `sessionid`='$sessionid'");
		while ($row = mysql_fetch_array($result)) {
			$user = $row['user'];
		}
		return $user;
	}
	
	/*
	 * Gets a user ID by a given name.
	 */
	public function getIDbyName($name) {
		$user = "";
		$name = mysql_real_escape_string($name);
		$db = new DB();
		$result = $db->query("SELECT `user` FROM `user` WHERE LOWER(`nickname`)=LOWER('$name')");
		while ($row = mysql_fetch_array($result)) {
			$user = $row['user'];
		}
		return $user;
	}
	
	/*
	 * Get the password of a user.
	 */
	public function getPassbyID($id) {
		$password = "";
		$user = mysql_real_escape_string($id);
		$db = new DB();
		$result = $db->query("SELECT `password` FROM `user` WHERE `user`='$user'");
		while ($row = mysql_fetch_array($result)) {
			$password = $row['password'];
		}
		return $password;
	}
	
	/*
	 * Get the e-mail of a user.
	 */
	public function getMailbyID($id) {
		$mail = "";
		$user = mysql_real_escape_string($id);
		$db = new DB();
		$result = $db->query("SELECT `email` FROM `email` NATURAL JOIN `user` WHERE `user`='$user' AND `confirmed`='1'");
		while ($row = mysql_fetch_array($result)) {
			$mail = htmlentities($row['email'], ENT_HTML5, "ISO-8859-1");
		}
		return $mail;
	}
	
	/*
	 * Get the nickname of a user.
	 */
	public function getNickbyID($id) {
		$name = "";
		$user = mysql_real_escape_string($id);
		$db = new DB();
		$result = $db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
		while ($row = mysql_fetch_array($result)) {
			$name = htmlentities($row['nickname'], ENT_HTML5, "ISO-8859-1");
		}
		return $name;
	}
	
	/*
	 * Get the acronym of a user.
	 */
	public function getAcronymbyID($id) {
		$acronym = "";
		$user = mysql_real_escape_string($id);
		$db = new DB();
		$result = $db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
		while ($row = mysql_fetch_array($result)) {
			$name = htmlentities($row['acronym'], ENT_HTML5, "ISO-8859-1");
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
		$mail = mysql_real_escape_string($mail);
		$db = new DB();
		$result = $db->query("SELECT `nickname` FROM `email` NATURAL JOIN `user` WHERE `email`='$mail' AND `confirmed`='1'");
		while ($row = mysql_fetch_array($result)) {
			$name = htmlentities($row['nickname'], ENT_HTML5, "ISO-8859-1");
		}
		return $name;
	}
	
	/*
	 * Change the role of a user.
	 */
	public function changeRole($user, $role) {
		$user = mysql_real_escape_string($user);
		$role = mysql_real_escape_string($role);
		$db = new DB();
		$db->query("UPDATE `user` SET `role`='$role' WHERE `user`='$user'");
	}
	
	/*
	 * Get the registration date of a user.
	 */
	public function getRegisterDate($user) {
		$regdate = "";
		$db = new DB();
		$user = mysql_real_escape_string($user);
		$result = $db->query("SELECT `regdate` FROM `user` WHERE `user`='$user'");
		while ($row = mysql_fetch_array($result)) {
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
		$hash = mysql_real_escape_string($this->hashPassword($time, $password));
		$user = mysql_real_escape_string($user);
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
					$user = mysql_real_escape_string($user);
					$roleID = mysql_real_escape_string($roleID);
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
		$acronym = mysql_real_escape_string($acronym);
		$user = mysql_real_escape_string($user);
		if (empty($acronym)) {
			$db->query("UPDATE `user` SET `acronym` = NULL WHERE `user`='$user'");
			return true;
		}
		else {
			if ((!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`acronym`)=LOWER('$acronym') AND NOT (`user`='$user')"))&&(!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$acronym') AND NOT(`user`='$user')"))) {
				$db->query("UPDATE `user` SET `acronym`='$acronym' WHERE `user`='$user'");
				$proofacronym = null;
				$result = $db->query("SELECT `acronym` FROM `user` WHERE `user`='$user'");
				while ($row = mysql_fetch_array($result)) {
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
			$nickname = mysql_real_escape_string($nickname);
			$user = mysql_real_escape_string($user);
			if ((!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname') AND NOT (`user`='$user')"))&&(!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname') AND NOT(`user`='$user')"))) {
				$db->query("UPDATE `user` SET `nickname`='$nickname' WHERE `user`='$user'");
				$proofnick = null;
				$result = $db->query("SELECT `nickname` FROM `user` WHERE `user`='$user'");
				while ($row = mysql_fetch_array($result)) {
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
	 * Update the e-mail-adress of the user.
	 */
	public function updateMail($user, $email) {
		$db = new DB();
		$basic = new Basic();
		if ($basic->checkMail($email)) {
			$email = mysql_real_escape_string($email);
			$user = mysql_real_escape_string($user);
			if ($db->isExisting("SELECT `email` FROM `email` WHERE `user`='$user'")) {
				$db->query("UPDATE `email` SET `email`='$email' WHERE `user`='$user'");
			}
			else {
				$time = time();
				$confirmID = mysql_real_escape_string($basic->confirmID());
				$db->query("INSERT INTO `email`(`email`,`user`,`confirmed`,`time`,`confirm_id`) VALUES('$email','$user','0','$time','$confirmID')");
			}
			$proofmail = null;
			$result = $db->query("SELECT `email` FROM `email` WHERE `user`='$user'");
			while ($row = mysql_fetch_array($result)) {
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
	}
	
	/*
	 * Update the prename of the user.
	 */
	public function updatePrename($user, $prename) {
		$db = new DB();
		$prename = mysql_real_escape_string($prename);
		$user = mysql_real_escape_string($user);
		$db->query("UPDATE `user` SET `prename`='$prename' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Update the family name of a user.
	 */
	public function updateName($user, $name) {
		$db = new DB();
		$name = mysql_real_escape_string($name);
		$user = mysql_real_escape_string($user);
		$db->query("UPDATE `user` SET `name`='$name' WHERE `user`='$user'");
		return true;
	}
	
	/*
	 * Register a user.
	 */
	public function register($nickname, $password, $mail) {
		$db = new DB();
		$nickname = mysql_real_escape_string($nickname);
		if (strlen($nickname)>=4) {
			if ((!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`nickname`)=LOWER('$nickname')"))&&(!$db->isExisting("SELECT * FROM `user` WHERE LOWER(`acronym`)=LOWER('$nickname')"))) {
				$regdate = mysql_real_escape_string(time());
				$hashPassword = mysql_real_escape_string($this->hashPassword($regdate, $password));
				$basic = new Basic();
				$session = mysql_real_escape_string($basic->randomHash().$basic->randomHash());
				$db->query("INSERT INTO `user`(`nickname`,`password`,`postcount`,`regdate`,`sessionid`,`deleted`) VALUES('$nickname','$hashPassword','0','$regdate','$session','0')");
				$user = mysql_real_escape_string($this->getIDbyName($nickname));
				$confirmID = mysql_real_escape_string($basic->confirmID());
				$mail = mysql_real_escape_string($mail);
				$result = $db->query("SELECT `user` FROM `stdroles`");
				while ($row = mysql_fetch_array($result)) {
					$role = mysql_real_escape_string($row['user']);
					$db->query("INSERT INTO `email`(`email`,`user`,`confirmed`,`time`,`confirm_id`) VALUES('$mail','$user','0','$regdate','$confirmID')");
				}
				$mailer = new Mailer();
				$mailer->sendConfirmationMail($confirmID);
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
			$roleID = mysql_real_escape_string($adminRole['role']);
			$result = $db->query("SELECT `user` FROM `user` WHERE `role`='$roleID' AND `deleted`='0'");
			while ($row = mysql_fetch_array($result)) {
				array_push($admins, $row['user']);
			}
		}
		return $admins;
	}
}
?>