<?php

namespace marsl\user;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\DB;

class Role
{
    private DB $db;
    private RoleBase $roleBase;
    private UserBase $userBase;

    private int $currentRole;
    private int $guestRole;

    /**
     * @var array<int, int>
     */
    private array $rolesByUser;
    private int $standardUserRole;

    /**
     * @var array<int, string>
     */
    private array $nameOfRoles;

    /**
     * @var array<string, int>
     */
    private array $idOfRoles;

    /**
     * @var array<int, array<string, string|int>>
     */
    private array $roles;

    /**
     * @var array<int, array<string, string|int>>
     */
    private array $adminRoles;

    public function __construct(
        DB $db,
        RoleBase $roleBase,
        UserBase $userBase
    ) {
        $this->db = $db;
        $this->roleBase = $roleBase;
        $this->userBase = $userBase;

        $this->rolesByUser = array();
        $this->nameOfRoles = array();
        $this->idOfRoles = array();
        $this->roles = array();
        $this->adminRoles = array();
        $this->currentRole = -1;
        $this->guestRole = -1;
        $this->standardUserRole = -1;
    }

    /*
     * Get the current user role.
     */
    public function getRole(): int
    {
        if ($this->currentRole == -1) {
            if ($this->userBase->isGuest()) {
                $this->currentRole =  $this->getGuestRole();
            } else {
                $session = $this->db->escapeString($this->userBase->getSession());
                $result = $this->db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `sessionid`='$session' AND `deleted`='0'");
                $role = -1;
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['role'])) {
                        $role = intval(strval($row['role']));
                    }
                }
                $this->currentRole = $role;
            }
        }
        return $this->currentRole;
    }

    /*
     * Get the standard guest role.
     */
    public function getGuestRole(): int
    {
        if ($this->guestRole == -1) {
            $result = $this->db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`guest`");
            $role = -1;
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['role'])) {
                    $role = intval(strval($row['role']));
                }
            }
            $this->guestRole = $role;
        }
        return $this->guestRole;
    }

    /*
     * Get a role of a user.
     */
    public function getRolebyUser(int $user): int
    {
        $role = -1;
        if (!array_key_exists($user, $this->rolesByUser)) {
            $result = $this->db->query("SELECT `role` FROM `role` JOIN `user` USING(`role`) WHERE `user`='$user' AND `deleted`='0'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['role'])) {
                    $role = intval(strval($row['role']));
                }
            }
            $this->rolesByUser[$user] = $role;
        } else {
            $role = $this->rolesByUser[$user];
        }
        return $role;
    }

    /*
     * Get the standard user role.
     */
    public function getUserRole(): int
    {
        if ($this->standardUserRole == -1) {
            $result = $this->db->query("SELECT `role` FROM `role` JOIN `stdroles` ON `role`=`user`");
            $role = -1;
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['role'])) {
                    $role = intval(strval($row['role']));
                }
            }
            $this->standardUserRole = $role;
        }
        return $this->standardUserRole;
    }

    /*
     * Get the name of a role.
     */
    public function getNamebyID(int $id): string
    {
        $name = "";
        if (!array_key_exists($id, $this->nameOfRoles)) {
            $result = $this->db->query("SELECT `name` FROM `role` WHERE `role`='$id'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['name'])) {
                    $name = $row['name'];
                }
            }
            $this->nameOfRoles[$id] = $name;
        } else {
            $name = $this->nameOfRoles[$id];
        }
        return $name;
    }

    /*
     * Get the ID of a role by a given name.
     */
    public function getIDbyName(string $name): int
    {
        $role = -1;
        if (!array_key_exists($name, $this->idOfRoles)) {
            $name = $this->db->escapeString($name);
            $result = $this->db->query("SELECT `role` FROM `role` WHERE `name`='$name'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['role'])) {
                    $role = intval(strval($row['role']));
                }
            }
            $this->idOfRoles[$name] = $role;
        } else {
            $role = $this->idOfRoles[$name];
        }
        return $role;
    }

    /*
     * Set the rights for a module.
     */
    public function setModuleRights(int $role, string $module, bool $read, bool $write, bool $extended, bool $admin): void
    {
        $module = $this->db->escapeString($module);
        $read = $read ? 1 : 0;
        $write = $write ? 1 : 0;
        $extended = $extended ? 1 : 0;
        $admin = $admin ? 1 : 0;
        if ($this->db->isExisting("SELECT `role` FROM `rights_module` WHERE `role`= '$role' AND `module`='$module' LIMIT 1")) {
            $this->db->query("UPDATE `rights_module` SET `read` = '$read', `write` = '$write', `extended` = '$extended', `admin` = '$admin' WHERE `role` = '$role' AND `module` = '$module'");
        } else {
            $this->db->query("INSERT INTO `rights_module`(`role`,`module`,`read`,`write`,`extended`,`admin`) VALUES('$role','$module','$read','$write','$extended','$admin')");
        }
    }

    /*
     * Set the rights for a location.
     */
    public function setRights(int $role, int $location, bool $read, bool $write, bool $extended, bool $admin): void
    {
        $read = $read ? 1 : 0;
        $write = $write ? 1 : 0;
        $extended = $extended ? 1 : 0;
        $admin = $admin ? 1 : 0;
        if ($this->db->isExisting("SELECT `role` FROM `rights` WHERE `role`='$role' AND `location`='$location' LIMIT 1")) {
            $this->db->query("UPDATE `rights` SET `read` = '$read', `write` = '$write', `extended`='$extended', `admin` = '$admin' WHERE `role`='$role' AND `location`='$location'");
        } else {
            $this->db->query("INSERT INTO `rights`(`role`,`location`,`read`,`write`,`extended`,`admin`) VALUES('$role','$location','$read','$write','$extended','$admin')");
        }
    }

    /*
     * Create a new role.
     */
    public function createRole(string $name): bool
    {
        $name = $this->db->escapeString($name);
        if (!$this->db->isExisting("SELECT `role` FROM `role` WHERE `name`='$name' LIMIT 1")) {
            $this->db->query("INSERT INTO `role`(`name`) VALUES('$name')");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the slave roles for which the given role is the root.
     * @return array<int, int>
     */
    public function getPossibleRoles(int $role): array
    {
        return $this->roleBase->getPossibleRoles($role);
    }

    /**
     * Return whether a given role is a master of another given role.
     *
     * @param int $master
     * @param int $slave
     * @param array<int, int> $possibleRoles
     */
    public function isMaster(int $master, int $slave, array $possibleRoles): bool
    {
        if ($master != $slave) {
            foreach ($possibleRoles as $possibleRole) {
                if ($slave == $possibleRole) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get all roles.
     *
     * @return array<int, array<string, string|int>>
     */
    public function getRoles(): array
    {
        if (count($this->roles) == 0) {
            $result = $this->db->query("SELECT `role`, `name` FROM `role`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['role'])
                    && is_string($row['name'])) {
                    $row['role'] = intval(strval($row['role']));
                    array_push($this->roles, $row);
                }
            }
        }
        // @phpstan-ignore return.type
        return $this->roles;
    }

    /**
     * Get all administrative roles.
     *
     * @return array<int, array<string, string|int>>
     */
    public function getAdminRoles(): array
    {
        if (count($this->adminRoles) == 0) {
            $allRoles = $this->getRoles();
            foreach ($allRoles as $role) {
                $roleID = $role['role'];
                $location = $this->db->isExisting("SELECT `role` FROM `rights` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
                $module = $this->db->isExisting("SELECT `role` FROM `rights_module` WHERE `role` = '$roleID' AND `admin` = '1' LIMIT 1");
                $master = $this->db->isExisting("SELECT `master` FROM `role_editor` WHERE `master` = '$roleID' LIMIT 1");
                if ($location || $module || $master) {
                    array_push($this->adminRoles, $role);
                }
            }
        }
        return $this->adminRoles;
    }
}
