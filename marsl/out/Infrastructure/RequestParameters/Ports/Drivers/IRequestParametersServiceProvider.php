<?php

namespace marsl\Infrastructure\RequestParameters\Ports\Drivers;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

interface IRequestParametersServiceProvider
{
    public function fromGet(): IParametersParserProvider;

    public function fromPost(): IParametersParserProvider;

    public function fromRequest(): IParametersParserProvider;

    public function fromFiles(): IParametersParserProvider;

    public function fromCookie(): IParametersParserProvider;

    public function fromServer(): IParametersParserProvider;
}
