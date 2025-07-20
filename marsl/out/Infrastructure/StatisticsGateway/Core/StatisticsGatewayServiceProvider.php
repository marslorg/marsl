<?php

namespace marsl\Infrastructure\StatisticsGateway\Core;

include_once(dirname(__FILE__)."/../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../autoload.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IConfiguration;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IRequestParametersService;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\ITracker;
use marsl\Infrastructure\StatisticsGateway\Ports\Drivers\IStatisticsGatewayServiceProvider;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AcceptLanguage;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\IpAddress;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\PageTitle;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\RefererUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Url;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\UserAgent;

class StatisticsGatewayServiceProvider implements IStatisticsGatewayServiceProvider
{
    private IConfiguration $configuration;
    private IRequestParametersService $requestParametersService;
    private ITracker $tracker;

    public function __construct(
        IConfiguration $configuration,
        IRequestParametersService $requestParametersService,
        ITracker $tracker
    ) {
        $this->configuration = $configuration;
        $this->requestParametersService = $requestParametersService;
        $this->tracker = $tracker;
    }

    public function trackPageView(PageTitle $pageTitle): void
    {
        $this->tracker->trackPageView(
            new AcceptLanguage($this->requestParametersService->fromServer()->getStringParameter("HTTP_ACCEPT_LANGUAGE", "")),
            new IpAddress($this->requestParametersService->fromServer()->getStringParameter($this->configuration->getRemoteIpFieldName()->getValue())),
            $pageTitle,
            new RefererUrl($this->requestParametersService->fromServer()->getStringParameter("HTTP_REFERER", "")),
            new Url($this->getUrl()),
            new UserAgent($this->requestParametersService->fromServer()->getStringParameter("HTTP_USER_AGENT", ""))
        );
    }

    private function getUrl(): string
    {
        return $this->configuration->getDomain()->getValue().$this->configuration->getBasePath()->getValue()."/".$this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
    }
}
