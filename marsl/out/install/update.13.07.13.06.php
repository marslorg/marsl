<?php

namespace marsl\install;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Configuration;
use marsl\includes\DB;

class Install
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function startInstall(): void
    {
        $config = new Configuration();
        date_default_timezone_set($config->getTimezone());
        $content = file_get_contents("update.13.07.13.06.sql");

        if (!$content) {
            return;
        }

        $statement = strtok($content, ";");

        while ($statement) {
            $this->db->query($statement);
            $statement = strtok(";");
        }

        $this->db->close();
    }
}

$install = ComponentBuilder::buildDependencies()->make('marsl\install\Install');

if ($install instanceof Install) {
    $install->startInstall();
}
