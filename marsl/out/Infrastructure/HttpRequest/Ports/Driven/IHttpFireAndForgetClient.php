<?php

namespace marsl\Infrastructure\HttpRequest\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\ValueObjects\Port;
use marsl\Infrastructure\HttpRequest\ValueObjects\Request;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

interface IHttpFireAndForgetClient
{
    public function sendRequest(Url $url, Request $request): bool;
}
