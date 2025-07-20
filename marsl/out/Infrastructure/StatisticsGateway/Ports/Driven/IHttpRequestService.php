<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\BasePath;

interface IHttpRequestService
{
    public function constructPostRequest(ApiUrl $apiUrl, BasePath $basePath): IHttpPostRequest;
}
