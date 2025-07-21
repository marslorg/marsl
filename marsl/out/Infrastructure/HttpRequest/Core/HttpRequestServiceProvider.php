<?php

namespace marsl\Infrastructure\HttpRequest\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient;
use marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpPostRequestProvider;
use marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpRequestServiceProvider;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpRequestServiceProvider implements IHttpRequestServiceProvider
{
    private IHttpFireAndForgetClient $httpFireAndForgetClient;

    public function __construct(IHttpFireAndForgetClient $httpFireAndForgetClient)
    {
        $this->httpFireAndForgetClient = $httpFireAndForgetClient;
    }

    public function constructPostRequestProvider(Url $url): IHttpPostRequestProvider
    {
        return new HttpPostRequestProvider($this->httpFireAndForgetClient, $url);
    }
}
