<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");
include_once(dirname(__FILE__)."/../user/role.php");

class Install {
	public function startInstall() {
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$db = new DB();
		$db->connect();
		$content = file_get_contents("update.16.07.13.09.sql");
		$statement = strtok($content, ";");
		while ($statement) {
			$db->query($statement);
			$statement = strtok(";");		
		}	
		$db->query("INSERT INTO `newsletter_configuration`(`allow_anon_registration`) VALUES('1')");
		$db->query("INSERT INTO `module`(`name`,`file`,`class`) VALUES('Newsletter','newsletter','Newsletter')");
		
		$role = new Role();
		
		$roleID = $role->getIDbyName("root");
		$role->setModuleRights($roleID, "newsletter", "1", "1", "1", "1");
		$db->close();
	}
}

$install = new Install();
$install->startInstall();
?>