<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/vendor/autoload.php");
include_once(dirname(__FILE__)."/autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    private static Injector $injector;

    private function __construct()
    {
    }

    public static function buildDependencies(): Injector
    {
        if (!isset(ComponentBuilder::$injector)) {
            ComponentBuilder::$injector = new Injector();
            ComponentBuilder::$injector = admin\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
            ComponentBuilder::$injector = ajax\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
            ComponentBuilder::$injector = api\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
            ComponentBuilder::$injector = includes\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
            ComponentBuilder::$injector = Infrastructure\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
            ComponentBuilder::$injector = modules\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
            ComponentBuilder::$injector = user\ComponentBuilder::buildDependencies(ComponentBuilder::$injector);
        }

        return ComponentBuilder::$injector;
    }
}
