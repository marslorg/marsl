<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/basic.php");

/*
 * Password and user recovery
 */
class Recover {
	
	/*
	 * Loads the initial password/user recovery dialog or the further steps.
	 */
	public function admin() {
		if (isset($_GET['status'])&&$_GET['status']=="success") {
			$init = false;
			$success = true;
			$recover = true;
			$basic = new Basic();
			$title = htmlentities($basic->getTitle(), null, "ISO-8859-1");
			require_once("template/recover.tpl.php");
		}
		else {
			if (isset($_GET['subaction'])) {
				if ($_GET['subaction']=="set") {
					$time = $_GET['time'];
					if ($time+172800 >= time()) {
						$uid = $_GET['uid'];
						$user = new User();
						$password = $user->getPassbyID($uid);
						$auth_code = md5("admin".$uid.$time.$password);
						$auth = $_GET['auth'];
						if ($auth_code == $auth) {
							$password = $_POST['password'];
							$password2 = $_POST['password2'];
							if ($password==$password2) {
								$user->setPassword($uid, $password);
								header("Location: index.php?var=forgot&action=recover&status=success");
							}
							else {
								header("Location: index.php?var=forgot&action=recover&status=failed&uid=".$uid."&time=".$time."&auth=".$auth);
							}
						}
						else {
							header("Location: index.php?var=forgot&action=recover&uid=".$uid."&time=".$time."&auth=".$auth);
						}
					}
					else {
						header("Location: index.php?var=forgot&action=recover&uid=".$uid."&time=".$time."&auth=".$auth);
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
	
	/*
	 * Loads the box to set a new password.
	 */
	private function recoverBox() {
		$basic = new Basic();
		$title = htmlentities($basic->getTitle(), null, "ISO-8859-1");
		$time = $_GET['time'];
		$recover = false;
		$uid = "";
		$auth = "";
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
			$user = new User();
			$password = $user->getPassbyID($uid);
			$auth_code = md5("admin".$uid.$time.$password);
			$auth = $_GET['auth'];
			if ($auth_code == $auth) {
				$recover = true;
			}
		}
		require_once("template/recover.tpl.php");
	}
}
?>