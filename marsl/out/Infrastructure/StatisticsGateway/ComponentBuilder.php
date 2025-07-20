<?php

namespace marsl\Infrastructure\StatisticsGateway;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../vendor/autoload.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector->alias(
            '\marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\IStatisticsGatewayResolver',
            '\marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\Internal\StatisticsGatewayResolver'
        );

        $injector->alias(
            '\marsl\Infrastructure\StatisticsGateway\Ports\Drivers\IStatisticsGatewayResolverProvider',
            '\marsl\Infrastructure\StatisticsGateway\Core\StatisticsGatewayResolverProvider'
        );

        $injector->alias(
            '\marsl\Infrastructure\StatisticsGateway\Ports\Driven\IConfiguration',
            '\marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation\Configuration'
        );

        $injector->alias(
            '\marsl\Infrastructure\StatisticsGateway\Ports\Driven\IRequestParametersService',
            '\marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation\RequestParametersService'
        );

        $injector->alias(
            '\marsl\Infrastructure\StatisticsGateway\Ports\Driven\IHttpRequestService',
            '\marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation\HttpRequestService'
        );

        return $injector;
    }
}
