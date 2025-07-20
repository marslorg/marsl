<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

interface IParametersParser
{
    public function getStringParameter(string $name, ?string $default = null): string;
}
