<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Driven\Mock;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient;
use marsl\Infrastructure\HttpRequest\ValueObjects\Port;
use marsl\Infrastructure\HttpRequest\ValueObjects\Request;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpFireAndForgetClient implements IHttpFireAndForgetClient
{
    public function sendRequest(Url $url, Request $request): bool
    {
        return true;
    }
}
