<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\Internal;

include_once(dirname(__FILE__)."/../../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\IStatisticsGatewayResolver;
use marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\IStatisticsGatewayService;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IConfiguration;
use marsl\Infrastructure\StatisticsGateway\Ports\Drivers\IStatisticsGatewayResolverProvider;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\StatisticsGateway;

class StatisticsGatewayResolver implements IStatisticsGatewayResolver
{
    private IConfiguration $configuration;
    private IStatisticsGatewayResolverProvider $statisticsGatewayResolverProvider;

    public function __construct(
        IConfiguration $configuration,
        IStatisticsGatewayResolverProvider $statisticsGatewayResolverProvider
    ) {
        $this->configuration = $configuration;
        $this->statisticsGatewayResolverProvider = $statisticsGatewayResolverProvider;
    }

    public function getStatisticsGatewayService(): IStatisticsGatewayService
    {
        return new StatisticsGatewayService(
            $this->statisticsGatewayResolverProvider->getStatisticsGatewayService(
                $this->configuration->getStatisticsGateway(),
                $this->configuration->getSiteId(),
                $this->configuration->getStatisticsBasePath(),
                $this->configuration->getAuthToken()
            )
        );
    }
}
