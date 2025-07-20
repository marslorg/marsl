<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\ValueObjects\DataKey;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\DataValue;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ParamKey;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ParamValue;

interface IHttpPostRequest
{
    public function addParam(ParamKey $paramKey, ParamValue $paramValue): void;

    public function addData(DataKey $dataKey, DataValue $dataValue): void;

    public function fireAndForget(): void;
}
