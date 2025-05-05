<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");


use marsl\ComponentBuilder;
use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class EditPicture
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
     * Runs the edit picture dialog for changing the subtitles of a gallery picture.
     */
    public function admin(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        $title = $this->basic->getTitle();
        $new = true;
        if ($this->user->isAdmin()
            && $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole())
            && $this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole())) {
            $picture = $this->requestParametersService->fromGet()->getIntegerParameter("id");
            $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            if ($action != "") {
                $new = false;
                if ($action == "send"
                    && $this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                    $result = $this->db->query("SELECT `location` FROM `picture` JOIN `album` USING(`album`) WHERE `picture`='$picture'");
                    while ($row = $this->db->fetchArray($result)) {
                        $subtitle = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("subtitle", "")));
                        $this->db->query("UPDATE `picture` SET `subtitle`='$subtitle' WHERE `picture`='$picture'");
                    }
                }
            }
            $result = $this->db->query("SELECT `subtitle`, `location`, `folder`, `filename`, `picture` FROM `picture` JOIN `album` USING(`album`) WHERE `picture`='$picture'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                    && ($row['subtitle'] == null || is_string($row['subtitle']))
                    && is_string($row['folder'])
                    && is_string($row['filename'])
                    && is_string($row['picture'])) {
                    if ($this->authentication->locationAdminAllowed(intval(strval($row['location'])), $this->role->getRole())) {
                        $subtitle = is_string($row['subtitle']) ? $row['subtitle'] : "";
                        $path = "../albums/".$this->basic->convertToHTMLEntities($row['folder']).$this->basic->convertToHTMLEntities($row['filename']);
                        $picture = intval(strval($row['picture']));
                        $authTime = time();
                        $authToken = $this->authentication->getToken($authTime);
                        require_once("template/gallery.editpicture.tpl.php");
                    }
                }
            }

        }
        $this->db->close();
    }
}


$editPicture = ComponentBuilder::buildDependencies()->make('marsl\admin\EditPicture');

if ($editPicture instanceof EditPicture) {
    $editPicture->admin();
}
