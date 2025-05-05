<?php

namespace marsl\install;

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
        $content = file_get_contents("update.13.02.13.01.sql");

        if (!$content) {
            return;
        }

        $statement = strtok($content, ";");

        while ($statement) {
            $this->db->query($statement);
            $statement = strtok(";");
        }

        $this->db->query("INSERT INTO `module`(`name`,`file`,`class`) VALUES('C/B/V-Verzeichnis','cbe','CBE')");

        $roleID = $this->role->getIDbyName("root");
        $this->role->setModuleRights($roleID, "cbe", true, true, true, true);
        $this->db->close();
    }
}

$install = ComponentBuilder::buildDependencies()->make('marsl\install\Install');

if ($install instanceof Install) {
    $install->startInstall();
}
