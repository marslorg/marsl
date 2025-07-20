<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

interface IHttpRequestService
{
    public function constructPostRequest(string $protocol, string $host, int $port, string $basePath): IHttpPostRequest;
}
