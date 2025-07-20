<?php

namespace marsl\Infrastructure;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../vendor/autoload.php");
include_once(dirname(__FILE__)."/../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    /**
     * @SuppressWarnings("staticAccess")
     */
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector = HttpRequest\ComponentBuilder::buildDependencies($injector);
        $injector = PHPConfiguration\ComponentBuilder::buildDependencies($injector);
        $injector = RequestParameters\ComponentBuilder::buildDependencies($injector);
        $injector = StatisticsGateway\ComponentBuilder::buildDependencies($injector);

        return $injector;
    }
}
