<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\User;

class AdminLogin
{
    private Authentication $authentication;
    private Configuration $configuration;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private User $user;

    /*
     * Login an admin user and redirect to the admin panel.
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
        if ($this->requestParametersService->fromPost()->getStringParameter("action", "") != "") {
            $rightpw = $this->user->login(
                $this->requestParametersService->fromPost()->getStringParameter("nickname"),
                $this->requestParametersService->fromPost()->getStringParameter("password"),
                $this->authentication
            );
            $this->db->close();
            if ($rightpw) {
                header("Location: admin/index.php");
            } else {
                header("Location: admin/index.php?wrongpw=1");
            }
        } else {
            header("Location: ./");
        }
    }
}

$adminLogin = ComponentBuilder::buildDependencies()->make('marsl\AdminLogin');

if ($adminLogin instanceof AdminLogin) {
    $adminLogin->display();
}
