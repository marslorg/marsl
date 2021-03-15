<html>
<head>
<title>Installation - Schritt 2</title>
</head>
<body>
<?php 
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../user/user.php");
include_once(dirname(__FILE__)."/../user/role.php");
include_once(dirname(__FILE__)."/../user/auth.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

class Root {

	private $db;
	
	public function __construct() {
		$config = new Configuration();
		date_default_timezone_set($config->getTimezone());
		$this->db = new DB();
		$this->db->connect();
	}
	
	public function makeRoot() {
		if (isset($_POST['action'])) {
			if ($_POST['action'] == "send") {
				if (!empty($_POST['password'])) {
					if ($_POST['password']!=$_POST['proof']) {
						echo "Die Passwörter stimmen nicht überein.<br><br>";
					}
					else {
						$role = new Role($this->db);
						$auth = new Authentication($this->db, $role);
						$basic = new Basic($this->db, $auth, $role);
						if ($basic->checkMail($_POST['email'])) {
							$user = new User($this->db, $role);
							$user->register("root", $_POST['password'], $_POST['email'], $auth);
							$userID = $user->getIDbyName("root");
							$roleID = $role->getIDbyName("root");
							$user->changeRole($userID, $roleID);
							$email = $this->db->escapeString($_POST['email']);
							$this->db->query("UPDATE `email` SET `confirmed`='1' WHERE `email`='$email'");
						}
						else {
							echo "Die E-Mail-Adresse ist nicht gültig.<br><br>";
						}
					}
				}
			}
		}
	}
	
	public function closeDB() {
		$this->db->close();
	}
}

$root = new Root();
$root->makeRoot();
$root->closeDB();
?>
Bitte geben Sie Passwort und E-Mail-Adresse des Root-Benutzers ein.<br><br>

	<form method="post" action="root.php">
	<fieldset>
	<legend>
	Root-Benutzer anlegen
	</legend>
		<table>
		<tr>
		<td>Passwort</td><td><input type="password" name="password" /></td>
		</tr>
		<tr>
		<td>Passwort wiederholen</td><td><input type="password" name="proof" /></td>
		</tr>
		<tr>
		<td>E-Mail</td><td><input type="text" name="email" /></td>
		<tr>
		<td colspan="2"><button type="submit" name="action" value="send"> Absenden </button></td>
		</tr>
		</table>
	</fieldset>
	</form>

</body>
</html>