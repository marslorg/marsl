<?php

namespace marsl\Infrastructure\RequestParameters\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\RequestParameters\Ports\Driven\IParameters;
use marsl\Infrastructure\RequestParameters\Ports\Drivers\IParametersParserProvider;
use marsl\Infrastructure\RequestParameters\Ports\Drivers\IRequestParametersServiceProvider;

class RequestParametersServiceProvider implements IRequestParametersServiceProvider
{
    private IParameters $parameters;

    public function __construct(IParameters $parameters)
    {
        $this->parameters = $parameters;
    }

    public function fromGet(): IParametersParserProvider
    {
        return new ParametersParserProvider($this->parameters->getGetParameters());
    }

    public function fromPost(): IParametersParserProvider
    {
        return new ParametersParserProvider($this->parameters->getPostParameters());
    }

    public function fromRequest(): IParametersParserProvider
    {
        return new ParametersParserProvider($this->parameters->getRequestParameters());
    }

    public function fromFiles(): IParametersParserProvider
    {
        return new ParametersParserProvider($this->parameters->getFilesParameters());
    }

    public function fromCookie(): IParametersParserProvider
    {
        return new ParametersParserProvider($this->parameters->getCookieParameters());
    }

    public function fromServer(): IParametersParserProvider
    {
        return new ParametersParserProvider($this->parameters->getServerParameters());
    }
}
