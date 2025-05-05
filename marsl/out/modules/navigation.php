<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/slugify/vendor/autoload.php");
include_once(dirname(__FILE__)."/../autoload.php");

use Cocur\Slugify\Slugify;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\includes\PageBase;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class Navigation implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private PageBase $pageBase;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        PageBase $pageBase,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->pageBase = $pageBase;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    /*
     * Displays the admin interface for the navigation.
     */
    public function admin(): void
    {
        $curRole = $this->role->getRole();
        if ($this->authentication->moduleAdminAllowed("navigation", $curRole)) {
            $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
            $this->evalAction($action);

            if ($action != "role") {
                $categories = array();
                $catcontents = array();
                $links = array();
                $result = $this->db->query("SELECT `id`, `name`, `pos`, `category`, `maps_to`, `type` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['id'])
                        && is_string($row['name'])
                        && ($row['maps_to'] == null || is_string($row['maps_to']))
                        && is_string($row['type'])
                        && is_string($row['pos'])
                        && ($row['category'] == null || is_string($row['category']))) {
                        $id = intval(strval($row['id']));
                        $type = intval(strval($row['type']));
                        $pos = intval(strval($row['pos']));
                        if ($this->authentication->locationAdminAllowed($id, $curRole)) {
                            if ($row['maps_to'] == null || intval(strval($row['maps_to'])) == 0) {
                                // PHPStan is wrong.
                                // @phpstan-ignore booleanAnd.leftAlwaysTrue
                                $roleEditor = $this->authentication->locationAdminAllowed($id, $curRole)
                                                && $this->authentication->locationExtendedAllowed($id, $curRole)
                                                && $this->authentication->locationWriteAllowed($id, $curRole)
                                                && $this->authentication->locationReadAllowed($id, $curRole);
                                $name = $this->basic->convertToHTMLEntities($row['name']);
                                if ($type == 0) {
                                    array_push($categories, array('id' => $id, 'name' => $name, 'pos' => $pos, 'role' => $roleEditor));
                                } elseif ($type == 1) {
                                    array_push($catcontents, array('id' => $id, 'name' => $name, 'pos' => $pos, 'role' => $roleEditor));
                                } elseif ($type == 2) {
                                    $category = is_string($row['category']) ? intval(strval($row['category'])) : 0;
                                    array_push($links, array('id' => $id, 'name' => $name, 'pos' => $pos, 'category' => $category, 'role' => $roleEditor));
                                }
                            }
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/navigation.tpl.php");
            }
        }
    }

    /*
     * Displays the navigation.
     */
    public function display(): void
    {
        if ($this->authentication->moduleReadAllowed("navigation", $this->role->getRole())) {
            $categories = array();
            $links = array();
            $result = $this->db->query("SELECT `id`, `name`, `type`, `category` FROM `navigation` WHERE `type` IN ('0','1','2') ORDER BY `pos`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                        && is_string($row['name'])
                        && is_string($row['type'])
                        && ($row['category'] == null || is_string($row['category']))) {
                    $id = intval(strval($row['id']));
                    $type = intval(strval($row['type']));
                    $category = is_string($row['category']) ? intval(strval($row['category'])) : 0;
                    if ($this->authentication->locationReadAllowed($id, $this->role->getRole())) {
                        $name = $this->basic->convertToHTMLEntities($row['name']);

                        $link = "";

                        if ($this->configuration->getEnableOldURIs()) {
                            $link = "index.php?id=".$id;
                        } else {
                            $link = $this->generateRestfulURI($id, $row['name']);
                        }

                        if ($type == 0 || $type == 1) {
                            array_push($categories, array('id' => $id, 'name' => $name, 'link' => $link, 'type' => $type));
                        } elseif ($type == 2) {
                            if (!array_key_exists($category, $links)) {
                                $links[$category] = array();
                            }
                            array_push($links[$category], array('id' => $id, 'name' => $name, 'link' => $link));
                        }
                    }
                }
            }

            require(dirname(__FILE__)."/../template/navigation.tpl.php");
        }
    }

    /*
     * Executes the given action in the admin interface.
     */
    private function evalAction(string $action): void
    {
        if ($this->authentication->moduleAdminAllowed("navigation", $this->role->getRole())) {
            $roleID = $this->role->getRole();
            if ($action == "addcat") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','0','0')");
                    $location = (int)$this->db->lastInsertedID();
                    $this->role->setRights($roleID, $location, true, true, true, true);
                }
            } elseif ($action == "addcatcontent") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','1','0')");
                    $location = (int)$this->db->lastInsertedID();
                    $this->role->setRights($roleID, $location, true, true, true, true);
                }
            } elseif ($action == "addlink") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $this->db->query("INSERT INTO `navigation`(`name`,`type`, `pos`) VALUES('Standard','2','0')");
                    $location = (int)$this->db->lastInsertedID();
                    $this->role->setRights($roleID, $location, true, true, true, true);
                }
            } elseif ($action == "change") {
                $id = $this->requestParametersService->fromPost()->getIntegerParameter("id", -1);
                if ($id != -1) {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $name = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("name", ""));
                        $pos = $this->requestParametersService->fromPost()->getIntegerParameter("pos");
                        if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())) {
                            $type = $this->requestParametersService->fromGet()->getIntegerParameter("type", -1);
                            if ($type == 0 || $type == 1) {
                                $this->db->query("UPDATE `navigation` SET `name`='$name', `pos`='$pos' WHERE `id`='$id'");
                            } elseif ($type == 2) {
                                $catbelong = $this->requestParametersService->fromPost()->getIntegerParameter("catbelong");
                                $this->db->query("UPDATE `navigation` SET `name`='$name', `pos`='$pos', `category`='$catbelong' WHERE `id`='$id'");
                            }
                        }
                    }
                }
            } elseif ($action == "del") {
                $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())
                    && $this->authentication->locationExtendedAllowed($id, $this->role->getRole())
                    && $this->authentication->locationWriteAllowed($id, $this->role->getRole())
                    && $this->authentication->locationReadAllowed($id, $this->role->getRole())) {
                        $this->db->query("UPDATE `navigation` SET `type`='3' WHERE `id`='$id'");
                    }
                }
            } elseif ($action == "role") {
                $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())
                    && $this->authentication->locationExtendedAllowed($id, $this->role->getRole())
                    && $this->authentication->locationWriteAllowed($id, $this->role->getRole())
                    && $this->authentication->locationReadAllowed($id, $this->role->getRole())) {
                    $name = $this->basic->convertToHTMLEntities($this->getNamebyID($id));
                    $roles = $this->role->getPossibleRoles($this->role->getRole());
                    if ($this->requestParametersService->fromPost()->getStringParameter("change", "") != "") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                            $this->requestParametersService->fromPost()->getStringParameter("authToken")
                        )) {
                            foreach ($roles as $roleID) {
                                if ($roleID != $this->role->getRole()) {
                                    $read = $this->requestParametersService->fromPost()->getBoolParameter($roleID."_read", false);
                                    $write = $this->requestParametersService->fromPost()->getBoolParameter($roleID."_write", false);
                                    $extended = $this->requestParametersService->fromPost()->getBoolParameter($roleID."_extended", false);
                                    $admin = $this->requestParametersService->fromPost()->getBoolParameter($roleID."_admin", false);
                                    $this->role->setRights($roleID, $id, $read, $write, $extended, $admin);
                                }
                            }
                        }
                    }
                    $rights = array();
                    foreach ($roles as $roleID) {
                        if ($roleID != $this->role->getRole()) {
                            if ($this->db->isExisting("SELECT `role` FROM `rights` WHERE `role`='$roleID' AND `location`='$id' LIMIT 1")) {
                                $result = $this->db->query("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights` WHERE `role`='$roleID' AND `location`='$id'");
                                while ($row = $this->db->fetchArray($result)) {
                                    if (is_string($row['role'])
                                        && is_string($row['read'])
                                        && is_string($row['write'])
                                        && is_string($row['extended'])
                                        && is_string($row['admin'])) {
                                        $role = intval(strval($row['role']));
                                        $roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($role));
                                        array_push($rights, array('name' => $roleName,'role' => $role,'read' => intval(strval($row['read'])),'write' => intval(strval($row['write'])),'extended' => intval(strval($row['extended'])),'admin' => intval(strval($row['admin']))));
                                    }
                                }
                            } else {
                                $roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($roleID));
                                array_push($rights, array('name' => $roleName,'role' => $roleID,'read' => "0",'write' => "0",'extended' => "0",'admin' => "0"));
                            }
                        }
                    }
                    $authTime = time();
                    $authToken = $this->authentication->getToken($authTime);
                    require_once(dirname(__FILE__)."/../admin/template/navigation.role.tpl.php");
                }
            }
        }
    }

    /*
     * Gets the name of a link by the given ID.
     */
    public function getNamebyID(int $id): string
    {
        $name = "";
        $result = $this->db->query("SELECT `name` FROM `navigation` WHERE `id`='$id'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['name'])) {
                $name = $row['name'];
            }
        }
        return $name;
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
        return null;
    }

    public function getTitle(): string|null
    {
        return null;
    }

    public function getRestfulURIPartFromOldURL(): string|null
    {
        return null;
    }

    public function getOldURIPartFromRestfulURL(): string|null
    {
        return null;
    }

    public function generateRestfulURIByID(int $id): string
    {
        list($title, $module) = $this->getModuleAndTitleByID($id);
        $uri = $this->generateRestfulURI($id, $title);
        return $uri;
    }

    public function generateRestfulURI(int $id, string $title): string
    {
        $slugify = new Slugify();
        return  $slugify->slugify($title)."-".$id;
    }

    /**
     * @return array<string>
     */
    public function getModuleAndTitleByID(int $id): array
    {
        return $this->pageBase->getModuleAndTitleByID($id);
    }

    public function getIDFromRestfulURI(): int
    {
        return $this->pageBase->getIDFromRestfulURI();
    }

    public function getPageID(): int
    {
        return $this->pageBase->getPageID();
    }

    public function getRelativeURI(int $id, string|null $title, bool $withParameters): string
    {
        $uri = "";

        if ($this->configuration->getEnableOldURIs()) {
            $uri = "index.php?id=".$id;
            if ($withParameters) {
                $uri = $uri."&";
            }
        } else {
            if (isset($title) && !empty($title)) {
                $uri = $this->generateRestfulURI($id, $title);
            } else {
                $uri = $this->generateRestfulURIByID($id);
            }

            if ($withParameters) {
                $uri = $uri."?";
            }
        }

        return $uri;
    }
}
