<?php

namespace marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

interface IParametersParser
{
    public function getIntegerParameter(string $name, ?int $default = null): int;

    public function getFloatParameter(string $name, ?float $default = null): float;

    public function getStringParameter(string $name, ?string $default = null): string;

    public function getBoolParameter(string $name, ?bool $default = null): bool;

    /**
     * @param array<mixed> $default
     *
     * @return array<mixed>
     */
    public function getArrayParameter(string $name, ?array $default = null): array;
}
