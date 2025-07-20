<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\ValueObjects\AcceptLanguage;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\IpAddress;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\PageTitle;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\RefererUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Url;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\UserAgent;

interface ITracker
{
    public function trackPageView(
        AcceptLanguage $acceptLanguage,
        IpAddress $ipAddress,
        PageTitle $pageTitle,
        RefererUrl $refererUrl,
        Url $url,
        UserAgent $userAgent
    ): void;
}
