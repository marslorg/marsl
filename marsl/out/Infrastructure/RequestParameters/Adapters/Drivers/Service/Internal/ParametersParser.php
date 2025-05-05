<?php

namespace marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IParametersParser;
use marsl\Infrastructure\RequestParameters\Ports\Drivers\IParametersParserProvider;

class ParametersParser implements IParametersParser
{
    private IParametersParserProvider $parametersParserProvider;

    public function __construct(IParametersParserProvider $parametersParserProvider)
    {
        $this->parametersParserProvider = $parametersParserProvider;
    }

    public function getIntegerParameter(string $name, ?int $default = null): int
    {
        return $this->parametersParserProvider->getIntegerParameter($name, $default);
    }

    public function getFloatParameter(string $name, ?float $default = null): float
    {
        return $this->parametersParserProvider->getFloatParameter($name, $default);
    }

    public function getStringParameter(string $name, ?string $default = null): string
    {
        return $this->parametersParserProvider->getStringParameter($name, $default);
    }

    public function getBoolParameter(string $name, ?bool $default = null): bool
    {
        return $this->parametersParserProvider->getBoolParameter($name, $default);
    }

    /**
     * @param array<mixed> $default
     *
     * @return array<mixed>
     */
    public function getArrayParameter(string $name, ?array $default = null): array
    {
        return $this->parametersParserProvider->getArrayParameter($name, $default);
    }
}
