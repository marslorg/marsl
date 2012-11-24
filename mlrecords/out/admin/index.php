<?php
include_once (dirname(__FILE__)."/../includes/errorHandler.php");
include_once (dirname(__FILE__)."/../includes/dbsocket.php");
include_once (dirname(__FILE__)."/../user/user.php");
include_once (dirname(__FILE__)."/../includes/basic.php");
include_once (dirname(__FILE__)."/../user/auth.php");
include_once (dirname(__FILE__)."/../modules/urlloader.php");
include_once (dirname(__FILE__)."/roleadmin.php");
include_once (dirname(__FILE__)."/standard.php");
include_once (dirname(__FILE__)."/modulerights.php");
include_once (dirname(__FILE__)."/register.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/recover.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Main {
	
	private $var;
	
	/*
	 * Loader for the configuration file and the right timezone.
	*/
	public function Main() {
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$db = new DB();
		$db->connect();
	}
	
	/*
	 * Main runner for the current admin interface information.
	 * Loads the modules if necessary.
	 */
	public function admin() {
		
		$user = new User();
		$basic = new Basic();
		$urlloader = new URLLoader();
		$auth = new Authentication();
		$db = new DB();
		$role = new Role();
		$roleID = $role->getRole();
		
		$headAdmin = $user->isHead();
		
		$content = "";
		
		if ($user->isAdmin()) {
			
			if (isset($_GET['var'])) {
				$this->var = $_GET['var'];
			}
			
			if ($this->var == "logout") {
				$user->logout();
				@header("Location: index.php");
			}
			else if ($this->var == "module") {
				if ($basic->getModule($_GET['module'])!=false) {
					$array = $basic->getModule($_GET['module']);
					include_once(dirname(__FILE__)."/../modules/".$array['file'].".php");
					$content = new $array['class'];
				}
				else {
					include_once(dirname(__FILE__)."/admin.php");
					$content = new Administration();
				}
			}
			else if ($this->var == "urlloader") {
				if ($auth->moduleAdminAllowed("urlloader", $roleID)) {
					$content = new URLLoader();
				}
			}
			else if ($this->var == "standards") {
				if ($headAdmin) {
					$content = new Standard();
				}
			}
			else if ($this->var == "modulerights") {
				$content = new ModuleRights();
			}
			else if ($this->var == "role") {
				$content = new RoleAdmin();
			}
			else if ($this->var =="register") {
				$content = new Register();
			}
			else if ($this->var=="events") {
				$content = new EventManagement();
			}
			else {
				include_once(dirname(__FILE__)."/admin.php");
				$content = new Administration();
			}
		}
		
		$title = htmlentities($basic->getTitle());
		$modules = $basic->getModules();
		if ($user->isGuest()) {
			if (isset($_GET['var'])) {
				if ($_GET['var']=="forgot") {
					if (isset($_GET['action'])) {
						if ($_GET['action']=="recover") {
							$recover = new Recover();
							$recover->admin();
						}
						else {
							$init = true;
							$success = false;
							if ($_GET['action']=="success") {
								$init = false;
								$success = true;
								$topic = $_GET['topic'];
							}
							elseif ($_GET['action']=="failed") {
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
						if (isset($_GET['action'])) {
							if ($_GET['action']=="success") {
								$init = false;
								$success = true;
								$topic = $_GET['topic'];
							}
							elseif ($_GET['action']=="failed") {
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
					require_once ("template/login.tpl.php");
				}
			}
			else {
				$wrongpw = "";
				if (isset($_GET['wrongpw'])) {
					$wrongpw = $_GET['wrongpw'];
				}
				require_once ("template/login.tpl.php");
			}
		}
		
		else if ($user->isAdmin()) {
			$userdata = $auth->moduleExtendedAllowed("userdata", $role->getRole());
			$userID = $user->getID();
			require_once ("template/index.tpl.php");
		}
		$db->close();
	}
}

$main = new Main();
$main->admin();
?>