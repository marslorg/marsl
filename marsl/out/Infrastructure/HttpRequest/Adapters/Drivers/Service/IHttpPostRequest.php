<?php

namespace marsl\Infrastructure\HttpRequest\Adapters\Drivers\Service;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

interface IHttpPostRequest
{
    public function addParam(string $paramKey, string $paramValue): void;

    public function addData(string $dataKey, string $dataValue): void;

    public function fireAndForget(): void;
}
