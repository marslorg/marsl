<?php

namespace marsl\Infrastructure\RequestParameters\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use Exception;
use marsl\Infrastructure\RequestParameters\Ports\Drivers\IParametersParserProvider;

class ParametersParserProvider implements IParametersParserProvider
{
    /**
     * @var array<mixed>
     */
    private $requestParameters;

    /**
     * @param array<mixed> $requestParameters
     */
    public function __construct(array $requestParameters)
    {
        $this->requestParameters = $requestParameters;
    }

    public function getIntegerParameter(string $name, ?int $default = null): int
    {
        $parameter = $this->getParameter($name, $default);

        if ((is_string($parameter) || is_numeric($parameter)) && (string)$parameter == (string)(int)$parameter) {
            return (int)$parameter;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
    }

    public function getFloatParameter(string $name, ?float $default = null): float
    {
        $parameter = $this->getParameter($name, $default);

        if (is_float($parameter) || is_int($parameter)) {
            return (float)$parameter;
        }

        // Regex for all supported float notations in PHP (see https://www.php.net/manual/en/language.types.float.php)
        $floatRegex = "/^[-+]?((([0-9]+(_[0-9]+)*)|(([0-9]+(_[0-9]+)*)?\.([0-9]+(_[0-9]+)*))|(([0-9]+(_[0-9]+)*)\.([0-9]+(_[0-9]+)*)?))([eE][+-]?([0-9]+(_[0-9]+)*))?)$/";

        if (is_string($parameter) && preg_match($floatRegex, $parameter)) {
            // underscores would break numbers if not removed before
            return (float) str_replace('_', '', $parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
    }

    public function getStringParameter(string $name, ?string $default = null): string
    {
        $parameter = $this->getParameter($name, $default);

        if (is_string($parameter) || is_numeric($parameter)) {
            return $this->filterNullByteString((string)$parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
    }

    public function getBoolParameter(string $name, ?bool $default = null): bool
    {
        $parameter = $this->getParameter($name, $default);

        if ($parameter === false || $parameter === true) {
            return $parameter;
        }

        if ((\is_string($parameter) && \strtolower($parameter) === 'false') || $parameter === '0' || $parameter === 0) {
            return false;
        }

        if ((\is_string($parameter) && \strtolower($parameter) === 'true') || $parameter === '1' || $parameter === 1) {
            return true;
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
    }

    public function getArrayParameter(string $name, ?array $default = null): array
    {
        $parameter = $this->getParameter($name, $default);

        if (is_array($parameter)) {
            return $this->filterNullByteArray($parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
    }

    private function getParameter(string $name, mixed $default = null): mixed
    {
        if (!strlen($name)) {
            throw new Exception();
        }

        if (
            array_key_exists($name, $this->requestParameters)
            && $this->requestParameters[$name] !== null
        ) {
            return $this->filterNullBytes($this->requestParameters[$name]);
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
    }

    /**
     * @param array<mixed> $value
     *
     * @return array<mixed>
     */
    private function filterNullByteArray(array $value): array
    {
        $result = $this->filterNullBytes($value);

        if (!is_array($result)) {
            throw new Exception();
        }

        return $result;
    }

    private function filterNullByteString(string $value): string
    {
        $result = $this->filterNullBytes($value);

        if (!is_string($result)) {
            throw new Exception();
        }

        return $result;
    }

    private function filterNullBytes(mixed $value): mixed
    {
        if (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayValue) {
                $result[$key] = $this->filterNullBytes($arrayValue);
            }
            return $result;
        } else {
            return is_string($value) ? $this->sanitizeNullBytes($value) : $value;
        }
    }

    private function sanitizeNullBytes(string $value): string
    {
        return str_replace(array("\0"), '', $value);
    }
}
