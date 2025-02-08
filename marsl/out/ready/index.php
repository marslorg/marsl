<?php

namespace marsl\ready;

include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\DB;

class Main
{
    private DB $db;

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function display(): void
    {
        $healthy = $this->db->isHealthy();
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../albums/health");
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../files/health");
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../news/health");
        $healthy = $healthy && file_exists(dirname(__FILE__)."/../shared/health");

        if ($healthy) {
            http_response_code(200);
            echo "HEALTHY";
        } else {
            http_response_code(500);
            echo "UNHEALTHY";
        }
    }
}

$main = ComponentBuilder::buildDependencies()->make('marsl\ready\Main');

if ($main instanceof Main) {
    $main->display();
}
