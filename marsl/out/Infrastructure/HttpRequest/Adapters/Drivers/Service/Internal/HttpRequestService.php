<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpPostRequest;
use marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpRequestService;
use marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpRequestServiceProvider;
use marsl\Infrastructure\HttpRequest\ValueObjects\BasePath;
use marsl\Infrastructure\HttpRequest\ValueObjects\Host;
use marsl\Infrastructure\HttpRequest\ValueObjects\Port;
use marsl\Infrastructure\HttpRequest\ValueObjects\Protocol;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpRequestService implements IHttpRequestService
{
    private IHttpRequestServiceProvider $httpRequestServiceProvider;

    public function __construct(
        IHttpRequestServiceProvider $httpRequestServiceProvider
    ) {
        $this->httpRequestServiceProvider = $httpRequestServiceProvider;
    }

    public function constructPostRequest(string $protocol, string $host, int $port, string $basePath): IHttpPostRequest
    {
        return new HttpPostRequest(
            $this->httpRequestServiceProvider->constructPostRequestProvider(
                new Url(
                    new Host($host),
                    new Port($port),
                    new Protocol($protocol)
                ),
                new BasePath($basePath)
            )
        );
    }
}
