<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/mailer.php");
include_once(dirname(__FILE__)."/module.php");

class UserData implements Module {
	
	/*
	 * Displays the user administration.
	 */
	public function admin() {
		$user = new User();
		$db = new DB();
		$auth = new Authentication();
		$role = new Role();
		$mailer = new Mailer();
		$basic = new Basic();
		if ($auth->moduleAdminAllowed("userdata", $role->getRole())||$auth->moduleExtendedAllowed("userdata", $role->getRole())) {
			if ($auth->moduleAdminAllowed("userdata", $role->getRole())) {
				require_once("template/userdata.alphabet.tpl.php");
			}
			if (isset($_GET['action'])) {
				if (($_GET['action']=="list")&&$auth->moduleAdminAllowed("userdata", $role->getRole())) {
					$userdata = array();
					$search = mysql_real_escape_string($_GET['search']);
					$ownRole = $role->getRole();
					$possibleRoles = $role->getPossibleRoles($ownRole);
					$result = $db->query("SELECT `user`, `user`.`role` AS `roleid`, `nickname`, `prename`, `acronym`, `regdate`, `email`, `postcount`, `user`.`name` AS `username`, `role`.`name` AS `rolename` FROM `user` JOIN `role` USING(`role`) LEFT OUTER JOIN `email` USING(`user`) WHERE `nickname` LIKE '$search%' ORDER BY `nickname`");
					while ($row = mysql_fetch_array($result)) {
						$userid = htmlentities($row['user'], null, "ISO-8859-1");
						$nickname = htmlentities($row['nickname'], null, "ISO-8859-1");
						$prename = htmlentities($row['prename'], null, "ISO-8859-1");
						$acronym = htmlentities($row['acronym'], null, "ISO-8859-1");
						$regdate = date("d\. M Y\; H\:i\:s", $row['regdate']);
						$email = htmlentities($row['email'], null, "ISO-8859-1");
						$postcount = htmlentities($row['postcount'], null, "ISO-8859-1");
						$name = htmlentities($row['username'], null, "ISO-8859-1");
						$rolename = htmlentities($row['rolename'], null, "ISO-8859-1");
						$roleid = htmlentities($row['roleid'], null, "ISO-8859-1");
						$isMaster = $role->isMaster($ownRole, $roleid, $possibleRoles);
						if ($user->getID()==$userid) {
							$isMaster = true;
						}
						array_push($userdata, array('user'=>$userid, 'nickname'=>$nickname, 'prename'=>$prename, 'acronym'=>$acronym, 'regdate'=>$regdate, 'email'=>$email, 'postcount'=>$postcount, 'name'=>$name, 'rolename'=>$rolename, 'isMaster'=>$isMaster));
					}
					require_once("template/userdata.list.tpl.php");
				}
				if ($_GET['action']=="details") {
					if ($auth->moduleAdminAllowed("userdata", $role->getRole())||($auth->moduleExtendedAllowed("userdata", $role->getRole())&&($_GET['user']==$user->getID()))) {
						$userID = mysql_real_escape_string($_GET['user']);
						$ownID = $user->getID();
						$ownRole = $role->getRole();
						$possibleRoles = $role->getPossibleRoles($ownRole);
						
						if (isset($_POST['entermail'])) {
							if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
								$email = mysql_real_escape_string($_POST['email']);
								if ($basic->checkMail($email)) {
									$curTime = time();
									$confirmID = $basic->confirmID();
									$db->query("INSERT INTO `email`(`email`,`user`, `confirmed`, `time`, `confirm_id`) VALUES('$email', '$userID', '1', '$curTime', '$confirmID')");
								}
							}
						}
						
						if (isset($_GET['delmail'])) {
							if ($auth->checkToken($_GET['time'], $_GET['token'])) {
								$email = mysql_real_escape_string(urldecode($_GET['delmail']));
								$db->query("DELETE FROM `email` WHERE `user`='$userID' AND `primary`='0' AND `email`='$email'");
							}
						}
						if (isset($_GET['primemail'])) {
							if ($auth->checkToken($_GET['time'], $_GET['token'])) {
								$email = mysql_real_escape_string(urldecode($_GET['primemail']));
								if (!$db->isExisting("SELECT `email` FROM `email` WHERE `email`='$email' AND `user`='$userID' AND `confirmed`='0'")) {
									$db->query("UPDATE `email` SET `primary`='0' WHERE `user`='$userID'");
									$db->query("UPDATE `email` SET `primary`='1' WHERE `user`='$userID' AND `email`='$email'");
								}
							}
						}
						if (isset($_GET['confmail'])) {
							if ($auth->checkToken($_GET['time'], $_GET['token'])) {
								$email = mysql_real_escape_string(urldecode($_GET['confmail']));
								$mailer->sendConfirmationMail($userID, $email);
							}
						}
							
						$result = $db->query("SELECT `user`, `regdate`, `role`, `nickname`, `prename`, `acronym`, `name` FROM `user` WHERE `user`='$userID'");
						while ($row = mysql_fetch_array($result)) {
							$userRole = htmlentities($row['role'], null, "ISO-8859-1");
							$isMaster = $role->isMaster($ownRole, $userRole, $possibleRoles);
							if ($isMaster||($user->getID()==$userID)) {
								$userID = htmlentities($row['user'], null, "ISO-8859-1");
								$nickname = htmlentities($row['nickname'], null, "ISO-8859-1");
								$prename = htmlentities($row['prename'], null, "ISO-8859-1");
								$acronym = htmlentities($row['acronym'], null, "ISO-8859-1");
								$emails = array();
								$result2 = $db->query("SELECT * FROM `email` WHERE `user`='$userID' ORDER BY `confirmed` DESC, `primary` DESC");
								while ($row2 = mysql_fetch_array($result2)) {
									$email = htmlentities($row2['email'], null, "ISO-8859-1");
									$confirmed = $row2['confirmed'];
									$primary = $row2['primary'];
									array_push($emails, array('email'=>$email, 'confirmed'=>$confirmed, 'primary'=>$primary));
								}
								$name = htmlentities($row['name'], null, "ISO-8859-1");
								$regdate = $row['regdate'];
								$updateNickname = true;
								$updateAcronym = true;
								$samePasswords = true;
								$rightPassword = true;
								$safePassword = true;
								if (isset($_POST['change'])||isset($_POST['passwordChange'])) {
									if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
										if (isset($_POST['change'])) {
											$updateNickname = $user->updateNickname($userID, $_POST['nickname']);
											if ($updateNickname) {
												$nickname = htmlentities($_POST['nickname'], null, "ISO-8859-1");
											}
											$user->updatePrename($userID, $_POST['prename']);
											$prename = htmlentities($_POST['prename'], null, "ISO-8859-1");
											$user->updateName($userID, $_POST['name']);
											$name = htmlentities($_POST['name'], null, "ISO-8859-1");

											if ($isMaster) {
												$updateAcronym = $user->updateAcronym($userID, $_POST['acronym']);
												if ($updateAcronym) {
													$acronym = htmlentities($_POST['acronym'], null, "ISO-8859-1");
												}
												$user->updateRole($userID, $_POST['role']);
												$userRole = htmlentities($_POST['role'], null, "ISO-8859-1");
											}
										}
										if (isset($_POST['passwordChange'])) {
											if ($userID==$user->getID()) {
												$hash = $user->hashPassword($regdate, $_POST['oldPassword']);
												$proofPass = $user->getPassbyID($user->getID());
												if ($hash == $proofPass) {
													if ($_POST['newPassword']==$_POST['proofPassword']) {
														$safePassword = $user->setPassword($user->getID(), $_POST['newPassword']);
													}
													else {
														$samePasswords = false;
													}
												}
												else {
													$rightPassword = false;
												}
											}
										}
									}
								}
								$roles = array();
								foreach ($possibleRoles as $possibleRole) {
									if ($possibleRole!=$ownRole) {
										array_push($roles, array('role'=>$possibleRole, 'name'=>$role->getNamebyID($possibleRole)));
									}
								}
								$authTime = time();
								$authToken = $auth->getToken($authTime);
								require_once("template/userdata.edit.tpl.php");
							}
						}
					}
				}
			}
		}
	}
	
	public function display() {
		$db = new DB();
		$user = new User();
		$userID = $user->getID();
		$basic = new Basic();
		
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		
		$auth = new Authentication();
		$role = new Role();
		$samePasswords = true;
		$rightPassword = true;
		$passwordChange = false;
		
		if ($auth->locationReadAllowed($location, $role->getRole())&&$auth->moduleReadAllowed("userdata", $role->getRole())&&$auth->moduleWriteAllowed("userdata", $role->getRole())) {
			
			$mailer = new Mailer();
			if (isset($_POST['entermail'])) {
				if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$email = mysql_real_escape_string($_POST['email']);
					if ($basic->checkMail($email)) {
						$curTime = time();
						$confirmID = $basic->confirmID();
						$db->query("INSERT INTO `email`(`email`,`user`, `confirmed`, `time`, `confirm_id`) VALUES('$email', '$userID', '0', '$curTime', '$confirmID')");
						$mailer->sendConfirmationMail($userID, $email);
					}
				}
			}
			
			if (isset($_GET['delmail'])) {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$email = mysql_real_escape_string(urldecode($_GET['delmail']));
					$db->query("DELETE FROM `email` WHERE `user`='$userID' AND `primary`='0' AND `email`='$email'");
				}
			}
			if (isset($_GET['primemail'])) {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$email = mysql_real_escape_string(urldecode($_GET['primemail']));
					if (!$db->isExisting("SELECT `email` FROM `email` WHERE `email`='$email' AND `user`='$userID' AND `confirmed`='0'")) {
						$db->query("UPDATE `email` SET `primary`='0' WHERE `user`='$userID'");
						$db->query("UPDATE `email` SET `primary`='1' WHERE `user`='$userID' AND `email`='$email'");
					}
				}
			}
			if (isset($_GET['confmail'])) {
				if ($auth->checkToken($_GET['time'], $_GET['token'])) {
					$email = mysql_real_escape_string(urldecode($_GET['confmail']));
					$mailer->sendConfirmationMail($userID, $email);
				}
			}
			
			
			if (isset($_POST['action'])) {
				if (($userID == $_POST['userID'])&&($auth->checkToken($_POST['authTime'], $_POST['authToken']))) {
					
					if ($_POST['action']=="password") {
						$passwordChange = true;
						$regdate = $user->getRegisterDate($userID);
						$hash = $user->hashPassword($regdate, $_POST['oldPassword']);
						$proofPass = $user->getPassbyID($user->getID());
						if ($hash == $proofPass) {
							if ($_POST['newPassword']==$_POST['proofPassword']) {
								$safePassword = $user->setPassword($user->getID(), $_POST['newPassword']);
							}
							else {
								$samePasswords = false;
							}
						}
						else {
							$rightPassword = false;
						}
					}
					
					if ($_POST['action']=="edit") {
						$prename = mysql_real_escape_string($_POST['prename']);
						$name = mysql_real_escape_string($_POST['name']);
						$info = mysql_real_escape_string($basic->cleanStrict($_POST['info']));
						$signature = mysql_real_escape_string($basic->cleanStrict($_POST['signature']));
						$birthdate = 0;
						if (checkdate($_POST['month'], $_POST['day'], $_POST['year'])) {
							$birthdate = mktime(0,0,0,$_POST['month'],$_POST['day'],$_POST['year']);
						}
						$gender = "";
						if ($_POST['gender']=="female") {
							$gender = "female";
						}
						if ($_POST['gender']=="male") {
							$gender = "male";
						}
						$interests = mysql_real_escape_string($_POST['interests']);
						$job = mysql_real_escape_string($_POST['job']);
						$zip = mysql_real_escape_string($_POST['zip']);
						$street = mysql_real_escape_string($_POST['street']);
						$house = mysql_real_escape_string($_POST['house']);
						$city = mysql_real_escape_string($_POST['city']);
						$db->query("UPDATE `user` SET `prename`='$prename', `name`='$name', `info`='$info', `signature`='$signature', `birthdate`='$birthdate', `gender`='$gender', `interests`='$interests', `job`='$job', `zip`='$zip', `street`='$street', `house`='$house', `city`='$city' WHERE `user`='$userID'");
					}
				}
			}
			
			$authTime = time();
			$authToken = $auth->getToken($authTime);
			$nickname = "";
			$prename = "";
			$name = "";
			$info = "";
			$signature = "";
			$day = "DD";
			$month = "MM";
			$year = "YYYY";
			$gender = "";
			$interests = "";
			$job = "";
			$zip = "";
			$street = "";
			$house = "";
			$city = "";
			
			$result = $db->query("SELECT * FROM `user` WHERE `user`='$userID'");
			while ($row = mysql_fetch_array($result)) {
				
				$userID = $row['user'];
				$prename = htmlentities($row['prename'], null, "ISO-8859-1");
				$name = htmlentities($row['name'], null, "ISO-8859-1");
				$info = $row['info'];
				$signature = $row['signature'];
				$day = date("d", $row['birthdate']);
				$month = date("m", $row['birthdate']);
				$year = date("Y", $row['birthdate']);
				$gender = $row['gender'];
				$interests = htmlentities($row['interests'], null, "ISO-8859-1");
				$job = htmlentities($row['job'], null, "ISO-8859-1");
				$zip = htmlentities($row['zip'], null, "ISO-8859-1");
				$street = htmlentities($row['street'], null, "ISO-8859-1");
				$house = htmlentities($row['house'], null, "ISO-8859-1");
				$city = htmlentities($row['city'], null, "ISO-8859-1");
			
			}
			
			$emails = array();
			
			$result = $db->query("SELECT * FROM `email` WHERE `user` = '$userID' ORDER BY `confirmed` DESC, `primary` DESC");
			while ($row = mysql_fetch_array($result)) {
				
				$email = htmlentities($row['email'], null, "ISO-8859-1");
				$confirmed = $row['confirmed'];
				$primary = $row['primary'];
				array_push($emails, array('email'=>$email, 'confirmed'=>$confirmed, 'primary'=>$primary));
			}
			
			require_once("template/userdata.tpl.php");
		}
		
	}
	
	/*
	 * Interface method stub.
	*/
	public function isSearchable() {
		return false;
	}
	
	/*
	 * Interface method stub.
	*/
	public function getSearchList() {
		return array();
	}
	
	/*
	 * Interface method stub.
	*/
	public function search($query, $type) {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function isTaggable() {
		return false;
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagList() {
		return null;
	}
	
	/*
	 * Interface method stub.
	*/
	public function addTags($tagString, $type, $news) {
	}
	
	/*
	 * Interface method stub.
	*/
	public function getTagString($type, $news) {
	}
	
	public function getTags($type, $news) {
		return null;
	}
	
	public function displayTag($tagID, $type) {
	}
}
?>