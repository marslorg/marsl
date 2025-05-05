<?php

namespace marsl\Infrastructure\RequestParameters;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../vendor/autoload.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector->alias(
            '\marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService',
            '\marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\Internal\RequestParametersService'
        );

        $injector->alias(
            '\marsl\Infrastructure\RequestParameters\Ports\Driven\IParameters',
            '\marsl\Infrastructure\RequestParameters\Adapters\Driven\Implementation\Parameters'
        );
        $injector->alias(
            '\marsl\Infrastructure\RequestParameters\Ports\Drivers\IRequestParametersServiceProvider',
            '\marsl\Infrastructure\RequestParameters\Core\RequestParametersServiceProvider'
        );

        return $injector;
    }
}
