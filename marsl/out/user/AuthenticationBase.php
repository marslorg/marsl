<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\DB;

class AuthenticationBase
{
    private DB $db;
    private RoleBase $roleBase;

    /**
     * @var array<array<array<string, bool>>>
     */
    private array $locationRights;

    public function __construct(
        DB $db,
        RoleBase $roleBase
    ) {
        $this->db = $db;
        $this->roleBase = $roleBase;

        $curLocationRights = array();
        $result = $this->db->query("SELECT `role`, `location`, `read`, `write`, `extended`, `admin` FROM `rights` WHERE `read`='1' OR `write`='1' OR `extended`='1' OR `admin`='1'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['role'])
                && is_string($row['location'])
                && is_string($row['read'])
                && is_string($row['write'])
                && is_string($row['extended'])
                && is_string($row['admin'])) {
                $roleID = intval(strval($row['role']));
                $location = intval(strval($row['location']));
                $read = boolval(strval($row['read']));
                $write = boolval(strval($row['write']));
                $extended = boolval(strval($row['extended']));
                $admin = boolval(strval($row['admin']));
                if (array_key_exists($roleID, $curLocationRights) && array_key_exists($location, $curLocationRights[$roleID])) {
                    $curLocationRights[$roleID][$location]['read'] = $curLocationRights[$roleID][$location]['read'] || $read;
                    $curLocationRights[$roleID][$location]['write'] = $curLocationRights[$roleID][$location]['write'] || $write;
                    $curLocationRights[$roleID][$location]['extended'] = $curLocationRights[$roleID][$location]['extended'] || $extended;
                    $curLocationRights[$roleID][$location]['admin'] = $curLocationRights[$roleID][$location]['admin'] || $admin;
                } else {
                    $curLocationRights[$roleID][$location]['read'] = $read;
                    $curLocationRights[$roleID][$location]['write'] = $write;
                    $curLocationRights[$roleID][$location]['extended'] = $extended;
                    $curLocationRights[$roleID][$location]['admin'] = $admin;
                }
            }
        }
        $this->locationRights = $curLocationRights;
    }

    /*
     * Evaluates if the role has right for the given location.
     */
    private function evalLocationRights(string $right, int $location, int $roleID): bool
    {
        $hasRight = false;
        $roles = $this->roleBase->getPossibleRoles($roleID);
        $rolesLength = sizeof($roles);
        for ($roleIdx = 0; !$hasRight && $roleIdx < $rolesLength; $roleIdx++) {
            $curRoleID = $roles[$roleIdx];
            if (array_key_exists($curRoleID, $this->locationRights) && array_key_exists($location, $this->locationRights[$curRoleID]) && array_key_exists($right, $this->locationRights[$curRoleID][$location])) {
                $hasRight = $this->locationRights[$curRoleID][$location][$right];
            }
        }
        return $hasRight;
    }

    /*
     * Returns whether the role has read rights on a location.
    */
    public function locationReadAllowed(int $location, int $roleID): bool
    {
        return $this->evalLocationRights("read", $location, $roleID);
    }

    /*
     * Returns whether the role has write rights on a location.
    */
    public function locationWriteAllowed(int $location, int $roleID): bool
    {
        return $this->evalLocationRights("write", $location, $roleID);
    }

    /*
     * Returns whether the role has extended rights on a location.
    */
    public function locationExtendedAllowed(int $location, int $roleID): bool
    {
        return $this->evalLocationRights("extended", $location, $roleID);
    }

    /*
     * Returns whether the role has administrative rights on a location.
    */
    public function locationAdminAllowed(int $location, int $roleID): bool
    {
        return $this->evalLocationRights("admin", $location, $roleID);
    }
}
