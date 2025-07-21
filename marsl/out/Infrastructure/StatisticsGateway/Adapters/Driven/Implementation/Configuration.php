<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IConfiguration;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\BasePath;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Domain;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\RemoteIpFieldName;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\StatisticsGateway;

class Configuration implements IConfiguration
{
    private \marsl\includes\Configuration $configuration;

    public function __construct(\marsl\includes\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getStatisticsGateway(): StatisticsGateway
    {
        return new StatisticsGateway($this->configuration->getStatisticsGateway());
    }

    public function getSiteId(): SiteId
    {
        return new SiteId($this->configuration->getSiteId());
    }

    public function getStatisticsBasePath(): ApiUrl
    {
        return new ApiUrl($this->configuration->getStatisticsBasePath());
    }

    public function getAuthToken(): AuthToken
    {
        return new AuthToken($this->configuration->getAuthToken());
    }

    public function getRemoteIpFieldName(): RemoteIpFieldName
    {
        return new RemoteIpFieldName($this->configuration->getRemoteIpFieldName());
    }

    public function getBasePath(): BasePath
    {
        return new BasePath($this->configuration->getBasePath());
    }

    public function getDomain(): Domain
    {
        return new Domain($this->configuration->getDomain());
    }
}
