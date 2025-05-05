<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\DB;

class RoleBase
{
    private DB $db;

    /**
     * @var array<int, list<int>>
     */
    private array $possibleRoles;

    public function __construct(
        DB $db
    ) {
        $this->db = $db;

        $this->possibleRoles = array();
    }

    /**
     * Get the slave roles for which the given role is the root.
     * @return array<int, int>
     */
    public function getPossibleRoles(int $role): array
    {
        $roles = array();
        if (array_key_exists($role, $this->possibleRoles)) {
            $roles = $this->possibleRoles[$role];
        } else {
            $result = $this->db->query("SELECT `slave` FROM `role_editor` WHERE `master`='$role'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['slave'])) {
                    $slaves = $this->getPossibleRoles(intval(strval($row['slave'])));
                    foreach ($slaves as $slave) {
                        array_push($roles, $slave);
                    }
                }
            }
            array_push($roles, $role);
            $this->possibleRoles[$role] = $roles;
        }
        return $roles;
    }
}
