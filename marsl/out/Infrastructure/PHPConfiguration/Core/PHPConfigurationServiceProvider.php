<?php

namespace marsl\Infrastructure\PHPConfiguration\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use Exception;
use marsl\Infrastructure\PHPConfiguration\Ports\Driven\IPHPConfiguration;
use marsl\Infrastructure\PHPConfiguration\Ports\Drivers\IPHPConfigurationServiceProvider;

class PHPConfigurationServiceProvider implements IPHPConfigurationServiceProvider
{
    private IPHPConfiguration $phpConfiguration;

    public function __construct(IPHPConfiguration $phpConfiguration)
    {
        $this->phpConfiguration = $phpConfiguration;
    }

    public function getConfigurationEntryAsString(string $name, ?string $default = null): string
    {
        $parameter = $this->phpConfiguration->getConfigurationEntry($name);

        if (is_string($parameter) || is_numeric($parameter)) {
            return $this->filterNullByteString((string)$parameter);
        }

        if (null !== $default) {
            return $default;
        }

        throw new Exception();
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
