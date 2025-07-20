<?php

namespace marsl\Infrastructure\StatisticsGateway\Adapters\Driven\Implementation;

include_once(dirname(__FILE__)."/../../../../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../../../../vendor/autoload.php");
include_once(dirname(__FILE__)."/../../../../../autoload.php");
include_once(dirname(__FILE__)."/Matomo/ThirdParty/InternalMatomoTracker.php");

use marsl\Infrastructure\StatisticsGateway\Ports\Driven\IHttpRequestService;
use marsl\Infrastructure\StatisticsGateway\Ports\Driven\ITracker;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AcceptLanguage;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ApiUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\AuthToken;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\BasePath;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\IpAddress;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\PageTitle;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\PageViewId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\DataKey;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\DataValue;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ParamKey;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\ParamValue;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\RefererUrl;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\SiteId;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\Url;
use marsl\Infrastructure\StatisticsGateway\ValueObjects\UserAgent;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class MatomoTracker implements ITracker
{
    private ApiUrl $apiUrl;
    private AuthToken $authToken;
    private IHttpRequestService $httpRequestService;
    private SiteId $siteId;

    public function __construct(
        ApiUrl $apiUrl,
        AuthToken $authToken,
        IHttpRequestService $httpRequestService,
        SiteId $siteId
    ) {
        $this->apiUrl = $apiUrl;
        $this->authToken = $authToken;
        $this->httpRequestService = $httpRequestService;
        $this->siteId = $siteId;
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function trackPageView(
        AcceptLanguage $acceptLanguage,
        IpAddress $ipAddress,
        PageTitle $pageTitle,
        RefererUrl $refererUrl,
        Url $url,
        UserAgent $userAgent
    ): void {
        $httpPostRequest = $this->httpRequestService->constructPostRequest($this->apiUrl, new BasePath("matomo.php"));
        $httpPostRequest->addData(new DataKey("token_auth"), new DataValue($this->authToken->getValue()));
        $httpPostRequest->addParam(new ParamKey("idsite"), new ParamValue((string)$this->siteId->getValue()));
        $httpPostRequest->addParam(new ParamKey("rec"), new ParamValue("1"));
        $httpPostRequest->addParam(new ParamKey("apiv"), new ParamValue("1"));
        $httpPostRequest->addParam(new ParamKey("action_name"), new ParamValue(urlencode($pageTitle->getValue())));
        $httpPostRequest->addParam(new ParamKey("url"), new ParamValue($url->getValue()));
        $httpPostRequest->addParam(new ParamKey("urlref"), new ParamValue($refererUrl->getValue()));
        $httpPostRequest->addParam(new ParamKey("cip"), new ParamValue($ipAddress->getValue()));
        $httpPostRequest->addParam(new ParamKey("pv_id"), new ParamValue(PageViewId::generateNew()->getValue()));
        $httpPostRequest->addParam(new ParamKey("ua"), new ParamValue($userAgent->getValue()));
        $httpPostRequest->addParam(new ParamKey("lang"), new ParamValue($acceptLanguage->getValue()));
        $httpPostRequest->fireAndForget();
    }
}
