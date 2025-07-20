<?php

namespace marsl\Infrastructure\HttpRequest\Ports\Drivers;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\ValueObjects\DataKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamValue;

interface IHttpPostRequestProvider
{
    public function addParam(ParamKey $paramKey, ParamValue $paramValue): void;

    public function addData(DataKey $dataKey, DataValue $dataValue): void;

    public function fireAndForget(): void;
}
