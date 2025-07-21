<?php

namespace marsl\Infrastructure\HttpRequest\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient;
use marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpPostRequestProvider;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\Port;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpPostRequestProvider implements IHttpPostRequestProvider
{
    private IHttpFireAndForgetClient $httpFireAndForgetClient;
    private Url $url;

    /**
     * @var array<string, ParamValue>
     */
    private array $params = array();

    /**
     * @var array<string, DataValue>
     */
    private array $data = array();

    public function __construct(
        IHttpFireAndForgetClient $httpFireAndForgetClient,
        Url $url
    ) {
        $this->httpFireAndForgetClient = $httpFireAndForgetClient;
        $this->url = $url;
    }

    public function addParam(ParamKey $paramKey, ParamValue $paramValue): void
    {
        $this->params[$paramKey->getValue()] = $paramValue;
    }

    public function addData(DataKey $dataKey, DataValue $dataValue): void
    {
        $this->data[$dataKey->getValue()] = $dataValue;
    }

    public function fireAndForget(): void
    {
        $this->httpFireAndForgetClient->postRequest(
            $this->url,
            $this->params,
            $this->data
        );
    }
}
