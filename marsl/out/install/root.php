<?php

namespace marsl\install;

?>
<html>
<head>
<title>Installation - Schritt 2</title>
</head>
<body>
<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Root
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;

        date_default_timezone_set($configuration->getTimezone());
    }

    public function makeRoot(): void
    {
        if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "send") {
            $password = $this->requestParametersService->fromPost()->getStringParameter("password", "");
            if (!empty($password)) {
                if ($password != $this->requestParametersService->fromPost()->getStringParameter("proof", "")) {
                    echo "Die Passwörter stimmen nicht überein.<br><br>";
                } else {
                    $email = $this->requestParametersService->fromPost()->getStringParameter("email", "");
                    if ($this->basic->checkMail($email)) {
                        $this->user->register("root", $password, $email, $this->authentication, false);
                        $userID = $this->user->getIDbyName("root");
                        $roleID = $this->role->getIDbyName("root");
                        $this->user->changeRole($userID, $roleID);
                        $email = $this->db->escapeString($email);
                        $this->db->query("UPDATE `email` SET `confirmed`='1' WHERE `email`='$email'");
                    } else {
                        echo "Die E-Mail-Adresse ist nicht gültig.<br><br>";
                    }
                }
            }
        }
    }

    public function closeDB(): void
    {
        $this->db->close();
    }
}

$root = ComponentBuilder::buildDependencies()->make('marsl\install\Root');

if ($root instanceof Root) {
    $root->makeRoot();
    $root->closeDB();
}
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