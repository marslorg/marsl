<?php

namespace marsl\Infrastructure\HttpRequest\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\ValueObjects\DataKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

interface IHttpFireAndForgetClient
{
    /**
     * Sends a POST request to the specified URL with the given parameters and data.
     *
     * @param Url $url The URL to which the request is sent.
     * @param array<string, ParamValue> $params The parameters to include in the request.
     * @param array<string, DataValue> $data The data to include in the request body.
     * @return bool Returns true if the request was sent successfully, false otherwise.
     */
    public function postRequest(Url $url, array $params, array $data): bool;
}
