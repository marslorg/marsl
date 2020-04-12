<html>
<head>
<title>Installation - Schritt 1</title>
</head>
<body>
<font size="1">
<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Install {
	
	public function startInstall() {
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$db = new DB();
		$db->connect();
		$content = file_get_contents("database.sql");
		$statement = strtok($content, ";");
		while ($statement) {
			$db->query($statement);
			echo $statement."<br>";
			$statement = strtok(";");

		}
		
		$content = file_get_contents("modules.sql");
		$statement = strtok($content, ";");
		while ($statement) {
			$db->query($statement);
			echo $statement."<br>";
			$statement = strtok(";");
		}
		
		$role = new Role($db);
		
		$role->createRole("root");
		
		$roleID = $role->getIDbyName("root");
		
		$result = $db->query("SELECT `file` FROM `module`");
		while ($row = $db->fetchArray($result)) {
			$module = $db->escapeString($row['file']);
			$role->setModuleRights($roleID, $module, "1", "1", "1", "1");
		}
		
		$db->close();
		
	}
}
$install = new Install();
$install->startInstall();

?>
</font>
<br>
<a href="root.php">N�chster Schritt</a>
</body>
</html>