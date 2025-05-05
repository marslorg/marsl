<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\User;

class Login
{
    private Authentication $authentication;
    private Configuration $configuration;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private User $user;

    /*
     * Login a normal user.
     */
    public function __construct(
        Authentication $authentication,
        Configuration $configuration,
        DB $db,
        IRequestParametersService $requestParametersService,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->user = $user;
    }

    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        date_default_timezone_set($this->configuration->getTimezone());
        $httpReferer = $this->requestParametersService->fromServer()->getStringParameter("HTTP_REFERER", "");
        if ($httpReferer != "") {
            $rightpw = $this->user->login(
                $this->requestParametersService->fromPost()->getStringParameter("nickname"),
                $this->requestParametersService->fromPost()->getStringParameter("password"),
                $this->authentication
            );
            $this->db->close();
            if ($rightpw) {
                header("Location: ./");
            } else {
                header("Location: ".$httpReferer."&wrongpw=1");
            }
        } else {
            header("Location: ./");
        }
    }
}

$login = ComponentBuilder::buildDependencies()->make('marsl\Login');

if ($login instanceof Login) {
    $login->display();
}
