<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Mock;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IParametersParser;

class ParametersParser implements IParametersParser
{
    public function getStringParameter(string $name, ?string $default = null): string
    {
        return "";
    }
}
