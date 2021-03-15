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

	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}	
	
	/*
	 * Displays the user administration.
	 */
	public function admin() {
		$user = new User($this->db, $this->role);
		$mailer = new Mailer($this->db, $this->role);
		$basic = new Basic($this->db, $this->auth, $this->role);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		if ($this->auth->moduleAdminAllowed("userdata", $this->role->getRole())||$this->auth->moduleExtendedAllowed("userdata", $this->role->getRole())) {
			if ($this->auth->moduleAdminAllowed("userdata", $this->role->getRole())) {
				require_once("template/userdata.alphabet.tpl.php");
			}
			if (isset($_GET['action'])) {
				if (($_GET['action']=="list")&&$this->auth->moduleAdminAllowed("userdata", $this->role->getRole())) {
					$userdata = array();
					$search = $this->db->escapeString($_GET['search']);
					$ownRole = $this->role->getRole();
					$possibleRoles = $this->role->getPossibleRoles($ownRole);
					$result = $this->db->query("SELECT `user`, `user`.`role` AS `roleid`, `nickname`, `prename`, `acronym`, `regdate`, `email`, `postcount`, `user`.`name` AS `username`, `role`.`name` AS `rolename` FROM `user` JOIN `role` USING(`role`) LEFT OUTER JOIN `email` USING(`user`) WHERE `nickname` LIKE '$search%' ORDER BY `nickname`");
					while ($row = $this->db->fetchArray($result)) {
						$userid = htmlentities($row['user'], null, "UTF-8");
						$nickname = htmlentities($row['nickname'], null, "UTF-8");
						$prename = htmlentities($row['prename'], null, "UTF-8");
						$acronym = htmlentities($row['acronym'], null, "UTF-8");
						$dateTime->setTimestamp($row['regdate']);
						$regdate = $dateTime->format("d\. M Y\; H\:i\:s");
						$email = htmlentities($row['email'], null, "UTF-8");
						$postcount = htmlentities($row['postcount'], null, "UTF-8");
						$name = htmlentities($row['username'], null, "UTF-8");
						$rolename = htmlentities($row['rolename'], null, "UTF-8");
						$roleid = htmlentities($row['roleid'], null, "UTF-8");
						$isMaster = $this->role->isMaster($ownRole, $roleid, $possibleRoles);
						if ($user->getID()==$userid) {
							$isMaster = true;
						}
						array_push($userdata, array('user'=>$userid, 'nickname'=>$nickname, 'prename'=>$prename, 'acronym'=>$acronym, 'regdate'=>$regdate, 'email'=>$email, 'postcount'=>$postcount, 'name'=>$name, 'rolename'=>$rolename, 'isMaster'=>$isMaster));
					}
					require_once("template/userdata.list.tpl.php");
				}
				if ($_GET['action']=="details") {
					if ($this->auth->moduleAdminAllowed("userdata", $this->role->getRole())||($this->auth->moduleExtendedAllowed("userdata", $this->role->getRole())&&($_GET['user']==$user->getID()))) {
						$userID = $this->db->escapeString($_GET['user']);
						$ownID = $user->getID();
						$ownRole = $this->role->getRole();
						$possibleRoles = $this->role->getPossibleRoles($ownRole);
						
						if (isset($_POST['entermail'])) {
							if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
								$email = $this->db->escapeString($_POST['email']);
								if ($basic->checkMail($email)) {
									$curTime = time();
									$confirmID = $basic->confirmID();
									$this->db->query("INSERT INTO `email`(`email`,`user`, `confirmed`, `time`, `confirm_id`) VALUES('$email', '$userID', '1', '$curTime', '$confirmID')");
								}
							}
						}
						
						if (isset($_GET['delmail'])) {
							if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
								$email = $this->db->escapeString(urldecode($_GET['delmail']));
								$this->db->query("DELETE FROM `email` WHERE `user`='$userID' AND `primary`='0' AND `email`='$email'");
							}
						}
						if (isset($_GET['primemail'])) {
							if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
								$email = $this->db->escapeString(urldecode($_GET['primemail']));
								if (!$this->db->isExisting("SELECT `email` FROM `email` WHERE `email`='$email' AND `user`='$userID' AND `confirmed`='0' LIMIT 1")) {
									$this->db->query("UPDATE `email` SET `primary`='0' WHERE `user`='$userID'");
									$this->db->query("UPDATE `email` SET `primary`='1' WHERE `user`='$userID' AND `email`='$email'");
								}
							}
						}
						if (isset($_GET['confmail'])) {
							if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
								$email = $this->db->escapeString(urldecode($_GET['confmail']));
								$mailer->sendConfirmationMail($userID, $email);
							}
						}
							
						$result = $this->db->query("SELECT `user`, `regdate`, `role`, `nickname`, `prename`, `acronym`, `name` FROM `user` WHERE `user`='$userID'");
						while ($row = $this->db->fetchArray($result)) {
							$userRole = htmlentities($row['role'], null, "UTF-8");
							$isMaster = $this->role->isMaster($ownRole, $userRole, $possibleRoles);
							if ($isMaster||($user->getID()==$userID)) {
								$userID = htmlentities($row['user'], null, "UTF-8");
								$nickname = htmlentities($row['nickname'], null, "UTF-8");
								$prename = htmlentities($row['prename'], null, "UTF-8");
								$acronym = htmlentities($row['acronym'], null, "UTF-8");
								$emails = array();
								$result2 = $this->db->query("SELECT * FROM `email` WHERE `user`='$userID' ORDER BY `confirmed` DESC, `primary` DESC");
								while ($row2 = $this->db->fetchArray($result2)) {
									$email = htmlentities($row2['email'], null, "UTF-8");
									$confirmed = $row2['confirmed'];
									$primary = $row2['primary'];
									array_push($emails, array('email'=>$email, 'confirmed'=>$confirmed, 'primary'=>$primary));
								}
								$name = htmlentities($row['name'], null, "UTF-8");
								$regdate = $row['regdate'];
								$updateNickname = true;
								$updateAcronym = true;
								$samePasswords = true;
								$rightPassword = true;
								$safePassword = true;
								if (isset($_POST['change'])||isset($_POST['passwordChange'])) {
									if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
										if (isset($_POST['change'])) {
											$updateNickname = $user->updateNickname($userID, $_POST['nickname']);
											if ($updateNickname) {
												$nickname = htmlentities($_POST['nickname'], null, "UTF-8");
											}
											$user->updatePrename($userID, $_POST['prename']);
											$prename = htmlentities($_POST['prename'], null, "UTF-8");
											$user->updateName($userID, $_POST['name']);
											$name = htmlentities($_POST['name'], null, "UTF-8");

											if ($isMaster) {
												$updateAcronym = $user->updateAcronym($userID, $_POST['acronym']);
												if ($updateAcronym) {
													$acronym = htmlentities($_POST['acronym'], null, "UTF-8");
												}
												$user->updateRole($userID, $_POST['role']);
												$userRole = htmlentities($_POST['role'], null, "UTF-8");
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
										array_push($roles, array('role'=>$possibleRole, 'name'=>$this->role->getNamebyID($possibleRole)));
									}
								}
								$authTime = time();
								$authToken = $this->auth->getToken($authTime);
								require_once("template/userdata.edit.tpl.php");
							}
						}
					}
				}
			}
		}
	}
	
	public function display() {
		$user = new User($this->db, $this->role);
		$userID = $user->getID();
		$basic = new Basic($this->db, $this->auth, $this->role);
		$config = new Configuration();
		$dateTime = new DateTime("now", new DateTimeZone($config->getTimezone()));
		
		$location = "";
		if (isset($_GET['id'])) {
			$location = $_GET['id'];
		}
		else {
			$location = $basic->getHomeLocation();
		}
		
		$samePasswords = true;
		$rightPassword = true;
		$passwordChange = false;
		
		if ($this->auth->locationReadAllowed($location, $this->role->getRole())&&$this->auth->moduleReadAllowed("userdata", $this->role->getRole())&&$this->auth->moduleWriteAllowed("userdata", $this->role->getRole())) {
			
			$mailer = new Mailer($this->db, $this->role);
			if (isset($_POST['entermail'])) {
				if ($this->auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
					$email = $this->db->escapeString($_POST['email']);
					if ($basic->checkMail($email)) {
						$curTime = time();
						$confirmID = $basic->confirmID();
						$this->db->query("INSERT INTO `email`(`email`,`user`, `confirmed`, `time`, `confirm_id`) VALUES('$email', '$userID', '0', '$curTime', '$confirmID')");
						$mailer->sendConfirmationMail($userID, $email);
					}
				}
			}
			
			if (isset($_GET['delmail'])) {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$email = $this->db->escapeString(urldecode($_GET['delmail']));
					$this->db->query("DELETE FROM `email` WHERE `user`='$userID' AND `primary`='0' AND `email`='$email'");
				}
			}
			if (isset($_GET['primemail'])) {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$email = $this->db->escapeString(urldecode($_GET['primemail']));
					if (!$this->db->isExisting("SELECT `email` FROM `email` WHERE `email`='$email' AND `user`='$userID' AND `confirmed`='0' LIMIT 1")) {
						$this->db->query("UPDATE `email` SET `primary`='0' WHERE `user`='$userID'");
						$this->db->query("UPDATE `email` SET `primary`='1' WHERE `user`='$userID' AND `email`='$email'");
					}
				}
			}
			if (isset($_GET['confmail'])) {
				if ($this->auth->checkToken($_GET['time'], $_GET['token'])) {
					$email = $this->db->escapeString(urldecode($_GET['confmail']));
					$mailer->sendConfirmationMail($userID, $email);
				}
			}
			
			
			if (isset($_POST['action'])) {
				if (($userID == $_POST['userID'])&&($this->auth->checkToken($_POST['authTime'], $_POST['authToken']))) {
					
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
						$prename = $this->db->escapeString($_POST['prename']);
						$name = $this->db->escapeString($_POST['name']);
						$info = $this->db->escapeString($basic->cleanStrict($_POST['info']));
						$signature = $this->db->escapeString($basic->cleanStrict($_POST['signature']));
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
						$interests = $this->db->escapeString($_POST['interests']);
						$job = $this->db->escapeString($_POST['job']);
						$zip = $this->db->escapeString($_POST['zip']);
						$street = $this->db->escapeString($_POST['street']);
						$house = $this->db->escapeString($_POST['house']);
						$city = $this->db->escapeString($_POST['city']);
						$this->db->query("UPDATE `user` SET `prename`='$prename', `name`='$name', `info`='$info', `signature`='$signature', `birthdate`='$birthdate', `gender`='$gender', `interests`='$interests', `job`='$job', `zip`='$zip', `street`='$street', `house`='$house', `city`='$city' WHERE `user`='$userID'");
					}
				}
			}
			
			$authTime = time();
			$authToken = $this->auth->getToken($authTime);
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
			
			$result = $this->db->query("SELECT * FROM `user` WHERE `user`='$userID'");
			while ($row = $this->db->fetchArray($result)) {
				
				$userID = $row['user'];
				$prename = htmlentities($row['prename'], null, "UTF-8");
				$name = htmlentities($row['name'], null, "UTF-8");
				$info = $row['info'];
				$signature = $row['signature'];
				$dateTime->setTimestamp($row['birthdate']);
				$day = $dateTime->format("d");
				$month = $dateTime->format("m");
				$year = $dateTime->format("Y");
				$gender = $row['gender'];
				$interests = htmlentities($row['interests'], null, "UTF-8");
				$job = htmlentities($row['job'], null, "UTF-8");
				$zip = htmlentities($row['zip'], null, "UTF-8");
				$street = htmlentities($row['street'], null, "UTF-8");
				$house = htmlentities($row['house'], null, "UTF-8");
				$city = htmlentities($row['city'], null, "UTF-8");
			
			}
			
			$emails = array();
			
			$result = $this->db->query("SELECT * FROM `email` WHERE `user` = '$userID' ORDER BY `confirmed` DESC, `primary` DESC");
			while ($row = $this->db->fetchArray($result)) {
				
				$email = htmlentities($row['email'], null, "UTF-8");
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
	
	public function getImage() {
		return null;
	}
	
	public function getTitle() {
		return null;
	}
}
?>