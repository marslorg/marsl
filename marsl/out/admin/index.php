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
include_once (dirname(__FILE__)."/tags.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/recover.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Main {
	
	private $var;

	private $db;

	public function __construct() {
		$this->db = new DB();
		$this->db->connect();
	}
	
	/*
	 * Loader for the configuration file and the right timezone.
	*/
	public function Main() {
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
	}
	
	/*
	 * Main runner for the current admin interface information.
	 * Loads the modules if necessary.
	 */
	public function admin() {
		
		$user = new User($this->db);
		$auth = new Authentication($this->db);
		$basic = new Basic($this->db, $auth);
		$urlloader = new URLLoader($this->db, $auth);
		$role = new Role($this->db);
		$roleID = $role->getRole();
		
		$headAdmin = $user->isHead();
		
		$content = "";
		
		if ($user->isAdmin()) {
			
			if (isset($_GET['var'])) {
				$this->var = $_GET['var'];
			}
			
			if ($this->var == "logout") {
				$user->logout($auth);
				@header("Location: index.php");
			}
			else if ($this->var == "module") {
				if ($basic->getModule($_GET['module'])!=false) {
					$array = $basic->getModule($_GET['module']);
					include_once(dirname(__FILE__)."/../modules/".$array['file'].".php");
					$content = new $array['class']($this->db, $auth);
				}
				else {
					include_once(dirname(__FILE__)."/admin.php");
					$content = new Administration();
				}
			}
			else if ($this->var == "urlloader") {
				if ($auth->moduleAdminAllowed("urlloader", $roleID)) {
					$content = new URLLoader($this->db, $auth);
				}
			}
			else if ($this->var == "standards") {
				if ($headAdmin) {
					$content = new Standard($this->db, $auth);
				}
			}
			else if ($this->var == "modulerights") {
				$content = new ModuleRights($this->db, $auth);
			}
			else if ($this->var == "role") {
				$content = new RoleAdmin($this->db, $auth);
			}
			else if ($this->var =="register") {
				$content = new RegisterUser($this->db, $auth);
			}
			else if ($this->var=="tags") {
				$content = new Tags($this->db, $auth);
			}
			else {
				include_once(dirname(__FILE__)."/admin.php");
				$content = new Administration();
			}
		}
		
		$title = htmlentities($basic->getTitle(), null, "ISO-8859-1");
		$modules = $basic->getModules();
		if ($user->isGuest()) {
			if (isset($_GET['var'])) {
				if ($_GET['var']=="forgot") {
					if (isset($_GET['action'])) {
						if ($_GET['action']=="recover") {
							$recover = new Recover($this->db, $auth);
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
			$config = new Configuration();
			$clusterServer = $config->getClusterServer();
			require_once ("template/index.tpl.php");
		}
		$this->db->close();
	}
}

$main = new Main();
$main->admin();
?>