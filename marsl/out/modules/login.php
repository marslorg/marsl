<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Login implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    public function display(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = "";
        if ($pageID > -1) {
            $location = $pageID;
        } else {
            $location = $this->basic->getHomeLocation();
        }

        $uri = $this->navigation->getRelativeURI($location, null, true);
        $forgotURI = $uri."action=forgot";

        if ($this->user->isGuest() || $this->user->isAdmin()) {
            if ($this->authentication->moduleReadAllowed("login", $this->role->getRole())
                && $this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
                $action2 = $this->requestParametersService->fromGet()->getStringParameter("action2", "");
                if ($action != "") {
                    if ($action == "forgot") {
                        if ($action2 != "") {
                            if ($action2 == "recover") {
                                $this->recover();
                            } else {
                                $init = true;
                                $success = false;
                                $topic = "";
                                if ($action2 == "success") {
                                    $init = false;
                                    $success = true;
                                    $topic = $this->requestParametersService->fromGet()->getStringParameter("topic", "");
                                } elseif ($action2 == "failed") {
                                    $init = false;
                                    $success = false;
                                    $topic = $this->requestParametersService->fromGet()->getStringParameter("topic", "");
                                }
                                require_once(dirname(__FILE__)."/../template/login.forgot.tpl.php");
                            }
                        } else {
                            $init = true;
                            $success = false;
                            $topic = "";
                            require_once(dirname(__FILE__)."/../template/login.forgot.tpl.php");
                        }
                    } else {
                        $wrongpw = $this->requestParametersService->fromGet()->getStringParameter("wrongpw", "");
                        require_once(dirname(__FILE__)."/../template/login.tpl.php");
                    }
                } else {
                    $wrongpw = $this->requestParametersService->fromGet()->getStringParameter("wrongpw", "");
                    require_once(dirname(__FILE__)."/../template/login.tpl.php");
                }
            }

        }
    }

    public function admin(): void
    {
        echo "Nichts zu tun hier.";
    }

    public function isSearchable(): bool
    {
        return false;
    }

    /*
     * Interface method stub.
     */
    public function getSearchList(): array
    {
        return array();
    }

    public function search(string $query, string $type): void
    {
    }

    public function isTaggable(): bool
    {
        return false;
    }

    /*
     * Interface method stub.
    */
    public function getTagList(): array
    {
        return array();
    }

    public function addTags(string $tagString, string $type, int $news): void
    {

    }

    public function getTagString(string $type, int $news): string|null
    {
        return null;
    }

    public function getTags(string $type, int $news): array
    {
        return array();
    }

    private function recover(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = "";
        if ($pageID > -1) {
            $location = $pageID;
        } else {
            $location = $this->basic->getHomeLocation();
        }

        $baseURI = $this->navigation->getRelativeURI($location, null, true);
        $baseForgotURI = $baseURI."action=forgot";
        $baseRecoverURI = $baseForgotURI."&action2=recover";

        if ($this->requestParametersService->fromGet()->getStringParameter("status", "") == "success") {
            $init = false;
            $success = true;
            $recover = true;
            $title = $this->basic->convertToHTMLEntities($this->basic->getTitle());
            require_once(dirname(__FILE__)."/../template/recover.tpl.php");
        } else {
            $subaction = $this->requestParametersService->fromGet()->getStringParameter("subaction", "");
            if ($subaction != "") {
                if ($subaction == "set") {
                    $time = $this->requestParametersService->fromGet()->getIntegerParameter("time");
                    $authParameter = $this->requestParametersService->fromGet()->getStringParameter("auth");
                    $uid = $this->requestParametersService->fromGet()->getIntegerParameter("uid");

                    if ($time + 172800 >= time()) {
                        $password = $this->user->getPassbyID($uid);
                        $auth_code = md5("admin".$uid.$time.$password);
                        if ($auth_code == $authParameter) {
                            $password = $this->requestParametersService->fromPost()->getStringParameter("password");
                            $password2 = $this->requestParametersService->fromPost()->getStringParameter("password2");
                            if ($password == $password2) {
                                $this->user->setPassword($uid, $password);
                                header("Location: ".$baseRecoverURI."&status=success");
                            } else {
                                header("Location: ".$baseRecoverURI."&status=failed&uid=".$uid."&time=".$time."&auth=".$authParameter);
                            }
                        } else {
                            header("Location: ".$baseRecoverURI."&uid=".$uid."&time=".$time."&auth=".$authParameter);
                        }
                    } else {
                        header("Location: ".$baseRecoverURI."&uid=".$uid."&time=".$time."&auth=".$authParameter);
                    }

                } else {
                    $this->recoverBox();
                }
            } else {
                $this->recoverBox();
            }
        }
    }

    private function recoverBox(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = "";
        if ($pageID > -1) {
            $location = $pageID;
        } else {
            $location = $this->basic->getHomeLocation();
        }

        $title = $this->basic->convertToHTMLEntities($this->basic->getTitle());
        $time = $this->requestParametersService->fromGet()->getIntegerParameter("time");
        $recover = false;
        $uid = "";
        $authParameter = "";
        $init = true;
        $success = false;
        if ($this->requestParametersService->fromGet()->getStringParameter("status", "") == "failed") {
            $init = false;
            $success = false;
        }
        if ($time + 172800 >= time()) {
            $uid = $this->requestParametersService->fromGet()->getIntegerParameter("uid");
            $password = $this->user->getPassbyID($uid);
            $auth_code = md5("admin".$uid.$time.$password);
            $authParameter = $this->requestParametersService->fromGet()->getStringParameter("auth");
            if ($auth_code == $authParameter) {
                $recover = true;
            }
        }

        $baseURI = $this->navigation->getRelativeURI($location, null, true);
        $baseForgotURI = $baseURI."action=forgot";
        $baseRecoverURI = $baseForgotURI."&action2=recover";
        $baseRecoverSetURI = $baseRecoverURI."&subaction=set&uid=".$uid."&time=".$time."&auth=".$authParameter;

        require_once(dirname(__FILE__)."/../template/recover.tpl.php");
    }

    public function displayTag(): void
    {
    }

    public function getImage(): string|null
    {
        return null;
    }

    public function getTitle(): string|null
    {
        return null;
    }

    public function getRestfulURIPartFromOldURL(): string|null
    {
        return null;
    }

    public function getOldURIPartFromRestfulURL(): string|null
    {
        return null;
    }
}
