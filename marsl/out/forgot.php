<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\includes\Mailer;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\Navigation;
use marsl\user\User;

class Forgot
{
    private Configuration $configuration;
    private DB $db;
    private Mailer $mailer;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private User $user;

    /*
     * Initialize the mailer for the administrative password recovery function.
     */
    public function __construct(
        Configuration $configuration,
        DB $db,
        Mailer $mailer,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        User $user
    ) {
        $this->configuration = $configuration;
        $this->db = $db;
        $this->mailer = $mailer;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->user = $user;
    }

    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        date_default_timezone_set($this->configuration->getTimezone());
        $location = $this->requestParametersService->fromPost()->getIntegerParameter("location", -1);
        if ($this->user->isGuest() || $this->user->isAdmin()) {
            $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            if ($action != "") {
                $uri = $this->navigation->getRelativeURI($location, null, true);
                $uri = $this->configuration->getDomain().$this->configuration->getBasePath().$uri;
                if ($action == "password") {
                    $nickname = $this->requestParametersService->fromPost()->getStringParameter("nickname", "");
                    if ($nickname != "") {
                        if ($this->mailer->sendPasswordMail($location, $nickname)) {
                            header("Location: ".$uri."action=forgot&action2=success&topic=password");
                        } else {
                            header("Location: ".$uri."action=forgot&action2=failed&topic=password");
                        }
                    } else {
                        header("Location: ".$uri."action=forgot&action2=empty");
                    }
                } elseif ($action == "nickname") {
                    $mail = $this->requestParametersService->fromPost()->getStringParameter("mail", "");
                    if ($mail != "") {
                        if ($this->mailer->sendNicknameMail($mail)) {
                            header("Location: ".$uri."action=forgot&action2=success&topic=nickname");
                        } else {
                            header("Location: ".$uri."action=forgot&action2=failed&topic=nickname");
                        }
                    } else {
                        header("Location: ".$uri."action=forgot&action2=empty");
                    }
                } else {
                    header("Location: ".$uri."action=forgot&action2=empty");
                }
            }
        } else {
            header("Location: ".$this->configuration->getDomain().$this->configuration->getBasePath());
        }
        $this->db->close();
    }
}


$forgot = ComponentBuilder::buildDependencies()->make('marsl\Forgot');

if ($forgot instanceof Forgot) {
    $forgot->display();
}
