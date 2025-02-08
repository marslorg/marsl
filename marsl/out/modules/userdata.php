<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use DateTime;
use DateTimeZone;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\includes\Mailer;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class UserData implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private Mailer $mailer;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        Mailer $mailer,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->mailer = $mailer;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Displays the user administration.
     */
    public function admin(): void
    {
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->authentication->moduleAdminAllowed("userdata", $this->role->getRole()) || $this->authentication->moduleExtendedAllowed("userdata", $this->role->getRole())) {
            if ($this->authentication->moduleAdminAllowed("userdata", $this->role->getRole())) {
                require_once(dirname(__FILE__)."/../admin/template/userdata.alphabet.tpl.php");
            }
            $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
            if (($action == "list") && $this->authentication->moduleAdminAllowed("userdata", $this->role->getRole())) {
                $userdata = array();
                $search = $this->db->escapeString($this->requestParametersService->fromGet()->getStringParameter("search", ""));
                $ownRole = $this->role->getRole();
                $possibleRoles = $this->role->getPossibleRoles($ownRole);
                $result = $this->db->query("SELECT `user`, `user`.`role` AS `roleid`, `nickname`, `prename`, `acronym`, `regdate`, `email`, `postcount`, `user`.`name` AS `username`, `role`.`name` AS `rolename` FROM `user` JOIN `role` USING(`role`) LEFT OUTER JOIN `email` USING(`user`) WHERE `nickname` LIKE '$search%' ORDER BY `nickname`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['user'])
                        && is_string($row['nickname'])
                        && ($row['prename'] == null || is_string($row['prename']))
                        && ($row['acronym'] == null || is_string($row['acronym']))
                        && is_string($row['regdate'])
                        && is_string($row['email'])
                        && is_string($row['postcount'])
                        && ($row['username'] == null || is_string($row['username']))
                        && is_string($row['rolename'])
                        && is_string($row['roleid'])) {
                        $userid = intval(strval($row['user']));
                        $nickname = $this->basic->convertToHTMLEntities($row['nickname']);
                        $prename = $this->basic->convertToHTMLEntities(is_string($row['prename']) ? $row['prename'] : "");
                        $acronym = $this->basic->convertToHTMLEntities(is_string($row['acronym']) ? $row['acronym'] : "");
                        $dateTime->setTimestamp(intval(strval($row['regdate'])));
                        $regdate = $dateTime->format("d\. M Y\; H\:i\:s");
                        $email = $this->basic->convertToHTMLEntities($row['email']);
                        $postcount = intval(strval($row['postcount']));
                        $name = $this->basic->convertToHTMLEntities(is_string($row['username']) ? $row['username'] : "");
                        $rolename = $this->basic->convertToHTMLEntities($row['rolename']);
                        $roleid = intval(strval($row['roleid']));
                        $isMaster = $this->role->isMaster($ownRole, $roleid, $possibleRoles);
                        if ($this->user->getID() == $userid) {
                            $isMaster = true;
                        }
                        array_push($userdata, array('user' => $userid, 'nickname' => $nickname, 'prename' => $prename, 'acronym' => $acronym, 'regdate' => $regdate, 'email' => $email, 'postcount' => $postcount, 'name' => $name, 'rolename' => $rolename, 'isMaster' => $isMaster));
                    }
                }
                require_once(dirname(__FILE__)."/../admin/template/userdata.list.tpl.php");
            }
            if ($action == "details") {
                $userID = $this->requestParametersService->fromGet()->getIntegerParameter("user");
                if ($this->authentication->moduleAdminAllowed("userdata", $this->role->getRole())
                    || ($this->authentication->moduleExtendedAllowed("userdata", $this->role->getRole()) && ($userID == $this->user->getID()))) {
                    $ownID = $this->user->getID();
                    $ownRole = $this->role->getRole();
                    $possibleRoles = $this->role->getPossibleRoles($ownRole);

                    if ($this->requestParametersService->fromPost()->getStringParameter("entermail", "") != "") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                            $this->requestParametersService->fromPost()->getStringParameter("authToken")
                        )) {
                            $email = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("email", ""));
                            if ($this->basic->checkMail($email)) {
                                $curTime = time();
                                $confirmID = $this->basic->confirmID();
                                $this->db->query("INSERT INTO `email`(`email`,`user`, `confirmed`, `time`, `confirm_id`) VALUES('$email', '$userID', '1', '$curTime', '$confirmID')");
                            }
                        }
                    }

                    if ($this->requestParametersService->fromGet()->getStringParameter("delmail", "") != "") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $email = $this->db->escapeString(urldecode($this->requestParametersService->fromGet()->getStringParameter("delmail", "")));
                            $this->db->query("DELETE FROM `email` WHERE `user`='$userID' AND `primary`='0' AND `email`='$email'");
                        }
                    }
                    if ($this->requestParametersService->fromGet()->getStringParameter("primemail", "") != "") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $email = $this->db->escapeString(urldecode($this->requestParametersService->fromGet()->getStringParameter("primemail", "")));
                            if (!$this->db->isExisting("SELECT `email` FROM `email` WHERE `email`='$email' AND `user`='$userID' AND `confirmed`='0' LIMIT 1")) {
                                $this->db->query("UPDATE `email` SET `primary`='0' WHERE `user`='$userID'");
                                $this->db->query("UPDATE `email` SET `primary`='1' WHERE `user`='$userID' AND `email`='$email'");
                            }
                        }
                    }
                    if ($this->requestParametersService->fromGet()->getStringParameter("confmail", "") != "") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $email = $this->db->escapeString(urldecode($this->requestParametersService->fromGet()->getStringParameter("confmail", "")));
                            $this->mailer->sendConfirmationMail($userID, $email);
                        }
                    }

                    $result = $this->db->query("SELECT `user`, `regdate`, `role`, `nickname`, `prename`, `acronym`, `name` FROM `user` WHERE `user`='$userID'");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['role'])
                            && is_string($row['user'])
                            && is_string($row['nickname'])
                            && ($row['prename'] == null || is_string($row['prename']))
                            && ($row['acronym'] == null || is_string($row['acronym']))
                            && is_string($row['regdate'])
                            && ($row['name'] == null || is_string($row['name']))) {
                            $userRole = intval(strval($row['role']));
                            $isMaster = $this->role->isMaster($ownRole, $userRole, $possibleRoles);
                            if ($isMaster || ($this->user->getID() == $userID)) {
                                $userID = intval(strval($row['user']));
                                $nickname = $this->basic->convertToHTMLEntities($row['nickname']);
                                $prename = $this->basic->convertToHTMLEntities(is_string($row['prename']) ? $row['prename'] : "");
                                $acronym = $this->basic->convertToHTMLEntities(is_string($row['acronym']) ? $row['acronym'] : "");
                                $emails = array();
                                $result2 = $this->db->query("SELECT `email`, `confirmed`, `primary` FROM `email` WHERE `user`='$userID' ORDER BY `confirmed` DESC, `primary` DESC");
                                while ($row2 = $this->db->fetchArray($result2)) {
                                    if (is_string($row2['email'])
                                        && is_string($row2['confirmed'])
                                        && is_string($row2['primary'])) {
                                        $email = $this->basic->convertToHTMLEntities($row2['email']);
                                        $confirmed = boolval(strval($row2['confirmed']));
                                        $primary = boolval(strval($row2['primary']));
                                        array_push($emails, array('email' => $email, 'confirmed' => $confirmed, 'primary' => $primary));
                                    }
                                }
                                $name = $this->basic->convertToHTMLEntities(is_string($row['name']) ? $row['name'] : "");
                                $regdate = intval(strval($row['regdate']));
                                $updateNickname = true;
                                $updateAcronym = true;
                                $samePasswords = true;
                                $rightPassword = true;
                                $safePassword = true;
                                $change = $this->requestParametersService->fromPost()->getStringParameter("change", "");
                                $passwordChange = $this->requestParametersService->fromPost()->getStringParameter("passwordChange", "");
                                if ($change != "" || $passwordChange != "") {
                                    if ($this->authentication->checkToken(
                                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                                    )) {
                                        if ($change != "") {
                                            $updateNickname = $this->user->updateNickname($userID, $this->requestParametersService->fromPost()->getStringParameter("nickname", ""));
                                            if ($updateNickname) {
                                                $nickname = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("nickname", ""));
                                            }
                                            $this->user->updatePrename($userID, $this->requestParametersService->fromPost()->getStringParameter("prename", ""));
                                            $prename = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("prename", ""));
                                            $this->user->updateName($userID, $this->requestParametersService->fromPost()->getStringParameter("name", ""));
                                            $name = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("name", ""));

                                            if ($isMaster) {
                                                $updateAcronym = $this->user->updateAcronym($userID, $this->requestParametersService->fromPost()->getStringParameter("acronym", ""));
                                                if ($updateAcronym) {
                                                    $acronym = $this->basic->convertToHTMLEntities($this->requestParametersService->fromPost()->getStringParameter("acronyme", ""));
                                                }
                                                $this->user->updateRole($userID, $this->requestParametersService->fromPost()->getIntegerParameter("role", -1));
                                                $userRole = $this->requestParametersService->fromPost()->getIntegerParameter("role", -1);
                                            }
                                        }
                                        if ($passwordChange != "") {
                                            if ($userID == $this->user->getID()) {
                                                $hash = $this->user->hashPassword($regdate, $this->requestParametersService->fromPost()->getStringParameter("oldPassword"));
                                                $proofPass = $this->user->getPassbyID($this->user->getID());
                                                if ($hash == $proofPass) {
                                                    $newPassword = $this->requestParametersService->fromPost()->getStringParameter("newPassword");
                                                    $proofPassword = $this->requestParametersService->fromPost()->getStringParameter("proofPassword");
                                                    if ($newPassword == $proofPassword) {
                                                        $safePassword = $this->user->setPassword($this->user->getID(), $newPassword);
                                                    } else {
                                                        $samePasswords = false;
                                                    }
                                                } else {
                                                    $rightPassword = false;
                                                }
                                            }
                                        }
                                    }
                                }
                                $roles = array();
                                foreach ($possibleRoles as $possibleRole) {
                                    if ($possibleRole != $ownRole) {
                                        array_push($roles, array('role' => $possibleRole, 'name' => $this->role->getNamebyID($possibleRole)));
                                    }
                                }
                                $authTime = time();
                                $authToken = $this->authentication->getToken($authTime);
                                require_once(dirname(__FILE__)."/../admin/template/userdata.edit.tpl.php");
                            }
                        }
                    }
                }
            }
        }
    }

    public function display(): void
    {
        $userID = $this->user->getID();
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));

        $location = -1;
        $pageID = $this->navigation->getPageID();
        if ($pageID > -1) {
            $location = $pageID;
        } else {
            $location = $this->basic->getHomeLocation();
        }

        $uri = $this->navigation->getRelativeURI($location, null, true);

        $samePasswords = true;
        $rightPassword = true;
        $passwordChange = false;

        if ($this->authentication->locationReadAllowed($location, $this->role->getRole())
            && $this->authentication->moduleReadAllowed("userdata", $this->role->getRole())
            && $this->authentication->moduleWriteAllowed("userdata", $this->role->getRole())) {
            if ($this->requestParametersService->fromPost()->getStringParameter("entermail", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    $email = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("email", ""));
                    if ($this->basic->checkMail($email)) {
                        $curTime = time();
                        $confirmID = $this->basic->confirmID();
                        $this->db->query("INSERT INTO `email`(`email`,`user`, `confirmed`, `time`, `confirm_id`) VALUES('$email', '$userID', '0', '$curTime', '$confirmID')");
                        $this->mailer->sendConfirmationMail($userID, $email);
                    }
                }
            }

            if ($this->requestParametersService->fromGet()->getStringParameter("delmail", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $email = $this->db->escapeString(urldecode($this->requestParametersService->fromGet()->getStringParameter("delmail", "")));
                    $this->db->query("DELETE FROM `email` WHERE `user`='$userID' AND `primary`='0' AND `email`='$email'");
                }
            }
            if ($this->requestParametersService->fromGet()->getStringParameter("primemail", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $email = $this->db->escapeString(urldecode($this->requestParametersService->fromGet()->getStringParameter("primemail", "")));
                    if (!$this->db->isExisting("SELECT `email` FROM `email` WHERE `email`='$email' AND `user`='$userID' AND `confirmed`='0' LIMIT 1")) {
                        $this->db->query("UPDATE `email` SET `primary`='0' WHERE `user`='$userID'");
                        $this->db->query("UPDATE `email` SET `primary`='1' WHERE `user`='$userID' AND `email`='$email'");
                    }
                }
            }
            if ($this->requestParametersService->fromGet()->getStringParameter("confmail", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $email = $this->db->escapeString(urldecode($this->requestParametersService->fromGet()->getStringParameter("confmail", "")));
                    $this->mailer->sendConfirmationMail($userID, $email);
                }
            }


            if (($userID == $this->requestParametersService->fromPost()->getIntegerParameter("userID", -1))
                && ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                ))) {

                if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "password") {
                    $passwordChange = true;
                    $regdate = $this->user->getRegisterDate($userID);
                    $hash = $this->user->hashPassword($regdate, $this->requestParametersService->fromPost()->getStringParameter("oldPassword"));
                    $proofPass = $this->user->getPassbyID($this->user->getID());
                    if ($hash == $proofPass) {
                        $newPassword = $this->requestParametersService->fromPost()->getStringParameter("newPassword");
                        $proofPassword = $this->requestParametersService->fromPost()->getStringParameter("proofPassword");
                        if ($newPassword == $proofPassword) {
                            $safePassword = $this->user->setPassword($this->user->getID(), $newPassword);
                        } else {
                            $samePasswords = false;
                        }
                    } else {
                        $rightPassword = false;
                    }
                }

                if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "edit") {
                    $prename = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("prename", ""));
                    $name = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("name", ""));
                    $info = $this->db->escapeString($this->basic->cleanStrict($this->requestParametersService->fromPost()->getStringParameter("info", "")));
                    $signature = $this->db->escapeString($this->basic->cleanStrict($this->requestParametersService->fromPost()->getStringParameter("signature", "")));
                    $birthdate = 0;
                    $month = $this->requestParametersService->fromPost()->getIntegerParameter("month", 0);
                    $day = $this->requestParametersService->fromPost()->getIntegerParameter("day", 0);
                    $year = $this->requestParametersService->fromPost()->getIntegerParameter("year", 0);
                    if (checkdate($month, $day, $year)) {
                        $birthdate = mktime(0, 0, 0, $month, $day, $year);
                    }
                    $gender = $this->requestParametersService->fromPost()->getStringParameter("gender", "");
                    if ($gender != "female" && $gender != "male") {
                        $gender = "";
                    }

                    $interests = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("interests", ""));
                    $job = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("job", ""));
                    $zip = $this->requestParametersService->fromPost()->getIntegerParameter("zip", 0);
                    $street = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("street", ""));
                    $house = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("house", ""));
                    $city = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("city", ""));
                    $this->db->query("UPDATE `user` SET `prename`='$prename', `name`='$name', `info`='$info', `signature`='$signature', `birthdate`='$birthdate', `gender`='$gender', `interests`='$interests', `job`='$job', `zip`='$zip', `street`='$street', `house`='$house', `city`='$city' WHERE `user`='$userID'");
                }
            }

            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            $nickname = "";
            $prename = "";
            $name = "";
            $info = "";
            $signature = "";
            $day = "DD";
            $month = "MM";
            $year = "YYYY";
            $gender = "";
            $interests = "";
            $job = "";
            $zip = "";
            $street = "";
            $house = "";
            $city = "";

            $result = $this->db->query("SELECT `user`, `prename`, `name`, `info`, `signature`, `birthdate`, `gender`, `interests`, `job`, `zip`, `street`, `house`, `city` FROM `user` WHERE `user`='$userID'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['user'])
                    && ($row['prename'] == null || is_string($row['prename']))
                    && ($row['name'] == null || is_string($row['name']))
                    && ($row['info'] == null || is_string($row['info']))
                    && ($row['signature'] == null || is_string($row['signature']))
                    && is_string($row['birthdate'])
                    && ($row['interests'] == null || is_string($row['interests']))
                    && ($row['job'] == null || is_string($row['job']))
                    && ($row['zip'] == null || is_string($row['zip']))
                    && ($row['street'] == null || is_string($row['street']))
                    && ($row['house'] == null || is_string($row['house']))
                    && ($row['city'] == null || is_string($row['city']))) {
                    $userID = intval(strval($row['user']));
                    $prename = $this->basic->convertToHTMLEntities(is_string($row['prename']) ? $row['prename'] : "");
                    $name = $this->basic->convertToHTMLEntities(is_string($row['name']) ? $row['name'] : "");
                    $info = is_string($row['info']) ? $row['info'] : "";
                    $signature = is_string($row['signature']) ? $row['signature'] : "";
                    $dateTime->setTimestamp(intval(strval($row['birthdate'])));
                    $day = $dateTime->format("d");
                    $month = $dateTime->format("m");
                    $year = $dateTime->format("Y");
                    $gender = $row['gender'];
                    $interests = $this->basic->convertToHTMLEntities(is_string($row['interests']) ? $row['interests'] : "");
                    $job = $this->basic->convertToHTMLEntities(is_string($row['job']) ? $row['job'] : "");
                    $zip = intval(strval(is_string($row['zip']) ? $row['zip'] : "0"));
                    $street = $this->basic->convertToHTMLEntities(is_string($row['street']) ? $row['street'] : "");
                    $house = $this->basic->convertToHTMLEntities(is_string($row['house']) ? $row['house'] : "");
                    $city = $this->basic->convertToHTMLEntities(is_string($row['city']) ? $row['city'] : "");
                }
            }

            $emails = array();

            $result = $this->db->query("SELECT `email`, `confirmed`, `primary` FROM `email` WHERE `user` = '$userID' ORDER BY `confirmed` DESC, `primary` DESC");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['email'])
                    && is_string($row['confirmed'])
                    && is_string($row['primary'])) {
                    $email = $this->basic->convertToHTMLEntities($row['email']);
                    $confirmed = boolval(strval($row['confirmed']));
                    $primary = boolval(strval($row['primary']));
                    $encodedMail = urlencode($email);
                    $primaryURI = $uri."primemail=".$encodedMail."&time=".$authTime."&token=".$authToken;
                    $deleteURI = $uri."delmail=".$encodedMail."&time=".$authTime."&token=".$authToken;
                    $confirmURI = $uri."confmail=".$encodedMail."&time=".$authTime."&token=".$authToken;
                    array_push($emails, array('email' => $email, 'confirmed' => $confirmed, 'primary' => $primary, 'primaryURI' => $primaryURI, 'deleteURI' => $deleteURI, 'confirmURI' => $confirmURI));
                }
            }

            require_once(dirname(__FILE__)."/../template/userdata.tpl.php");
        }

    }

    /*
     * Interface method stub.
    */
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

    /*
     * Interface method stub.
    */
    public function search(string $query, string $type): void
    {
    }

    /*
     * Interface method stub.
    */
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

    /*
     * Interface method stub.
    */
    public function addTags(string $tagString, string $type, int $news): void
    {
    }

    /*
     * Interface method stub.
    */
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
