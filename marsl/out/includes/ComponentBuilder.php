<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../vendor/autoload.php");
include_once(dirname(__FILE__)."/../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector->share('\marsl\includes\DB');
        $db = $injector->make('\marsl\includes\DB');
        if ($db instanceof DB) {
            $db->connect();
        }

        return $injector;
    }
}
