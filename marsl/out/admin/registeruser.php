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

class RegisterUser
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
     * Dialog for administrators to register a new user.
     * No double-opt-in required.
     */
    public function admin(): void
    {
        if ($this->user->isAdmin()) {
            $possibleRoles = $this->role->getPossibleRoles($this->role->getRole());
            $userRole = true;
            if (!in_array($this->role->getUserRole(), $possibleRoles)) {
                $userRole = false;
            }
            $passwordProof = true;
            $emailProof = true;
            $registered = false;
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    if ($this->requestParametersService->fromPost()->getStringParameter("password") == $this->requestParametersService->fromPost()->getStringParameter("password2")) {
                        if ($this->basic->checkMail($this->requestParametersService->fromPost()->getStringParameter("email"))) {
                            if ($this->user->register(
                                $this->requestParametersService->fromPost()->getStringParameter("nickname"),
                                $this->requestParametersService->fromPost()->getStringParameter("password"),
                                $this->requestParametersService->fromPost()->getStringParameter("email"),
                                $this->authentication,
                                false
                            )) {
                                $email = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("email"));
                                $this->db->query("UPDATE `email` SET `confirmed`='1' WHERE `email`='$email'");
                                $registered = true;
                                $userID = $this->user->getIDbyName($this->requestParametersService->fromPost()->getStringParameter("nickname"));
                                $roleID = $this->requestParametersService->fromPost()->getIntegerParameter("role", -1);
                                if ($roleID != -1) {
                                    if (($roleID != $this->role->getRole()) && (in_array($roleID, $possibleRoles))) {
                                        $this->user->changeRole($userID, $roleID);
                                    }
                                }
                            } else {
                                $registered = false;
                            }
                        } else {
                            $emailProof = false;
                        }
                    } else {
                        $passwordProof = false;
                    }
                }
            }
            $roles = array();
            foreach ($possibleRoles as $possibleRole) {
                if ($possibleRole != $this->role->getRole()) {
                    $result = $this->db->query("SELECT `role`, `name` FROM `role` WHERE `role`='$possibleRole'");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['name'])) {
                            array_push($roles, array('role' => $row['role'],'name' => $this->basic->convertToHTMLEntities($row['name'])));
                        }
                    }
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once("template/register.tpl.php");
        }
    }
}
