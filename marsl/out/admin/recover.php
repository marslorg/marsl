<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\User;

/*
 * Password and user recovery
 */
class Recover
{
    private Basic $basic;
    private IRequestParametersService $requestParametersService;
    private User $user;

    public function __construct(
        Basic $basic,
        IRequestParametersService $requestParametersService,
        User $user
    ) {
        $this->basic = $basic;
        $this->requestParametersService = $requestParametersService;
        $this->user = $user;
    }

    /*
     * Loads the initial password/user recovery dialog or the further steps.
     */
    public function admin(): void
    {
        if ($this->requestParametersService->fromGet()->getStringParameter("status", "") == "success") {
            $init = false;
            $success = true;
            $recover = true;
            $title = $this->basic->convertToHTMLEntities($this->basic->getTitle());
            require_once("template/recover.tpl.php");
        } else {
            $subaction = $this->requestParametersService->fromGet()->getStringParameter("subaction", "");
            if ($subaction != "") {
                if ($subaction == "set") {
                    $time = $this->requestParametersService->fromGet()->getIntegerParameter("time");
                    $uid = $this->requestParametersService->fromGet()->getIntegerParameter("uid");
                    $authParameter = $this->requestParametersService->fromGet()->getStringParameter("auth");
                    if ($time + 172800 >= time()) {
                        $password = $this->user->getPassbyID($uid);
                        $auth_code = md5("admin".$uid.$time.$password);
                        if ($auth_code == $authParameter) {
                            $password = $this->requestParametersService->fromPost()->getStringParameter("password");
                            $password2 = $this->requestParametersService->fromPost()->getStringParameter("password2");
                            if ($password == $password2) {
                                $this->user->setPassword($uid, $password);
                                header("Location: index.php?var=forgot&action=recover&status=success");
                            } else {
                                header("Location: index.php?var=forgot&action=recover&status=failed&uid=".$uid."&time=".$time."&auth=".$authParameter);
                            }
                        } else {
                            header("Location: index.php?var=forgot&action=recover&uid=".$uid."&time=".$time."&auth=".$authParameter);
                        }
                    } else {
                        header("Location: index.php?var=forgot&action=recover&uid=".$uid."&time=".$time."&auth=".$authParameter);
                    }

                } else {
                    $this->recoverBox();
                }
            } else {
                $this->recoverBox();
            }
        }
    }

    /*
     * Loads the box to set a new password.
     */
    private function recoverBox(): void
    {
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
        require_once("template/recover.tpl.php");
    }
}
