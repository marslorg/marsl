<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../vendor/autoload.php");
include_once(dirname(__FILE__)."/../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector->share('\marsl\user\Authentication');
        $injector->share('\marsl\user\AuthenticationBase');
        $injector->share('\marsl\user\Role');
        $injector->share('\marsl\user\UserBase');

        return $injector;
    }
}
