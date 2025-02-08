<?php

namespace marsl\Infrastructure\PHPConfiguration\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\PHPConfiguration\Ports\Driven\IPHPConfiguration;

class PHPConfiguration implements IPHPConfiguration
{
    public function getConfigurationEntry(string $name): mixed
    {
        return ini_get($name);
    }
}
