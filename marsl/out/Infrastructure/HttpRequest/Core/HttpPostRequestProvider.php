<?php

namespace marsl\Infrastructure\HttpRequest\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\HttpRequest\Ports\Driven\IHttpFireAndForgetClient;
use marsl\Infrastructure\HttpRequest\Ports\Drivers\IHttpPostRequestProvider;
use marsl\Infrastructure\HttpRequest\ValueObjects\BasePath;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\DataValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamKey;
use marsl\Infrastructure\HttpRequest\ValueObjects\ParamValue;
use marsl\Infrastructure\HttpRequest\ValueObjects\Port;
use marsl\Infrastructure\HttpRequest\ValueObjects\Request;
use marsl\Infrastructure\HttpRequest\ValueObjects\Url;

class HttpPostRequestProvider implements IHttpPostRequestProvider
{
    private BasePath $basePath;
    private IHttpFireAndForgetClient $httpFireAndForgetClient;
    private Url $url;

    /**
     * @var array<string, string>
     */
    private array $params = array();

    /**
     * @var array<string, string>
     */
    private array $data = array();

    public function __construct(
        BasePath $basePath,
        IHttpFireAndForgetClient $httpFireAndForgetClient,
        Url $url
    ) {
        $this->basePath = $basePath;
        $this->httpFireAndForgetClient = $httpFireAndForgetClient;
        $this->url = $url;
    }

    public function addParam(ParamKey $paramKey, ParamValue $paramValue): void
    {
        $this->params[$paramKey->getValue()] = $paramValue->getValue();
    }

    public function addData(DataKey $dataKey, DataValue $dataValue): void
    {
        $this->data[$dataKey->getValue()] = $dataValue->getValue();
    }

    public function fireAndForget(): void
    {
        $getQuery = $this->buildQuery($this->params);
        $postQuery = $this->buildQuery($this->data);
        $request = 'POST'.' /'.$this->basePath->getValue().'?'.$getQuery." HTTP/1.1\r\n";
        $request .= 'Host: '.$this->url->getHost()->getValue()."\r\n";
        $request .= 'Content-Type: application/x-www-form-urlencoded'."\r\n";
        $request .= 'Content-Length: '.strlen($postQuery)."\r\n";
        $request .= 'Connection: close'."\r\n";
        $request .= "\r\n".$postQuery;

        $this->httpFireAndForgetClient->sendRequest(
            $this->url,
            new Request($request)
        );
    }

    /**
     * Builds the query string from the data array.
     *
     * @param array<string, string> $data
     *
     * @return string
     */
    private function buildQuery(array $data): string
    {
        return http_build_query($data);
    }
}
