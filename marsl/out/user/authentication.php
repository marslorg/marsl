<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;

class Authentication
{
    private AuthenticationBase $authenticationBase;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private RoleBase $roleBase;
    private UserBase $userBase;

    /**
     * @var array<array<array<string, bool>>>
     */
    private array $moduleRights;

    public function __construct(
        AuthenticationBase $authenticationBase,
        DB $db,
        IRequestParametersService $requestParametersService,
        RoleBase $roleBase,
        UserBase $userBase
    ) {
        $this->authenticationBase = $authenticationBase;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->roleBase = $roleBase;
        $this->userBase = $userBase;

        $curModuleRights = array();
        $moduleResult = $this->db->query("SELECT `role`, `module`, `read`, `write`, `extended`, `admin`  FROM `rights_module` WHERE `read`='1' OR `write`='1' OR `extended`='1' OR `admin`='1'");
        while ($moduleRow = $this->db->fetchArray($moduleResult)) {
            if (is_string($moduleRow['role'])
                && is_string($moduleRow['module'])
                && is_string($moduleRow['read'])
                && is_string($moduleRow['write'])
                && is_string($moduleRow['extended'])
                && is_string($moduleRow['admin'])) {
                $roleID = intval(strval($moduleRow['role']));
                $module = $moduleRow['module'];
                $read = boolval(strval($moduleRow['read']));
                $write = boolval(strval($moduleRow['write']));
                $extended = boolval(strval($moduleRow['extended']));
                $admin = boolval(strval($moduleRow['admin']));
                if (array_key_exists($roleID, $curModuleRights) && array_key_exists($module, $curModuleRights[$roleID])) {
                    $curModuleRights[$roleID][$module]['read'] = $curModuleRights[$roleID][$module]['read'] || $read;
                    $curModuleRights[$roleID][$module]['write'] = $curModuleRights[$roleID][$module]['write'] || $write;
                    $curModuleRights[$roleID][$module]['extended'] = $curModuleRights[$roleID][$module]['extended'] || $extended;
                    $curModuleRights[$roleID][$module]['admin'] = $curModuleRights[$roleID][$module]['admin'] || $admin;
                } else {
                    $curModuleRights[$roleID][$module]['read'] = $read;
                    $curModuleRights[$roleID][$module]['write'] = $write;
                    $curModuleRights[$roleID][$module]['extended'] = $extended;
                    $curModuleRights[$roleID][$module]['admin'] = $admin;
                }
            }
        }
        $this->moduleRights = $curModuleRights;
    }

    /*
     * Evaluates the rights matrix.
     */
    private function evalModuleRights(string $right, string $module, int $roleID): bool
    {
        $hasRight = false;
        $roles = $this->roleBase->getPossibleRoles($roleID);
        $rolesLength = sizeof($roles);
        for ($roleIdx = 0; !$hasRight && $roleIdx < $rolesLength; $roleIdx++) {
            $curRoleID = $roles[$roleIdx];
            if (array_key_exists($curRoleID, $this->moduleRights) && array_key_exists($module, $this->moduleRights[$curRoleID]) && array_key_exists($right, $this->moduleRights[$curRoleID][$module])) {
                $hasRight = $this->moduleRights[$curRoleID][$module][$right];
            }
        }
        return $hasRight;
    }

    /*
     * Returns whether the role has read rights on a module.
     */
    public function moduleReadAllowed(string $module, int $roleID): bool
    {
        return $this->evalModuleRights("read", $module, $roleID);
    }

    /*
     * Returns whether the role has write rights on a module.
    */
    public function moduleWriteAllowed(string $module, int $roleID): bool
    {
        return $this->evalModuleRights("write", $module, $roleID);
    }

    /*
     * Returns whether the role has extended rights on a module.
    */
    public function moduleExtendedAllowed(string $module, int $roleID): bool
    {
        return $this->evalModuleRights("extended", $module, $roleID);
    }

    /*
     * Returns whether the role has administrative rights on a module.
    */
    public function moduleAdminAllowed(string $module, int $roleID): bool
    {
        return $this->evalModuleRights("admin", $module, $roleID);
    }

    /*
     * Returns whether the role has read rights on a location.
    */
    public function locationReadAllowed(int $location, int $roleID): bool
    {
        return $this->authenticationBase->locationReadAllowed($location, $roleID);
    }

    /*
     * Returns whether the role has write rights on a location.
    */
    public function locationWriteAllowed(int $location, int $roleID): bool
    {
        return $this->authenticationBase->locationWriteAllowed($location, $roleID);
    }

    /*
     * Returns whether the role has extended rights on a location.
    */
    public function locationExtendedAllowed(int $location, int $roleID): bool
    {
        return $this->authenticationBase->locationExtendedAllowed($location, $roleID);
    }

    /*
     * Returns whether the role has administrative rights on a location.
    */
    public function locationAdminAllowed(int $location, int $roleID): bool
    {
        return $this->authenticationBase->locationAdminAllowed($location, $roleID);
    }

    public function isAppAllowed(): bool
    {
        $appIsAuthenticated = false;

        $phpAuthUser = $this->requestParametersService->fromServer()->getStringParameter("PHP_AUTH_USER", "");
        $phpAuthPw = $this->requestParametersService->fromServer()->getStringParameter("PHP_AUTH_PW", "");
        $requestMethod = $this->requestParametersService->fromServer()->getStringParameter("REQUEST_METHOD", "");

        if ($phpAuthUser != "") {
            if ($phpAuthPw != "") {
                $appKey = $this->db->escapeString($phpAuthUser);
                $appSecret = "";
                $result = $this->db->query("SELECT `secret` FROM `app` WHERE `key`='$appKey'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['secret'])) {
                        $appSecret = $row['secret'];
                    }
                }

                $message = $requestMethod.$this->requestParametersService->fromServer()->getStringParameter("HTTP_HOST", "").$this->requestParametersService->fromServer()->getStringParameter("REQUEST_URI", "");
                if ($requestMethod == "POST" || $requestMethod == "PUT") {
                    $rawData = file_get_contents("php://input");
                    $message = $message.$rawData;
                }

                $hash = hash_hmac("sha512", $message, $appSecret);

                if ($hash == $phpAuthPw) {
                    $appIsAuthenticated = true;
                }
            }
        }

        return $appIsAuthenticated;
    }

    /*
     * Get a token to prevent CSRF attacks.
     */
    public function getToken(int $time): string
    {
        $session = $this->userBase->getSession();
        $userID = $this->userBase->getID();
        $password = $this->userBase->getPassbyID($userID);
        $token = md5($session.$userID.$password.$time);
        return $token;
    }

    /*
     * Check a token to prevent CSRF attacks.
     */
    public function checkToken(int $time, string $token): bool
    {
        $session = $this->userBase->getSession();
        $userID = $this->userBase->getID();
        $password = $this->userBase->getPassbyID($userID);
        $proof = md5($session.$userID.$password.$time);
        return ($token == $proof);
    }

}
