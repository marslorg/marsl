<?php

namespace marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

interface IPHPConfigurationService
{
    public function getConfigurationEntryAsString(string $name, ?string $default = null): string;
}
