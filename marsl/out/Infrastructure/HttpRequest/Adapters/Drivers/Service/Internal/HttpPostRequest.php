<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service\IHttpPostRequest;
use marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpPostRequestProvider;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamValue;

class HttpPostRequest implements IHttpPostRequest
{
    private IHttpPostRequestProvider $httpPostRequestProvider;

    public function __construct(
        IHttpPostRequestProvider $httpPostRequestProvider
    ) {
        $this->httpPostRequestProvider = $httpPostRequestProvider;
    }

    public function addParam(string $paramKey, string $paramValue): void
    {
        $this->httpPostRequestProvider->addParam(
            new ParamKey($paramKey),
            new ParamValue($paramValue)
        );
    }

    public function addData(string $dataKey, string $dataValue): void
    {
        $this->httpPostRequestProvider->addData(
            new DataKey($dataKey),
            new DataValue($dataValue)
        );
    }

    public function fireAndForget(): void
    {
        $this->httpPostRequestProvider->fireAndForget();
    }
}
