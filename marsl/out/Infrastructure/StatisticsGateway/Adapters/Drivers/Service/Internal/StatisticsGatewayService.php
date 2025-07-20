<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\IStatisticsGatewayService;
use marsl\Infrastructure\StatisticsGateway\Ports\Drivers\IStatisticsGatewayServiceProvider;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\PageTitle;

class StatisticsGatewayService implements IStatisticsGatewayService
{
    private IStatisticsGatewayServiceProvider $statisticsGatewayServiceProvider;

    public function __construct(IStatisticsGatewayServiceProvider $statisticsGatewayServiceProvider)
    {
        $this->statisticsGatewayServiceProvider = $statisticsGatewayServiceProvider;
    }

    public function trackPageView(string $pageTitle): void
    {
        $this->statisticsGatewayServiceProvider->trackPageView(new PageTitle($pageTitle));
    }
}
