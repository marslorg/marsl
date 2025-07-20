<?php

namespace marsl\Infrastructure\StatisticsGateway\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation\MatomoTracker;
use marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Mock\Tracker;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IConfiguration;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IHttpRequestService;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IRequestParametersService;
use marsl\Infrastructure\StatisticsGateway\Ports\Drivers\IStatisticsGatewayResolverProvider;
use marsl\Infrastructure\StatisticsGateway\Ports\Drivers\IStatisticsGatewayServiceProvider;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\StatisticsGateway;

class StatisticsGatewayResolverProvider implements IStatisticsGatewayResolverProvider
{
    private IConfiguration $configuration;
    private IHttpRequestService $httpRequestService;
    private IRequestParametersService $requestParametersService;

    public function __construct(
        IConfiguration $configuration,
        IHttpRequestService $httpRequestService,
        IRequestParametersService $requestParametersService
    ) {
        $this->configuration = $configuration;
        $this->httpRequestService = $httpRequestService;
        $this->requestParametersService = $requestParametersService;
    }

    public function getStatisticsGatewayService(StatisticsGateway $statisticsGateway, SiteId $siteId, ApiUrl $apiUrl, AuthToken $authToken): IStatisticsGatewayServiceProvider
    {
        if ($statisticsGateway->getValue() == "matomo") {
            return new StatisticsGatewayServiceProvider(
                $this->configuration,
                $this->requestParametersService,
                new MatomoTracker(
                    $apiUrl,
                    $authToken,
                    $this->httpRequestService,
                    $siteId
                )
            );
        }

        return new StatisticsGatewayServiceProvider(
            $this->configuration,
            $this->requestParametersService,
            new Tracker()
        );
    }
}
