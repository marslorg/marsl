<?php

namespace marsl\Infrastructure\PHPConfiguration;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../vendor/autoload.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector->alias(
            '\marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\IPHPConfigurationService',
            '\marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\Internal\PHPConfigurationService'
        );
        $injector->alias(
            '\marsl\Infrastructure\PHPConfiguration\Ports\Driven\IPHPConfiguration',
            '\marsl\Infrastructure\PHPConfiguration\Adapters\Driven\Implementation\PHPConfiguration'
        );
        $injector->alias(
            '\marsl\Infrastructure\PHPConfiguration\Ports\Drivers\IPHPConfigurationServiceProvider',
            '\marsl\Infrastructure\PHPConfiguration\Core\PHPConfigurationServiceProvider'
        );

        return $injector;
    }
}
