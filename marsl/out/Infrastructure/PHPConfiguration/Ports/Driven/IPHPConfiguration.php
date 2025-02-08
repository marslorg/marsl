<?php

namespace marsl\Infrastructure\PHPConfiguration\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

interface IPHPConfiguration
{
    public function getConfigurationEntry(string $name): mixed;
}
