<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IHttpPostRequest;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\DataKey;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\DataValue;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ParamKey;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ParamValue;

class HttpPostRequest implements IHttpPostRequest
{
    private \marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpPostRequest $httpPostRequest;

    public function __construct(\marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpPostRequest $httpPostRequest)
    {
        $this->httpPostRequest = $httpPostRequest;
    }

    public function addParam(ParamKey $paramKey, ParamValue $paramValue): void
    {
        $this->httpPostRequest->addParam($paramKey->getValue(), $paramValue->getValue());
    }

    public function addData(DataKey $dataKey, DataValue $dataValue): void
    {
        $this->httpPostRequest->addData($dataKey->getValue(), $dataValue->getValue());
    }

    public function fireAndForget(): void
    {
        $this->httpPostRequest->fireAndForget();
    }
}
