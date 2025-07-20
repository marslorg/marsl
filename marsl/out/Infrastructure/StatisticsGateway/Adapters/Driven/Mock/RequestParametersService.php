<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Mock;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IParametersParser;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IRequestParametersService;

class RequestParametersService implements IRequestParametersService
{
    public function fromServer(): IParametersParser
    {
        return new ParametersParser();
    }

    public function fromGet(): IParametersParser
    {
        return new ParametersParser();
    }
}
