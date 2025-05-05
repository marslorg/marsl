<?php

namespace marsl\api\controllers;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../vendor/autoload.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        return $injector;
    }
}
