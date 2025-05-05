<?php

namespace marsl\modules\board;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\Navigation;
use marsl\user\Authentication;
use marsl\user\Role;

class BoardBase
{
    private Authentication $authentication;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    /** @var array<array<array<string, bool>>> */
    private array $boardRights;

    public function __construct(
        Authentication $authentication,
        DB $db,
        Configuration $configuration,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->db = $db;
        $this->configuration = $configuration;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;

        $curBoardRights = array();
        $result = $this->db->query("SELECT `role`, `board`, `read`, `write`, `extended`, `admin` FROM `rights_board` WHERE `read`='1' OR `write`='1' OR `extended`='1' OR `admin`='1'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['role']) && is_string($row['board'])) {
                $roleID = intval(strval($row['role']));
                $board = intval(strval($row['board']));
                $read = $row['read'] == 1;
                $write = $row['write'] == 1;
                $extended = $row['extended'] == 1;
                $admin = $row['admin'] == 1;

                if (array_key_exists($roleID, $curBoardRights) && array_key_exists($board, $curBoardRights[$roleID])) {
                    $curBoardRights[$roleID][$board]['read'] = $curBoardRights[$roleID][$board]['read'] || $read;
                    $curBoardRights[$roleID][$board]['write'] = $curBoardRights[$roleID][$board]['write'] || $write;
                    $curBoardRights[$roleID][$board]['extended'] = $curBoardRights[$roleID][$board]['extended'] || $extended;
                    $curBoardRights[$roleID][$board]['admin'] = $curBoardRights[$roleID][$board]['admin'] || $admin;
                } else {
                    $curBoardRights[$roleID][$board]['read'] = $read;
                    $curBoardRights[$roleID][$board]['write'] = $write;
                    $curBoardRights[$roleID][$board]['extended'] = $extended;
                    $curBoardRights[$roleID][$board]['admin'] = $admin;
                }
            }
        }
        $this->boardRights = $curBoardRights;
    }

    public function getBoardID(): int
    {
        $boardID = $this->requestParametersService->fromGet()->getIntegerParameter("board", -1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($boardID == -1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $boardSlug = $explodedRequestURI[1];
                $explodedBoardSlug = explode('-', $boardSlug);
                $explodedBoardSlugSize = sizeof($explodedBoardSlug);

                // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
                // @phpstan-ignore greater.alwaysTrue
                if ($explodedBoardSlugSize > 0) {
                    $boardID = intval(strval($explodedBoardSlug[$explodedBoardSlugSize - 1]));
                }
            }
        }

        return $boardID;
    }

    /*
     * Get location of a board.
     */
    public function getLocation(int $board): int
    {
        $location = -1;
        $result = $this->db->query("SELECT `location`, `type` FROM `board` WHERE `board`='$board'");
        while ($row = $this->db->fetchArray($result)) {
            if ($row['type'] == "1") {
                if (is_string($row['location'])) {
                    $board = intval(strval($row['location']));
                    $result2 = $this->db->query("SELECT `location` FROM `board` WHERE `board`='$board'");
                    while ($row2 = $this->db->fetchArray($result2)) {
                        if (is_string($row2['location'])) {
                            $location = intval(strval($row2['location']));
                        }
                    }
                }
            } else {
                if (is_string($row['location'])) {
                    $location = intval(strval($row['location']));
                }
            }
        }
        return $location;
    }

    /*
     * Get the name of a board by a given board ID.
     */
    public function getNameById(int $board): string
    {
        $title = "";
        $result = $this->db->query("SELECT `title` FROM `board` WHERE `board`='$board'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['title'])) {
                $title = $row['title'];
            }
        }
        return $title;
    }

    public function getBoardURIPart(int $boardID, string $boardTitle): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&action=threads&board=".$boardID;
        } else {
            $result = "/".$this->navigation->generateRestfulURI($boardID, $boardTitle)."/threads";
        }
        return $result;
    }

    public function getPageURIFormatted(int $page): string
    {
        $result = "";
        if ($page > 1) {
            if ($this->configuration->getEnableOldURIs()) {
                $result = "&page=".$page;
            } else {
                $result = "/".$page;
            }
        }
        return $result;
    }

    /*
     * Returns whether the logged in user is a global administrator in the board. Either as a module administrator or as a location administrator.
     */
    public function isAdmin(int $boardID, int $userID): bool
    {
        $location = $this->getLocation($boardID);
        $roleID = $this->role->getRoleByUser($userID);
        if (($this->adminAllowed($boardID, $roleID)
            && $this->writeAllowed($boardID, $roleID)
            && $this->readAllowed($boardID, $roleID))
        || ($this->authentication->moduleReadAllowed("board", $roleID)
            && $this->authentication->moduleWriteAllowed("board", $roleID)
            && $this->authentication->locationReadAllowed($location, $roleID)
            && $this->authentication->locationWriteAllowed($location, $roleID)
            && ($this->authentication->locationAdminAllowed($location, $roleID)
            || $this->authentication->moduleAdminAllowed("board", $roleID)))) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Return whether the logged in user is a operator in the board.
     */
    public function isOperator(int $boardID, int $userID): bool
    {
        if ($this->db->isExisting("SELECT `user` FROM `board_operator` WHERE `board`='$boardID' AND `user`='$userID' LIMIT 1")) {
            $roleID = $this->role->getRolebyUser($userID);
            $location = $this->getLocation($boardID);
            if ($this->authentication->moduleReadAllowed("board", $roleID)
            && $this->authentication->moduleWriteAllowed("board", $roleID)
            && $this->authentication->locationReadAllowed($location, $roleID)
            && $this->authentication->locationWriteAllowed($location, $roleID)
            && $this->readAllowed($boardID, $roleID) && $this->writeAllowed($boardID, $roleID)
            && $this->extendedAllowed($boardID, $roleID)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * Returns whether the role is allowed to read the board.
     */
    public function readAllowed(int $board, int $roleID): bool
    {
        return $this->evalRights("read", $board, $roleID);
    }

    /*
     * Returns whether the role is allowed to write in the board.
    */
    public function writeAllowed(int $board, int $roleID): bool
    {
        return $this->evalRights("write", $board, $roleID);
    }

    /*
     * Returns whether the role is allowed to do extended services in the board.
    */
    public function extendedAllowed(int $board, int $roleID): bool
    {
        return $this->evalRights("extended", $board, $roleID);
    }

    /*
     * Returns whether the role is allowed to administrate the board.
    */
    public function adminAllowed(int $board, int $roleID): bool
    {
        return $this->evalRights("admin", $board, $roleID);
    }

    /*
     * Evaluates the right matrix.
     */
    private function evalRights(string $right, int $board, int $roleID): bool
    {
        $hasRight = false;
        $roles = $this->role->getPossibleRoles($roleID);
        $rolesLength = sizeof($roles);
        for ($roleIdx = 0; !$hasRight && $roleIdx < $rolesLength; $roleIdx++) {
            $curRoleID = $roles[$roleIdx];
            if (array_key_exists($curRoleID, $this->boardRights) && array_key_exists($board, $this->boardRights[$curRoleID]) && array_key_exists($right, $this->boardRights[$curRoleID][$board])) {
                $hasRight = $this->boardRights[$curRoleID][$board][$right];
            }
        }
        return $hasRight;
    }
}
