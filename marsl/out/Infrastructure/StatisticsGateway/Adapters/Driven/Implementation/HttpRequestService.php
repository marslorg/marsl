<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IHttpPostRequest;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IHttpRequestService;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\BasePath;

class HttpRequestService implements IHttpRequestService
{
    private \marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpRequestService $httpRequestService;

    public function __construct(\marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpRequestService $httpRequestService)
    {
        $this->httpRequestService = $httpRequestService;
    }

    public function constructPostRequest(ApiUrl $apiUrl, BasePath $basePath): IHttpPostRequest
    {
        return new HttpPostRequest(
            $this->httpRequestService->constructPostRequest($apiUrl->getProtocol()->getValue(), $apiUrl->getHost()->getValue(), $apiUrl->getPort()->getValue(), $basePath->getValue())
        );
    }
}
