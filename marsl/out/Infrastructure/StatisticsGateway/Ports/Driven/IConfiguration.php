<?php

namespace marsl\Infrastructure\StatisticsGateway\Ports\Driven;

include_once(dirname(__FILE__)."/../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\BasePath;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Domain;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Host;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Port;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Protocol;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\RemoteIpFieldName;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\StatisticsGateway;

interface IConfiguration
{
    public function getStatisticsGateway(): StatisticsGateway;

    public function getSiteId(): SiteId;

    public function getStatisticsApiProtocol(): Protocol;

    public function getStatisticsApiHost(): Host;

    public function getStatisticsApiPort(): Port;

    public function getAuthToken(): AuthToken;

    public function getRemoteIpFieldName(): RemoteIpFieldName;

    public function getBasePath(): BasePath;

    public function getDomain(): Domain;
}
