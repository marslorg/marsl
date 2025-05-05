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

class ModuleRights
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
     * The dialog to set up and change the module rights.
     */
    public function admin(): void
    {
        if ($this->user->isAdmin()) {
            $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
            if ($action == "") {
                $modules = array();
                $result = $this->db->query("SELECT `file`, `name` FROM `module`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['file'])
                        && $this->authentication->moduleAdminAllowed($row['file'], $this->role->getRole())
                        && $this->authentication->moduleExtendedAllowed($row['file'], $this->role->getRole())
                        && $this->authentication->moduleWriteAllowed($row['file'], $this->role->getRole())
                        && $this->authentication->moduleReadAllowed($row['file'], $this->role->getRole())) {
                        array_push($modules, array('file' => $row['file'],'name' => $row['name']));
                    }
                }
                require_once("template/modulerights.tpl.php");
            } elseif ($action == "role") {
                $module = $this->basic->getModule($this->requestParametersService->fromGet()->getStringParameter("module"));

                if (!is_bool($module)) {
                    $moduleID = $this->db->escapeString($module['file']);
                    $name = $this->basic->convertToHTMLEntities($module['name']);
                    if ($this->authentication->moduleAdminAllowed($moduleID, $this->role->getRole())
                        && $this->authentication->moduleExtendedAllowed($moduleID, $this->role->getRole())
                        && $this->authentication->moduleWriteAllowed($moduleID, $this->role->getRole())
                        && $this->authentication->moduleReadAllowed($moduleID, $this->role->getRole())) {
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
                                        $this->role->setModuleRights($roleID, $moduleID, $read, $write, $extended, $admin);
                                    }
                                }
                            }
                        }
                        $rights = array();
                        foreach ($roles as $roleID) {
                            if ($roleID != $this->role->getRole()) {
                                if ($this->db->isExisting("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID' LIMIT 1")) {
                                    $result = $this->db->query("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights_module` WHERE `role`='$roleID' AND `module`='$moduleID'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['role'])) {
                                            $roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID(intval($row['role'])));
                                            array_push($rights, array('name' => $roleName,'role' => intval($row['role']),'read' => $row['read'],'write' => $row['write'],'extended' => $row['extended'],'admin' => $row['admin']));
                                        }
                                    }
                                } else {
                                    $roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($roleID));
                                    array_push($rights, array('name' => $roleName,'role' => $roleID,'read' => "0",'write' => "0",'extended' => "0",'admin' => "0"));
                                }
                            }
                        }

                    }
                    $authTime = time();
                    $authToken = $this->authentication->getToken($authTime);
                    require_once("template/modulerights.role.tpl.php");
                }
            }
        }
    }
}
