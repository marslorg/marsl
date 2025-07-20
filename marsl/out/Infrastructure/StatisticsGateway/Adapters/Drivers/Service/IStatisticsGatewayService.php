<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

interface IStatisticsGatewayService
{
    public function trackPageView(string $pageTitle): void;
}
