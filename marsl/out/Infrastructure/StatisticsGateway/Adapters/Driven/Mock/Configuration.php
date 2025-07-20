<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Mock;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IConfiguration;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\BasePath;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Domain;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Host;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Port;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Protocol;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\RemoteIpFieldName;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\StatisticsGateway;

class Configuration implements IConfiguration
{
    public function getStatisticsGateway(): StatisticsGateway
    {
        return new StatisticsGateway("mock");
    }

    public function getSiteId(): SiteId
    {
        return new SiteId(1);
    }

    public function getStatisticsApiProtocol(): Protocol
    {
        return new Protocol("http");
    }

    public function getStatisticsApiHost(): Host
    {
        return new Host("localhost");
    }

    public function getStatisticsApiPort(): Port
    {
        return new Port(80);
    }

    public function getAuthToken(): AuthToken
    {
        return new AuthToken("mock");
    }

    public function getRemoteIpFieldName(): RemoteIpFieldName
    {
        return new RemoteIpFieldName("REMOTE_ADDR");
    }

    public function getBasePath(): BasePath
    {
        return new BasePath("/");
    }

    public function getDomain(): Domain
    {
        return new Domain("http://localhost");
    }
}
