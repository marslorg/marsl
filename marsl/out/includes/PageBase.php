<?php

namespace marsl\includes;

include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\Module;
use marsl\user\AuthenticationBase;
use marsl\user\Role;

class PageBase
{
    private AuthenticationBase $authenticationBase;
    private Configuration $configuration;
    private DB $db;
    private LocationBase $locationBase;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        AuthenticationBase $authenticationBase,
        Configuration $configuration,
        DB $db,
        LocationBase $locationBase,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authenticationBase = $authenticationBase;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->locationBase = $locationBase;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    public function getTitle(): string|null
    {
        $title = "";
        $id = $this->getPageID();
        if ($id == $this->locationBase->getHomeLocation()) {
            return null;
        } else {
            $id = $this->getNavigationMappingByID($id);

            if ($this->authenticationBase->locationReadAllowed($id, $this->role->getRole())) {
                list($title, $module) = $this->getModuleAndTitleByID($id);
                $result = $this->db->query("SELECT `class` FROM `module` WHERE `file`='$module'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['class'])) {
                        $classPath = "\\marsl\\modules\\".$row['class'];
                        $content = ComponentBuilder::buildDependencies()->make($classPath);
                        if ($content instanceof Module) {
                            $newTitle = $content->getTitle();
                            if ($newTitle != null) {
                                $title = $newTitle." - ";
                            }
                        }
                    }
                }
            }
            return $title;
        }
    }

    public function getImage(): string|null
    {
        $id = $this->getPageID();
        $id = $this->getNavigationMappingByID($id);

        if ($this->authenticationBase->locationReadAllowed($id, $this->role->getRole())) {
            $result = $this->db->query("SELECT `module` FROM `navigation` WHERE `id`='$id' AND `type` IN ('1','2')");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['module'])) {
                    $module = $this->db->escapeString($row['module']);
                    $result2 = $this->db->query("SELECT `class` FROM `module` WHERE `file`='$module'");
                    while ($row2 = $this->db->fetchArray($result2)) {
                        if (is_string($row2['class'])) {
                            $classPath = "\\marsl\\modules\\".$row2['class'];
                            $content = ComponentBuilder::buildDependencies()->make($classPath);
                            if ($content instanceof Module) {
                                return $content->getImage();
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    public function getPageID(): int
    {
        $id = -1;
        if (!$this->configuration->getEnableOldURIs()) {
            $id = $this->getIDFromRestfulURI();
        }

        if ($id == -1) {
            $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
            $tag = $this->requestParametersService->fromGet()->getIntegerParameter("tag", -1);
            if (!$this->configuration->getEnableOldURIs() || $id != -1 || $tag == -1) {
                $result = $this->db->query("SELECT `homepage` FROM homepage");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['homepage'])) {
                        $id = intval(strval($row['homepage']));
                    } else {
                        $id = 0;
                    }
                }
            }
        }

        return $id;
    }

    public function getIDFromRestfulURI(): int
    {
        $id = -1;

        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        $explodedRequestURI = explode('/', $requestURI);

        // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
        // @phpstan-ignore greater.alwaysTrue
        if (sizeof($explodedRequestURI) > 0) {
            $pagePart = $explodedRequestURI[0];
            if ($pagePart == "tag") {
                return 0;
            }

            $explodedPagePart = explode('-', $pagePart);
            $explodedPagePartSize = sizeof($explodedPagePart);

            if ($explodedPagePartSize > 1) {
                $id = (int)$explodedPagePart[$explodedPagePartSize - 1];
            }
        }

        return $id;
    }

    public function getNavigationMappingByID(int $id): int
    {
        $newID = $id;
        $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['maps_to'])) {
                $newID = intval(strval($row['maps_to']));
            }
        }

        return $newID;
    }

    /**
     * @return array<string>
     */
    public function getModuleAndTitleByID(int $id): array
    {
        $title = "";
        $module = "";
        $result = $this->db->query("SELECT `module`, `name` FROM `navigation` WHERE `id`='$id' AND `type` IN ('1','2')");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['name'])
            && is_string($row['module'])) {
                $title = $row['name']." - ";
                $module = $row['module'];
            }
        }

        return array($title, $module);
    }
}
