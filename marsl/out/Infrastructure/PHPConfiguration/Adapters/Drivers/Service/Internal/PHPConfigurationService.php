<?php

namespace marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\PHPConfiguration\Adapters\Drivers\Service\IPHPConfigurationService;
use marsl\Infrastructure\PHPConfiguration\Ports\Drivers\IPHPConfigurationServiceProvider;

class PHPConfigurationService implements IPHPConfigurationService
{
    private IPHPConfigurationServiceProvider $phpConfigurationServiceProvider;

    public function __construct(IPHPConfigurationServiceProvider $phpConfigurationServiceProvider)
    {
        $this->phpConfigurationServiceProvider = $phpConfigurationServiceProvider;
    }

    public function getConfigurationEntryAsString(string $name, ?string $default = null): string
    {
        return $this->phpConfigurationServiceProvider->getConfigurationEntryAsString($name, $default);
    }
}
