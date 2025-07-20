<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Drivers;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\StatisticsGateway;

interface IStatisticsGatewayResolverProvider
{
    public function getStatisticsGatewayService(
        StatisticsGateway $statisticsGateway,
        SiteId $siteId,
        ApiUrl $apiUrl,
        AuthToken $authToken
    ): IStatisticsGatewayServiceProvider;
}
