<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

interface IRequestParametersService
{
    public function fromServer(): IParametersParser;

    public function fromGet(): IParametersParser;
}
