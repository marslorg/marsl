<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\User;

class API
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        DB $db,
        IRequestParametersService $requestParametersService,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->user = $user;
    }

    public function admin(): void
    {
        if ($this->user->isRoot()) {
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "create") {
                $this->createApp($this->basic);
            }

            if ($this->requestParametersService->fromGet()->getStringParameter("action", "") == "delete") {
                $this->deleteApp();
            }

            $apps = array();
            $result = $this->db->query("SELECT `id`, `name`, `key`, `secret` FROM `app`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['name'])) {
                    array_push($apps, array('id' => $row['id'],'name' => $this->basic->convertToHTMLEntities($row['name']),'key' => $row['key'],'secret' => $row['secret']));
                }
            }

            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once("template/api.tpl.php");
        }
    }

    private function deleteApp(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $id = $this->requestParametersService->fromGet()->getIntegerParameter("id");
            $this->db->query("DELETE FROM `app` WHERE `id`='$id'");
        }
    }

    private function createApp(Basic $basic): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
            $this->requestParametersService->fromPost()->getStringParameter("authToken")
        )) {
            $appName = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("appname"));
            $appKey = $basic->randomHash();
            $appSecret = $basic->randomSHA512();
            $this->db->query("INSERT INTO `app`(`name`,`key`,`secret`) VALUES('$appName','$appKey','$appSecret')");
        }
    }
}
