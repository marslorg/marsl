<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Register implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        DB $db,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    public function display(): void
    {
        $location = -1;
        $pageID = $this->navigation->getPageID();
        if ($pageID > -1) {
            $location = $pageID;
        } else {
            $location = $this->basic->getHomeLocation();
        }
        $uri = $this->navigation->getRelativeURI($location, null, false);
        $nickname = "";
        $mail = "";
        $mail2 = "";
        $success = false;
        $captcha = false;
        $mailFailure = false;
        $passwordFailure = false;
        $nicknameFailure = false;
        if ($this->user->isGuest() || $this->user->isAdmin()) {
            if ($this->authentication->moduleReadAllowed("register", $this->role->getRole()) && $this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                if ($this->authentication->moduleWriteAllowed("register", $this->role->getRole()) && $this->authentication->locationWriteAllowed($location, $this->role->getRole())) {
                    if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "send") {
                        $mail = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("mail", ""));
                        $mail2 = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("mail2", ""));
                        if (($mail == $mail2) && ($this->basic->checkMail($mail))) {
                            $password = $this->requestParametersService->fromPost()->getStringParameter("password");
                            $password2 = $this->requestParametersService->fromPost()->getStringParameter("password2");
                            if ($password == $password2) {
                                $nickname = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("nickname"));
                                if ($this->user->register($nickname, $password, $mail, $this->authentication, true)) {
                                    $success = true;
                                } else {
                                    $nicknameFailure = true;
                                }
                            } else {
                                $passwordFailure = true;
                            }
                        } else {
                            $mailFailure = true;
                        }
                        if (!$success) {
                            $nickname = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("nickname"));
                            $mail = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("mail", ""));
                            $mail2 = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("mail2", ""));
                        } else {
                            $nickname = "";
                            $mail = "";
                            $mail2 = "";
                        }
                    }
                }
                require_once(dirname(__FILE__)."/../template/register.tpl.php");
            }
        }
    }

    public function admin(): void
    {
        if ($this->authentication->moduleAdminAllowed("register", $this->role->getRole())) {
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "send"
                && $this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                $newID = $this->requestParametersService->fromPost()->getIntegerParameter("location");
                if ($this->db->isExisting("SELECT `id` FROM `registration_tos` LIMIT 1")) {
                    $this->db->query("UPDATE `registration_tos` SET `id`='$newID'");
                } else {
                    $this->db->query("INSERT INTO `registration_tos`(`id`) VALUES('$newID')");
                }
            }
            $id = "";
            $result = $this->db->query("SELECT `id` FROM `registration_tos`");
            while ($row = $this->db->fetchArray($result)) {
                $id = $row['id'];
            }

            $links = array();

            $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `type` IN ('1','2')");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                    && is_string($row['name'])) {
                    $guestRole = $this->role->getGuestRole();
                    $location = intval(strval($row['id']));
                    if ($this->authentication->locationReadAllowed($location, $guestRole)) {
                        $name = $this->basic->convertToHTMLEntities($row['name']);
                        array_push($links, array('id' => $location, 'name' => $name));
                    }
                }
            }
        }
        $authTime = time();
        $authToken = $this->authentication->getToken($authTime);
        require_once(dirname(__FILE__)."/../admin/template/register.tos.tpl.php");
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
