<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\includes\Mailer;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\User;

class AdminForgot
{
    private Configuration $configuration;
    private DB $db;
    private Mailer $mailer;
    private IRequestParametersService $requestParametersService;
    private User $user;

    /*
     * Initialize the mailer for the administrative password recovery function.
     */
    public function __construct(
        Configuration $configuration,
        DB $db,
        Mailer $mailer,
        IRequestParametersService $requestParametersService,
        User $user
    ) {
        $this->configuration = $configuration;
        $this->db = $db;
        $this->mailer = $mailer;
        $this->requestParametersService = $requestParametersService;
        $this->user = $user;
    }

    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        date_default_timezone_set($this->configuration->getTimezone());
        if ($this->user->isGuest()) {
            $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            if ($action != "") {
                if ($action == "password") {
                    $nickname = $this->requestParametersService->fromPost()->getStringParameter("nickname", "");
                    if ($nickname != "") {
                        if ($this->mailer->sendPasswordMail(-1, $nickname)) {
                            header("Location: admin/index.php?var=forgot&action=success&topic=password");
                        } else {
                            header("Location: admin/index.php?var=forgot&action=failed&topic=password");
                        }
                    } else {
                        header("Location: admin/index.php?var=forgot&action=empty");
                    }
                } elseif ($action == "nickname") {
                    $mail = $this->requestParametersService->fromPost()->getStringParameter("mail", "");
                    if ($mail != "") {
                        if ($this->mailer->sendNicknameMail($mail)) {
                            header("Location: admin/index.php?var=forgot&action=success&topic=nickname");
                        } else {
                            header("Location: admin/index.php?var=forgot&action=failed&topic=nickname");
                        }
                    } else {
                        header("Location: admin/index.php?var=forgot&action=empty");
                    }
                } else {
                    header("Location: admin/index.php?var=forgot&action=empty");
                }
            }
        } else {
            if ($this->user->isAdmin()) {
                header("Location: admin/index.php");
            } else {
                header("Location: ./");
            }
        }
        $this->db->close();
    }
}

$adminForgot = ComponentBuilder::buildDependencies()->make('marsl\AdminForgot');

if ($adminForgot instanceof AdminForgot) {
    $adminForgot->display();
}
