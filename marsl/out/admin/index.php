<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\includes\Configuration;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\URLLoader;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Main
{
    private Administration $administration;
    private API $api;
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private ModuleRights $moduleRights;
    private Recover $recover;
    private RegisterUser $registerUser;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private RoleAdmin $roleAdmin;
    private Standard $standard;
    private Tags $tags;
    private URLLoader $urlLoader;
    private User $user;

    private string|null $var;

    public function __construct(
        Administration $administration,
        API $api,
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        ModuleRights $moduleRights,
        Recover $recover,
        RegisterUser $registerUser,
        IRequestParametersService $requestParametersService,
        Role $role,
        RoleAdmin $roleAdmin,
        Standard $standard,
        Tags $tags,
        URLLoader $urlLoader,
        User $user
    ) {
        $this->administration = $administration;
        $this->api = $api;
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->moduleRights = $moduleRights;
        $this->recover = $recover;
        $this->registerUser = $registerUser;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->roleAdmin = $roleAdmin;
        $this->standard = $standard;
        $this->tags = $tags;
        $this->urlLoader = $urlLoader;
        $this->user = $user;

        $this->var = null;
    }

    /*
     * Loader for the configuration file and the right timezone.
    */
    public function Main(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        date_default_timezone_set($this->configuration->getTimezone());
    }

    /*
     * Main runner for the current admin interface information.
     * Loads the modules if necessary.
     */
    public function admin(): void
    {
        $roleID = $this->role->getRole();

        $headAdmin = $this->user->isHead();
        $isRoot = $this->user->isRoot();
        $isAdmin = $this->user->isAdmin();

        $content = "";

        if ($isAdmin) {
            $this->var = $this->requestParametersService->fromGet()->getStringParameter("var", "");

            if ($this->var == "logout") {
                $this->user->logout($this->authentication);
                @header("Location: index.php");
            } elseif ($this->var == "module") {
                if ($this->basic->getModule($this->requestParametersService->fromGet()->getStringParameter("module")) != false) {
                    $array = $this->basic->getModule($this->requestParametersService->fromGet()->getStringParameter("module"));
                    $classPath = "\\marsl\\modules\\".$array['class'];
                    $content = ComponentBuilder::buildDependencies()->make($classPath);
                } else {
                    $content = $this->administration;

                }
            } elseif ($this->var == "urlloader") {
                if ($this->authentication->moduleAdminAllowed("urlloader", $roleID)) {
                    $content = $this->urlLoader;
                }
            } elseif ($this->var == "standards") {
                if ($headAdmin) {
                    $content = $this->standard;
                }
            } elseif ($this->var == "modulerights") {
                $content = $this->moduleRights;
            } elseif ($this->var == "role") {
                $content = $this->roleAdmin;
            } elseif ($this->var == "register") {
                $content = $this->registerUser;
            } elseif ($this->var == "tags") {
                $content = $this->tags;
            } elseif ($this->var == "api") {
                $content = $this->api;
            } else {
                $content = $this->administration;

            }
        }

        $title = $this->basic->convertToHTMLEntities($this->basic->getTitle());
        $modules = $this->basic->getModules();

        if ($isAdmin) {
            $userdata = $this->authentication->moduleExtendedAllowed("userdata", $this->role->getRole());
            $userID = $this->user->getID();
            $config = $this->configuration;
            $clusterServer = $config->getClusterServer();
            require_once("template/index.tpl.php");
        } elseif ($this->user->isGuest()) {
            $var = $this->requestParametersService->fromGet()->getStringParameter("var", "");
            if ($var != "") {
                if ($var == "forgot") {
                    $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
                    if ($action != "") {
                        if ($action == "recover") {
                            $this->recover->admin();
                        } else {
                            $init = true;
                            $success = false;
                            if ($action == "success") {
                                $init = false;
                                $success = true;
                                $topic = $this->requestParametersService->fromGet()->getStringParameter("topic");
                            } elseif ($action == "failed") {
                                $init = false;
                                $success = false;
                                $topic = $this->requestParametersService->fromGet()->getStringParameter("topic");
                            }
                            require_once("template/login.forgot.tpl.php");
                        }
                    } else {
                        $init = true;
                        $success = false;
                        require_once("template/login.forgot.tpl.php");
                    }
                } else {
                    $wrongpw = $this->requestParametersService->fromGet()->getStringParameter("wrongpw", "");
                    require_once("template/login.tpl.php");
                }
            } else {
                $wrongpw = $this->requestParametersService->fromGet()->getStringParameter("wrongpw", "");
                require_once("template/login.tpl.php");
            }
        }
        $this->db->close();
    }
}


$main = ComponentBuilder::buildDependencies()->make('marsl\admin\Main');

if ($main instanceof Main) {
    $main->admin();
}
