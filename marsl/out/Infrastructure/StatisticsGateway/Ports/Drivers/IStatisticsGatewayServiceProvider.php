<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Drivers;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\ValueObjects\PageTitle;

interface IStatisticsGatewayServiceProvider
{
    public function trackPageView(PageTitle $pageTitle): void;
}
