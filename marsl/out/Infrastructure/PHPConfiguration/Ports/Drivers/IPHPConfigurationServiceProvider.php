<?php

namespace marsl\Infrastructure\PHPConfiguration\Ports\Drivers;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

interface IPHPConfigurationServiceProvider
{
    public function getConfigurationEntryAsString(string $name, ?string $default = null): string;
}
