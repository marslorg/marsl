<?php

namespace marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IParametersParser;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\Infrastructure\RequestParameters\Ports\Drivers\IRequestParametersServiceProvider;

class RequestParametersService implements IRequestParametersService
{
    private IRequestParametersServiceProvider $requestParametersServiceProvider;

    public function __construct(IRequestParametersServiceProvider $requestParametersServiceProvider)
    {
        $this->requestParametersServiceProvider = $requestParametersServiceProvider;
    }

    public function fromGet(): IParametersParser
    {
        return new ParametersParser($this->requestParametersServiceProvider->fromGet());
    }

    public function fromPost(): IParametersParser
    {
        return new ParametersParser($this->requestParametersServiceProvider->fromPost());
    }

    public function fromRequest(): IParametersParser
    {
        return new ParametersParser($this->requestParametersServiceProvider->fromRequest());
    }

    public function fromFiles(): IParametersParser
    {
        return new ParametersParser($this->requestParametersServiceProvider->fromFiles());
    }

    public function fromCookie(): IParametersParser
    {
        return new ParametersParser($this->requestParametersServiceProvider->fromCookie());
    }

    public function fromServer(): IParametersParser
    {
        return new ParametersParser($this->requestParametersServiceProvider->fromServer());
    }
}
