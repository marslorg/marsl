<?php

namespace marsl\Infrastructure\HttpRequest;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../vendor/autoload.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use Auryn\Injector;

class ComponentBuilder
{
    public static function buildDependencies(Injector $injector): Injector
    {
        $injector->alias(
            '\marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpRequestService',
            '\marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\Internal\HttpRequestService'
        );

        $injector->alias(
            '\marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpRequestServiceProvider',
            '\marsl\Infrastructure\HttpRequest\Core\HttpRequestServiceProvider'
        );

        $injector->alias(
            '\marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient',
            '\marsl\Infrastructure\HttpRequest\Adapters\Driven\Implementation\HttpFireAndForgetClient'
        );

        return $injector;
    }
}
