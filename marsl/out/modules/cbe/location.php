<?php

namespace marsl\modules\cbe;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class Location
{
    private Authentication $authentication;
    private Basic $basic;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        DB $db,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->db = $db;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    public function display(): void
    {
    }

    public function admin(): void
    {
        if ($this->authentication->moduleAdminAllowed("cbe", $this->role->getRole())) {
            $newEntry = false;
            $entrySuccessful = false;
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "newClub") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    $newEntry = true;
                    $entry = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("entry", ""));
                    if (!$this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$entry' LIMIT 1")) {
                        $this->db->query("INSERT INTO `location`(`tag`) VALUES('$entry')");
                        $entrySuccessful = true;
                    }
                }
            }
            $deletionSuccessful = false;
            if ($this->requestParametersService->fromGet()->getStringParameter("action2", "") == "delete") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $clubID = $this->requestParametersService->fromGet()->getIntegerParameter("club", -1);
                    $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$clubID' AND `type`='cbe_location'");
                    $this->db->query("DELETE FROM `location` WHERE `id`='$clubID'");
                    $deletionSuccessful = true;
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            $clubs = array();
            $search = $this->db->escapeString($this->requestParametersService->fromGet()->getStringParameter("search", ""));
            $result = $this->db->query("SELECT `id`, `tag` FROM `location` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                && is_string($row['tag'])) {
                    $id = intval(strval($row['id']));
                    $tag = $this->basic->convertToHTMLEntities($row['tag']);
                    array_push($clubs, array('id' => $id, 'tag' => $tag));
                }
            }
            require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.tpl.php");
        }
    }

    public function edit(int $id): void
    {
        $authTime = time();
        $authToken = $this->authentication->getToken($authTime);
        if ($this->authentication->moduleAdminAllowed("cbe", $this->role->getRole())) {
            $nameconvertion = false;
            $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            if ($action == "name") {
                $nameconvertion = true;
            }
            if ($action == "tagExists") {
                $nameconvertion = true;
            }

            if ($nameconvertion) {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    $tag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("tag", ""));
                    $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
                    if ($do == "autoRename") {
                        $tag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("autoTag", ""));
                    }
                    if (($action == "tagExists") || $this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
                        if ($action == "tagExists") {
                            if ((($do == "rename") || ($do == "autoRename")) && $this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
                                $result = $this->db->query("SELECT `id` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')");
                                while ($row = $this->db->fetchArray($result)) {
                                    $duplicateID = $row['id'];
                                    $result2 = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$id'");
                                    while ($row2 = $this->db->fetchArray($result2)) {
                                        if (is_string($row2['tag'])) {
                                            $oldTag = $this->basic->convertToHTMLEntities($row2['tag']);
                                            $i = 2;
                                            $autoTag = $tag." (".$i.")";
                                            while ($this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
                                                $i++;
                                                $autoTag = $tag." (".$i.")";
                                            }
                                            require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.tag.tpl.php");
                                        }
                                    }
                                }
                            } else {
                                if ($do == "saveDuplicate") {
                                    $duplicateID = $this->requestParametersService->fromPost()->getIntegerParameter("duplicateID", -1);
                                    $result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='cbe_location'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['news'])) {
                                            $newsID = intval(strval($row['news']));
                                            $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='cbe_location'");
                                        }
                                    }
                                    $this->db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='cbe_location' AND `tag`='$id'");
                                    $this->db->query("DELETE FROM `location` WHERE `id`='$id'");
                                    $id = $duplicateID;
                                    require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.edit.success.tpl.php");
                                }
                                if ($do == "moveToDuplicate") {
                                    $targetTag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("targetTage", ""));
                                    $duplicateID = $this->requestParametersService->fromPost()->getIntegerParameter("duplicateID", -1);
                                    $result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='cbe_location'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['news'])) {
                                            $newsID = intval(strval($row['news']));
                                            $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='cbe_location'");
                                        }
                                    }
                                    $this->db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='cbe_location' AND `tag`='$duplicateID'");
                                    $this->db->query("DELETE FROM `location` WHERE `id`='$duplicateID'");
                                    $this->db->query("UPDATE `location` SET `tag`='$targetTag' WHERE `id`='$id'");
                                    require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.edit.success.tpl.php");
                                }
                                if ($do == "autoRename") {
                                    $this->db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
                                    $this->buildEditingForm($id);
                                }
                                if ($do == "rename") {
                                    $this->db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
                                    $this->buildEditingForm($id);
                                }
                            }
                        } else {
                            $result = $this->db->query("SELECT `id` FROM `location` WHERE `tag`='$tag' AND NOT(`id`='$id')");
                            while ($row = $this->db->fetchArray($result)) {
                                $duplicateID = $row['id'];
                                $result2 = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$id'");
                                while ($row2 = $this->db->fetchArray($result2)) {
                                    if (is_string($row2['tag'])) {
                                        $oldTag = $this->basic->convertToHTMLEntities($row2['tag']);
                                        $i = 2;
                                        $autoTag = $tag." (".$i.")";
                                        while ($this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
                                            $i++;
                                            $autoTag = $tag." (".$i.")";
                                        }
                                        require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.tag.tpl.php");
                                    }
                                }
                            }
                        }
                    } else {
                        $this->db->query("UPDATE `location` SET `tag`='$tag' WHERE `id`='$id'");
                        require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.edit.success.tpl.php");
                    }
                }
            } else {
                if ($action == "send") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $street = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("street", ""));
                        $number = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("number", ""));
                        $zip = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("zip", ""));
                        $city = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("city", ""));
                        $country = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("country", ""));
                        $capacity = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("capacity", ""));
                        $info = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("info", "")));
                        $this->db->query("UPDATE `location` SET `street`='$street', `number`='$number', `zip`='$zip', `city`='$city', `country`='$country', `capacity`='$capacity', `info`='$info' WHERE `id`='$id'");
                    }
                }
                $this->buildEditingForm($id);
            }
        }
    }

    private function buildEditingForm(int $id): void
    {
        $authTime = time();
        $authToken = $this->authentication->getToken($authTime);
        $news = array();
        $result = $this->db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='cbe_location' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['news'])
            && is_string($row['headline'])
            && is_string($row['title'])) {
                $newsID = intval(strval($row['news']));
                $headline = $this->basic->convertToHTMLEntities($row['headline']);
                $title = $this->basic->convertToHTMLEntities($row['title']);
                array_push($news, array('news' => $newsID, 'headline' => $headline, 'title' => $title));
            }
        }
        $result = $this->db->query("SELECT `tag`, `street`, `number`, `zip`, `city`, `country`, `capacity`, `info` FROM `location` WHERE `id`='$id'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['tag'])
            && ($row['street'] == null || is_string($row['street']))
            && ($row['number'] == null || is_string($row['number']))
            && ($row['zip'] == null || is_string($row['zip']))
            && ($row['city'] == null || is_string($row['city']))
            && ($row['country'] == null || is_string($row['country']))
            && ($row['capacity'] == null || is_string($row['capacity']))
            && ($row['info'] == null || is_string($row['info']))) {
                $tag = $this->basic->convertToHTMLEntities($row['tag']);
                $street = is_string($row['street']) ? $this->basic->convertToHTMLEntities($row['street']) : "";
                $number = is_string($row['number']) ? $this->basic->convertToHTMLEntities($row['number']) : "";
                $zip = is_string($row['zip']) ? $this->basic->convertToHTMLEntities($row['zip']) : "";
                $city = is_string($row['city']) ? $this->basic->convertToHTMLEntities($row['city']) : "";
                $country = is_string($row['country']) ? $this->basic->convertToHTMLEntities($row['country']) : "";
                $capacity = is_string($row['capacity']) ? $this->basic->convertToHTMLEntities($row['capacity']) : "";
                $info = is_string($row['info']) ? $row['info'] : "";
                require_once(dirname(__FILE__)."/../../admin/template/cbe.clubs.edit.tpl.php");
            }
        }
    }

}
