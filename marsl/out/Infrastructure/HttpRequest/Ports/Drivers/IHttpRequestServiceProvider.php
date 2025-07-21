<?php

namespace marsl\Infrastructure\HttpRequest\Ports\Drivers;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

interface IHttpRequestServiceProvider
{
    public function constructPostRequestProvider(Url $url): IHttpPostRequestProvider;
}
