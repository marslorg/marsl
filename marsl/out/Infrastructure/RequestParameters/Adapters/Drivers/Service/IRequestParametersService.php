<?php

namespace marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

interface IRequestParametersService
{
    public function fromGet(): IParametersParser;

    public function fromPost(): IParametersParser;

    public function fromRequest(): IParametersParser;

    public function fromFiles(): IParametersParser;

    public function fromCookie(): IParametersParser;

    public function fromServer(): IParametersParser;
}
