<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IParametersParser;

class ParametersParser implements IParametersParser
{
    private \marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IParametersParser $parametersParser;

    public function __construct(\marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IParametersParser $parametersParser)
    {
        $this->parametersParser = $parametersParser;
    }

    public function getStringParameter(string $name, ?string $default = null): string
    {
        return $this->parametersParser->getStringParameter($name, $default);
    }
}
