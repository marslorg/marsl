<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
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
						$userid = htmlentities($row['user']);
						$nickname = htmlentities($row['nickname']);
						$prename = htmlentities($row['prename']);
						$acronym = htmlentities($row['acronym']);
						$regdate = date("d\. M Y\; H\:i\:s", $row['regdate']);
						$email = htmlentities($row['email']);
						$postcount = htmlentities($row['postcount']);
						$name = htmlentities($row['username']);
						$rolename = htmlentities($row['rolename']);
						$roleid = htmlentities($row['roleid']);
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
						$result = $db->query("SELECT `user`, `regdate`, `role`, `nickname`, `prename`, `acronym`, `email`, `name` FROM `user` LEFT OUTER JOIN `email` USING(`user`) WHERE `user`='$userID'");
						while ($row = mysql_fetch_array($result)) {
							$userRole = htmlentities($row['role']);
							$isMaster = $role->isMaster($ownRole, $userRole, $possibleRoles);
							if ($isMaster||($user->getID()==$userID)) {
								$userID = htmlentities($row['user']);
								$nickname = htmlentities($row['nickname']);
								$prename = htmlentities($row['prename']);
								$acronym = htmlentities($row['acronym']);
								$email = htmlentities($row['email']);
								$name = htmlentities($row['name']);
								$regdate = $row['regdate'];
								$updateNickname = true;
								$updateMail = true;
								$updateAcronym = true;
								$samePasswords = true;
								$rightPassword = true;
								$safePassword = true;
								if (isset($_POST['change'])||isset($_POST['passwordChange'])) {
									if ($auth->checkToken($_POST['authTime'], $_POST['authToken'])) {
										if (isset($_POST['change'])) {
											$updateNickname = $user->updateNickname($userID, $_POST['nickname']);
											if ($updateNickname) {
												$nickname = htmlentities($_POST['nickname']);
											}
											$user->updatePrename($userID, $_POST['prename']);
											$prename = htmlentities($_POST['prename']);
											$user->updateName($userID, $_POST['name']);
											$name = htmlentities($_POST['name']);
											$updateMail = $user->updateMail($userID, $_POST['email']);
											if ($updateMail) {
												$email = mysql_real_escape_string($_POST['email']);
												$db->query("UPDATE `email` SET `confirmed`='1' WHERE `email`='$email'");
												$email = htmlentities($_POST['email']);
											}
											if ($isMaster) {
												$updateAcronym = $user->updateAcronym($userID, $_POST['acronym']);
												if ($updateAcronym) {
													$acronym = htmlentities($_POST['acronym']);
												}
												$user->updateRole($userID, $_POST['role']);
												$userRole = htmlentities($_POST['role']);
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
}
?>