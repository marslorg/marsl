<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/vendor/autoload.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\StatisticsGateway\Adapters\Drivers\Service\IStatisticsGatewayResolver;
use marsl\modules\Module;
use marsl\modules\Navigation;
use marsl\modules\URLLoader;
use marsl\user\Authentication;
use marsl\user\Role;

class Main
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private IStatisticsGatewayResolver $statisticsGatewayResolver;
    private URLLoader $urlLoader;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        IStatisticsGatewayResolver $statisticsGatewayResolver,
        URLLoader $urlLoader
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->statisticsGatewayResolver = $statisticsGatewayResolver;
        $this->urlLoader = $urlLoader;
    }

    /*
     * Initialize the frontend screen.
     */
    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

        if ($this->urlLoader->shouldRedirect()) {
            $this->urlLoader->redirect();
        }

        date_default_timezone_set($this->configuration->getTimezone());
        $fbcomments = $this->configuration->getFBComments();

        $title = $this->basic->convertToHTMLEntities($this->basic->getTitle());
        $image = $this->basic->convertToHTMLEntities($this->basic->getImage());
        $serverName = $this->basic->convertToHTMLEntities($this->configuration->getClusterServer());
        $domain = $this->configuration->getDomain();
        $basePath = $this->configuration->getBasePath();
        $baseURL = $domain.$basePath;
        $pageID = $this->navigation->getPageID();
        $showContentForWeb = !$this->authentication->isAppAllowed();

        if ($this->configuration->getStatisticsGateway() != "") {
            $statisticsGatewayService = $this->statisticsGatewayResolver->getStatisticsGatewayService();
            $statisticsGatewayService->trackPageView($title);
        }

        require_once("template/index.tpl.php");

        $this->db->close();

    }

    // Method used in PHP template file.
    // @phpstan-ignore method.unused
    private function displaySearchBox(): void
    {
        $domain = $this->configuration->getDomain();
        $basePath = $this->configuration->getBasePath();
        $baseURL = $domain.$basePath;
        $searchList = array();
        $modules = $this->basic->getModules();
        foreach ($modules as $module) {
            $file = $module['file'];
            $classPath = "\marsl\modules\\".$module['class'];
            $searchClass = ComponentBuilder::buildDependencies()->make($classPath);
            if (($searchClass instanceof Module) && $searchClass->isSearchable()) {
                $typeArray = $searchClass->getSearchList();
                foreach ($typeArray as $type) {
                    array_push($searchList, array('class' => $file, 'type' => $type['type'], 'text' => $type['text']));
                }
            }
        }


        require_once("template/searchbox.tpl.php");
    }

}

$main = ComponentBuilder::buildDependencies()->make('marsl\Main');

if ($main instanceof Main) {
    $main->display();
}
