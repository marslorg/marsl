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

class Standard
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
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
     * Set the standard roles for guest users and newly registered users in the guest registration dialog.
     */
    public function admin(): void
    {
        if ($this->user->isAdmin()) {
            if ($this->user->isHead()) {
                $possibleRoles = $this->role->getPossibleRoles($this->role->getRole());
                if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "change") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $stdUser = $this->requestParametersService->fromPost()->getIntegerParameter("user", -1);
                        $guest = $this->requestParametersService->fromPost()->getIntegerParameter("guest", -1);
                        if (($this->role->getRole() != $stdUser) && ($this->role->getRole() != $guest)) {
                            if (in_array($stdUser, $possibleRoles) && in_array($guest, $possibleRoles)) {
                                if ($this->db->isExisting("SELECT `user` FROM `stdroles` LIMIT 1")) {
                                    $this->db->query("UPDATE `stdroles` SET `guest`='$guest', `user`='$stdUser'");
                                } else {
                                    $this->db->query("INSERT INTO `stdroles`(`guest`,`user`) VALUES('$guest','$stdUser')");
                                }
                            }
                        }
                    }
                }
                $stdUser = -1;
                $guest = -1;
                $result = $this->db->query("SELECT `user`, `guest` FROM `stdroles`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['user']) && is_string($row['guest'])) {
                        $stdUser = intval($row['user']);
                        $guest = intval($row['guest']);
                    }
                }
                $roles = array();
                foreach ($possibleRoles as $possibleRole) {
                    $result = $this->db->query("SELECT `role`, `name` FROM `role` WHERE `role`='$possibleRole'");
                    while ($row = $this->db->fetchArray($result)) {
                        if ($this->role->getRole() != $row['role']) {
                            if (is_string($row['role'])
                                && is_string($row['name'])) {
                                array_push($roles, array('role' => intval(strval($row['role'])), 'name' => $this->basic->convertToHTMLEntities($row['name'])));
                            }
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once("template/standard.tpl.php");
            }
        }
    }
}
