<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Install {
	public function startInstall() {
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$db = new DB();
		$db->connect();
		$content = file_get_contents("update.20.05.16.07.sql");
		$statement = strtok($content, ";");
		while ($statement) {
			$db->query($statement);
            $statement = strtok(";");	
            echo $statement."<br />";
		}
		$db->close();
	}
}

$install = new Install();
$install->startInstall();
?>