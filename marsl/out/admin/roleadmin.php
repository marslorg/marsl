<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class RoleAdmin
{
    private DB $db;
    private Authentication $authentication;
    private Basic $basic;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        DB $db,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Set up and change roles.
     */
    public function admin(): void
    {
        if ($this->user->isAdmin()) {
            $roles = array();
            $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            if ($action != "") {
                if ($action == "new") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        if ($this->role->createRole($this->requestParametersService->fromPost()->getStringParameter("role"))) {
                            $slave = (int)$this->db->lastInsertedID();
                            $master = $this->role->getRole();
                            $this->db->query("INSERT INTO `role_editor`(`master`,`slave`) VALUES('$master','$slave')");
                            $roles = $this->buildRoles($slave, $roles);
                        }
                    }
                } elseif ($action == "change") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $roleID = $this->requestParametersService->fromPost()->getIntegerParameter("role");
                        if ($roleID != $this->role->getRole()) {
                            if (in_array($roleID, $this->role->getPossibleRoles($this->role->getRole()))) {
                                $name = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("name"));
                                $this->db->query("UPDATE `role` SET `name`='$name' WHERE `role` = '$roleID'");
                            }
                        }
                    }
                }
            } else {
                $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
                if ($action != "") {
                    if ($action == "del") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $roleID = $this->requestParametersService->fromGet()->getIntegerParameter("role");
                            if ($roleID != $this->role->getRole()) {
                                if (in_array($roleID, $this->role->getPossibleRoles($this->role->getRole()))) {
                                    $ownRole = $this->role->getRole();
                                    $this->db->query("UPDATE `user` SET `role`= (SELECT user FROM stdroles) WHERE `role`='$roleID'");
                                    $this->db->query("UPDATE `role_editor` SET `master`='$ownRole' WHERE `master`='$roleID'");
                                    $this->db->query("DELETE FROM `role_editor` WHERE `slave`='$roleID'");
                                    $this->db->query("DELETE FROM `role` WHERE `role`='$roleID'");
                                }
                            }
                        }
                    }
                }
            }
            $possibleRoles = $this->role->getPossibleRoles($this->role->getRole());
            foreach ($possibleRoles as $possibleRole) {
                $roles = $this->buildRoles($possibleRole, $roles);
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once("template/role.tpl.php");
        }
    }

    /**
     * @param array<int, array<string, int|string>|string> $roles
     *
     * @return array<int, array<string, int|string>|string>
     */
    private function buildRoles(int $possibleRole, array $roles): array
    {
        $result = $this->db->query("SELECT `role`, `name` FROM `role` WHERE `role` = '$possibleRole'");
        while ($row = $this->db->fetchArray($result)) {
            if ($possibleRole != $this->role->getRole()) {
                if (is_string($row['role'])
                    && is_string($row['name'])) {
                    array_push($roles, array('role' => intval(strval($row['role'])),'name' => $this->basic->convertToHTMLEntities($row['name'])));
                }
            }
        }
        return $roles;
    }
}
