<?php

namespace marsl\install;

?>

<html>
<head>
<title>Installation - Schritt 1</title>
</head>
<body>
<font size="1">
<?php
include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\user\Role;

class Install
{
    private DB $db;
    private Role $role;

    public function __construct(
        DB $db,
        Role $role
    ) {
        $this->db = $db;
        $this->role = $role;
    }

    public function startInstall(): void
    {
        $config = new Configuration();
        date_default_timezone_set($config->getTimezone());
        $content = file_get_contents("database.sql");

        if (!$content) {
            return;
        }

        $statement = strtok($content, ";");
        while ($statement) {
            $this->db->query($statement);
            echo $statement."<br>";
            $statement = strtok(";");

        }

        $content = file_get_contents("modules.sql");

        if (!$content) {
            return;
        }

        $statement = strtok($content, ";");

        if (!$statement) {
            return;
        }

        while (!empty(trim($statement))) {
            $this->db->query($statement);
            echo $statement."<br>";
            $statement = strtok(";");

            if (!$statement) {
                break;
            }
        }

        $this->role->createRole("root");

        $roleID = $this->role->getIDbyName("root");

        $result = $this->db->query("SELECT `file` FROM `module`");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['file'])) {
                $module = $this->db->escapeString($row['file']);
                $this->role->setModuleRights($roleID, $module, true, true, true, true);
            }
        }

        $this->db->close();

    }
}

$install = ComponentBuilder::buildDependencies()->make('marsl\install\Install');

if ($install instanceof Install) {
    $install->startInstall();
}

?>
</font>
<br>
<a href="root.php">Nächster Schritt</a>
</body>
</html>