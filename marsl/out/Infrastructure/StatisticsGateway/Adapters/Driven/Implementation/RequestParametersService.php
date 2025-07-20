<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IParametersParser;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IRequestParametersService;

class RequestParametersService implements IRequestParametersService
{
    private \marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService $requestParametersService;

    public function __construct(\marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService $requestParametersService)
    {
        $this->requestParametersService = $requestParametersService;
    }

    public function fromServer(): IParametersParser
    {
        return new ParametersParser($this->requestParametersService->fromServer());
    }

    public function fromGet(): IParametersParser
    {
        return new ParametersParser($this->requestParametersService->fromGet());
    }
}
