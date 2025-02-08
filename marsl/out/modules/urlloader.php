<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\includes\PageBase;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class URLLoader implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private PageBase $pageBase;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        PageBase $pageBase,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->pageBase = $pageBase;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Shows the navigation in the admin backend.
     */
    public function adminNavi(): void
    {
        $curRole = $this->role->getRole();
        if ($this->authentication->moduleWriteAllowed("urlloader", $curRole)) {
            $categories = array();
            $categoryLinks = array();
            $result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['type'])
                && is_string($row['id'])
                && is_string($row['name'])) {
                    $type = intval(strval($row['type']));
                    $id = intval(strval($row['id']));
                    if ($type == 2 || (($type == 0 || $type == 1)
                    && $this->authentication->locationAdminAllowed($id, $curRole))) {
                        if ($type == 2 && $this->authentication->locationReadAllowed($id, $curRole)) {
                            if (is_string($row['category'])) {
                                $catID = intval(strval($row['category']));
                                $linkID = $id;
                                $linkName = $this->basic->convertToHTMLEntities($row['name']);
                                if (!array_key_exists($catID, $categoryLinks)) {
                                    $categoryLinks[$catID] = array();
                                }
                                array_push($categoryLinks[$catID], array('id' => $linkID, 'name' => $linkName));
                            }
                        } else {
                            $catID = $id;
                            $catName = $this->basic->convertToHTMLEntities($row['name']);
                            $catType = $type;
                            array_push($categories, array('id' => $catID, 'name' => $catName, 'type' => $catType));
                        }
                    }
                }
            }

            require(dirname(__FILE__)."/../admin/template/urlloader.navigation.tpl.php");
        }
    }

    /*
     * The interface to change the standard page.
     */
    public function admin(): void
    {
        if ($this->requestParametersService->fromGet()->getStringParameter("var", "") == "urlloader") {
            $this->contentAdmin();
        } else {
            if (($this->authentication->moduleAdminAllowed("urlloader", $this->role->getRole())) && ($this->user->isHead())) {
                if ($this->requestParametersService->fromPost()->getStringParameter("action", "") != "") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $homepage = $this->requestParametersService->fromPost()->getIntegerParameter("homepage");
                        if ($this->db->isExisting("SELECT `homepage` FROM `homepage` LIMIT 1")) {
                            $this->db->query("UPDATE `homepage` SET `homepage`='$homepage'");
                        } else {
                            $this->db->query("INSERT INTO `homepage`(`homepage`) VALUES('$homepage')");
                        }
                    }
                }
                $homepage = "";
                $result = $this->db->query("SELECT `homepage` FROM `homepage`");
                while ($row = $this->db->fetchArray($result)) {
                    $homepage = $row['homepage'];
                }
                $locations = array();
                $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `type` IN ('1','2')");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['id']) && is_string($row['name'])) {
                        $name = $this->basic->convertToHTMLEntities($row['name']);
                        array_push($locations, array('name' => $name,'id' => intval(strval($row['id']))));
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/urlloader.tpl.php");
            }
        }
    }

    /*
     * Updates a location with the submitted content.
     */
    private function updateLocation(): void
    {
        $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        $head = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("head", "")));
        $module = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("module")));
        $foot = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("foot", "")));
        $this->db->query("UPDATE `navigation` SET `head`='$head', `module`='$module', `foot`='$foot' WHERE `id`='$id'");
    }

    /*
     * Interface to change the content of a location.
     */
    private function contentAdmin(): void
    {
        $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        if ($this->authentication->moduleWriteAllowed("urlloader", $this->role->getRole())) {
            if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())) {
                if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "update") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $this->updateLocation();
                    }
                }
                $modules = $this->basic->getModules();
                $head = "";
                $module = "";
                $foot = "";
                $result = $this->db->query("SELECT `head`,`module`,`foot` FROM `navigation` WHERE `id`='$id'");
                while ($row = $this->db->fetchArray($result)) {
                    $head = $row['head'];
                    $proof = $row['module'];
                    $foot = $row['foot'];
                }
                $name = $this->basic->convertToHTMLEntities($this->navigation->getNamebyID($id));
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/urlloader.content.tpl.php");
            }
        }
    }

    /*
     * Loads the content of a location into the frontend and starts the display-function of a module.
     */
    public function display(): void
    {
        $id = $this->navigation->getPageID();

        $searchQuery = $this->db->escapeString($this->requestParametersService->fromGet()->getStringParameter("search", ""));

        if ($searchQuery != "") {
            $type = "standard";
            $scope = $this->requestParametersService->fromGet()->getStringParameter("scope", "");
            if ($scope != "") {
                $searchScope = explode("_", $scope);
                $searchContext = $searchScope[0];
                $type = $searchScope[1];
                if ($this->authentication->moduleReadAllowed($searchContext, $this->role->getRole())) {
                    $moduleInfo = $this->basic->getModule($searchContext);

                    if (isset($moduleInfo['class'])) {
                        $classPath = "\\marsl\\modules\\".$moduleInfo['class'];
                        $module = ComponentBuilder::buildDependencies()->make($classPath);

                        if ($module instanceof Module) {
                            if ($module->isSearchable()) {
                                $module->search($searchQuery, $type);
                            }
                        }
                    }
                }
            } else {
                //Implement a standard search, if possible over the standard search methods of each module.
            }
        } elseif ($this->isTagURL() && $id <= 0) {
            $scope = $this->getScope();
            if (isset($scope)) {
                $tagScope = explode("_", $scope);
                $tagContext = $tagScope[0];
                if ($tagContext == "general") {
                    $tagContext = "news";
                }
                $type = $tagScope[1];
                if ($this->authentication->moduleReadAllowed($tagContext, $this->role->getRole())) {
                    $moduleInfo = $this->basic->getModule($tagContext);

                    if (isset($moduleInfo['class'])) {
                        $classPath = "\\marsl\\modules\\".$moduleInfo['class'];
                        $module = ComponentBuilder::buildDependencies()->make($classPath);

                        if ($module instanceof Module) {
                            if ($module->isTaggable()) {
                                $module->displayTag();
                            }
                        }
                    }
                }
            }
        } else {
            $id = $this->pageBase->getNavigationMappingByID($id);

            if ($this->authentication->locationReadAllowed($id, $this->role->getRole())) {
                $result = $this->db->query("SELECT `head`, `foot`, `module` FROM `navigation` WHERE `id`='$id' AND `type` IN ('1','2')");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['head'])
                    && is_string($row['foot'])
                    && is_string($row['module'])) {
                        $head = $row['head'];
                        $foot = $row['foot'];
                        $module = $this->db->escapeString($row['module']);
                        $result2 = $this->db->query("SELECT `class` FROM `module` WHERE `file`='$module'");
                        echo $head;
                        while ($row2 = $this->db->fetchArray($result2)) {
                            if (is_string($row2['class'])) {
                                $classPath = "\\marsl\\modules\\".$row2['class'];
                                $content = ComponentBuilder::buildDependencies()->make($classPath);
                                if ($content instanceof Module) {
                                    $content->display();
                                }
                            }
                        }
                        echo $foot;
                    }
                }
            }
        }
    }

    private function isTagURL(): bool
    {
        $isRestfulTag = false;
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);

            // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
            // @phpstan-ignore greater.alwaysTrue
            if (sizeof($explodedRequestURI) > 0) {
                $uriFirstPart = $explodedRequestURI[0];
                $isRestfulTag = $uriFirstPart == "tag";
            }
        }
        return $this->requestParametersService->fromGet()->getIntegerParameter("tag", -1) != -1 || $isRestfulTag;
    }

    private function getScope(): string|null
    {
        $scope = $this->requestParametersService->fromGet()->getStringParameter("scope", "");
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($scope == "" && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $scope = $explodedRequestURI[1];
            }
        }

        if ($scope == "") {
            $scope = null;
        }

        return $scope;
    }

    /*
     * Interface method stub.
    */
    public function isSearchable(): bool
    {
        return false;
    }

    /*
     * Interface method stub.
     */
    public function getSearchList(): array
    {
        return array();
    }

    /*
     * Interface method stub.
    */
    public function search(string $query, string $type): void
    {
    }

    /*
     * Interface method stub.
    */
    public function isTaggable(): bool
    {
        return false;
    }

    /*
     * Interface method stub.
    */
    public function getTagList(): array
    {
        return array();
    }

    /*
     * Interface method stub.
    */
    public function addTags(string $tagString, string $type, int $news): void
    {
    }

    /*
     * Interface method stub.
    */
    public function getTagString(string $type, int $news): string|null
    {
        return null;
    }

    public function getTags(string $type, int $news): array
    {
        return array();
    }

    public function displayTag(): void
    {
    }

    public function getImage(): string|null
    {
        return $this->pageBase->getImage();
    }

    public function getTitle(): string|null
    {
        return $this->pageBase->getTitle();
    }

    public function getRestfulURIPartFromOldURL(): string|null
    {
        return null;
    }

    public function getOldURIPartFromRestfulURL(): string|null
    {
        return null;
    }

    public function getRedirectURI(): string
    {
        $uri = "";
        if ($this->configuration->getEnableOldURIs()) {
            $uri = $this->getOldURI();
        } else {
            $uri = $this->getRestfulURI();
        }
        return $uri;
    }

    private function getRestfulURI(): string
    {
        $uri = "";
        if ($this->requestParametersService->fromGet()->getIntegerParameter("id", -1) != -1) {
            $uri = $this->getRestfulURIForStandardPages();
        } elseif ($this->requestParametersService->fromGet()->getIntegerParameter("tag", -1) != -1) {
            $uri = $this->getRestfulURIForTagPage();
        }
        return $uri;
    }

    private function getRestfulURIForTagPage(): string
    {
        $uri = "";
        $scope = $this->requestParametersService->fromGet()->getStringParameter("scope", "");
        if ($scope != "") {
            $explodedScope = explode("_", $scope);
            $module = $explodedScope[0];
            $classPath = "\\marsl\\modules\\".$module;
            $moduleClass = ComponentBuilder::buildDependencies()->make($classPath);
            if ($moduleClass instanceof Module) {
                $uri = "tag".$moduleClass->getRestfulURIPartFromOldURL();
            }
        }
        return $uri;
    }

    private function getRestfulURIForStandardPages(): string
    {
        $uri = "";
        $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        $id = $this->pageBase->getNavigationMappingByID($id);
        if ($this->authentication->locationReadAllowed($id, $this->role->getRole())) {
            $uri = $this->navigation->generateRestfulURIByID($id);
            list($title, $module) = $this->navigation->getModuleAndTitleByID($id);
            if (!empty($module)) {
                $result = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['class'])) {
                        $classPath = "\\marsl\\modules\\".$row['class'];
                        $moduleClass = ComponentBuilder::buildDependencies()->make($classPath);
                        if ($moduleClass instanceof Module) {
                            $uri = $uri.$moduleClass->getRestfulURIPartFromOldURL();
                        }
                    }
                }
            }
        }

        return $uri;
    }

    private function getOldURI(): string
    {
        $uri = "";

        $firstURIPart = "";
        $requestUri = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        $explodedRequestUri = explode('/', $requestUri);
        if (sizeof($explodedRequestUri) > 1) {
            $firstURIPart = $explodedRequestUri[0];
        }

        if ($firstURIPart == "tag") {
            $uri = $this->getOldURIForTagPage();
        } else {
            $uri = $this->getOldURIForStandardPages();
        }

        return $uri;
    }

    private function getOldURIForTagPage(): string
    {
        $uri = "";
        $requestUri = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        $explodedRequestURI = explode('/', $requestUri);
        if (sizeof($explodedRequestURI) > 2) {
            $scope = $explodedRequestURI[1];
            $explodedScope = explode("_", $scope);
            $module = $explodedScope[0];
            $classPath = "\\marsl\\modules\\".$module;
            $moduleClass = ComponentBuilder::buildDependencies()->make($classPath);
            if ($moduleClass instanceof Module) {
                $uri = $moduleClass->getOldURIPartFromRestfulURL();
            }
        }

        if ($uri == null) {
            $uri = "";
        }

        return $uri;
    }

    private function getOldURIForStandardPages(): string
    {
        $uri = "";
        $id = $this->navigation->getIDFromRestfulURI();
        $uri = "index.php?id=".$id;
        list($title, $module) = $this->navigation->getModuleAndTitleByID($id);
        if (!empty($module)) {
            $result = $this->db->query("SELECT `name`, `file`, `class` FROM `module` WHERE `file`='$module'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['class'])) {
                    $classPath = "\\marsl\\modules\\".$row['class'];
                    $moduleClass = ComponentBuilder::buildDependencies()->make($classPath);
                    if ($moduleClass instanceof Module) {
                        $uri = $uri.$moduleClass->getOldURIPartFromRestfulURL();
                    }
                }
            }
        }

        return $uri;
    }

    public function redirect(): void
    {
        header("HTTP/1.1 301 Moved Permanently");
        $redirectURL = $this->configuration->getDomain().$this->configuration->getBasePath()."/".$this->getRedirectURI();
        header("Location: ".$redirectURL);
        exit();
    }

    public function shouldRedirect(): bool
    {
        $result = false;
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        $tag = $this->requestParametersService->fromGet()->getIntegerParameter("tag", -1);
        if ($requestURI != "" || $id != -1 || $tag != -1) {
            if ($this->configuration->getEnableOldURIs()) {
                if ($requestURI != "" && ($id == -1 || $tag == -1)) {
                    $result = true;
                }
            } else {
                if (($requestURI == "" || $requestURI == "index.php")
                && ($id != -1 || $tag != -1)) {
                    $result = true;
                }
            }
        }

        return $result;
    }
}
