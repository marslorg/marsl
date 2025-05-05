<?php

namespace marsl\modules\cbe;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;

class Band
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
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "newBand"
            && $this->authentication->checkToken(
                $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                $this->requestParametersService->fromPost()->getStringParameter("authToken")
            )) {
                $newEntry = true;
                $entry = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("entry", ""));
                if (!$this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$entry' LIMIT 1")) {
                    $this->db->query("INSERT INTO `band`(`tag`) VALUES('$entry')");
                    $entrySuccessful = true;
                }
            }
            $deletionSuccessful = false;
            if ($this->requestParametersService->fromGet()->getStringParameter("action2", "") == "delete"
            && $this->authentication->checkToken(
                $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                $this->requestParametersService->fromGet()->getStringParameter("token")
            )) {
                $bandID = $this->requestParametersService->fromGet()->getIntegerParameter("band", -1);
                $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$bandID' AND `type`='cbe_band'");
                $this->db->query("DELETE FROM `band` WHERE `id`='$bandID'");
                $deletionSuccessful = true;
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            $bands = array();
            $search = $this->db->escapeString($this->requestParametersService->fromGet()->getStringParameter("search", ""));
            $result = $this->db->query("SELECT `id`, `tag` FROM `band` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                && is_string($row['tag'])) {
                    $id = intval(strval($row['id']));
                    $tag = $this->basic->convertToHTMLEntities($row['tag']);
                    array_push($bands, array('id' => $id, 'tag' => $tag));
                }
            }
            require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.tpl.php");
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
                    if (($action == "tagExists") || $this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
                        if ($action == "tagExists") {
                            if ((($do == "rename") || ($do == "autoRename")) && $this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
                                $result = $this->db->query("SELECT `id` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id')");
                                while ($row = $this->db->fetchArray($result)) {
                                    $duplicateID = $row['id'];
                                    $result2 = $this->db->query("SELECT `tag` FROM `band` WHERE `id`='$id'");
                                    while ($row2 = $this->db->fetchArray($result2)) {
                                        if (is_string($row2['tag'])) {
                                            $oldTag = $this->basic->convertToHTMLEntities($row2['tag']);
                                            $i = 2;
                                            $autoTag = $tag." (".$i.")";
                                            while ($this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
                                                $i++;
                                                $autoTag = $tag." (".$i.")";
                                            }
                                            require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.tag.tpl.php");
                                        }
                                    }
                                }
                            } else {
                                if ($do == "saveDuplicate") {
                                    $duplicateID = $this->requestParametersService->fromPost()->getIntegerParameter("duplicateID", -1);
                                    $result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='cbe_band'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['news'])) {
                                            $newsID = intval(strval($row['news']));
                                            $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='cbe_band'");
                                        }
                                    }
                                    $this->db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='cbe_band' AND `tag`='$id'");
                                    $this->db->query("DELETE FROM `band` WHERE `id`='$id'");
                                    $id = $duplicateID;
                                    require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.edit.success.tpl.php");
                                }
                                if ($do == "moveToDuplicate") {
                                    $targetTag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("targetTag", ""));
                                    $duplicateID = $this->requestParametersService->fromPost()->getIntegerParameter("duplicateID", -1);
                                    $result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='cbe_band'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['news'])) {
                                            $newsID = intval(strval($row['news']));
                                            $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='cbe_band'");
                                        }
                                    }
                                    $this->db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='cbe_band' AND `tag`='$duplicateID'");
                                    $this->db->query("DELETE FROM `band` WHERE `id`='$duplicateID'");
                                    $this->db->query("UPDATE `band` SET `tag`='$targetTag' WHERE `id`='$id'");
                                    require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.edit.success.tpl.php");
                                }
                                if ($do == "autoRename") {
                                    $this->db->query("UPDATE `band` SET `tag`='$tag' WHERE `id`='$id'");
                                    $this->buildEditingForm($id);
                                }
                                if ($do == "rename") {
                                    $this->db->query("UPDATE `band` SET `tag`='$tag' WHERE `id`='$id'");
                                    $this->buildEditingForm($id);
                                }
                            }
                        } else {
                            $result = $this->db->query("SELECT `id` FROM `band` WHERE `tag`='$tag' AND NOT(`id`='$id')");
                            while ($row = $this->db->fetchArray($result)) {
                                $duplicateID = $row['id'];
                                $result2 = $this->db->query("SELECT `tag` FROM `band` WHERE `id`='$id'");
                                while ($row2 = $this->db->fetchArray($result2)) {
                                    if (is_string($row2['tag'])) {
                                        $oldTag = $this->basic->convertToHTMLEntities($row2['tag']);
                                        $i = 2;
                                        $autoTag = $tag." (".$i.")";
                                        while ($this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
                                            $i++;
                                            $autoTag = $tag." (".$i.")";
                                        }
                                        require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.tag.tpl.php");
                                    }
                                }
                            }
                        }
                    } else {
                        $this->db->query("UPDATE `band` SET `tag`='$tag' WHERE `id`='$id'");
                        require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.edit.success.tpl.php");
                    }
                }
            } else {
                if ($action == "send") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $founded = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("founded", ""));
                        $ended = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("ended", ""));
                        $info = $this->db->escapeString($this->basic->cleanHTML($this->requestParametersService->fromPost()->getStringParameter("info", "")));
                        $this->db->query("UPDATE `band` SET `founded`='$founded', `ended`='$ended', `info`='$info' WHERE `id`='$id'");
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
        $result = $this->db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='cbe_band' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
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
        $result = $this->db->query("SELECT `tag`, `founded`, `ended`, `info` FROM `band` WHERE `id`='$id'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['tag'])
            && ($row['founded'] == null || is_string($row['founded']))
            && ($row['ended'] == null || is_string($row['ended']))
            && ($row['info'] == null || is_string($row['info']))) {
                $tag = $this->basic->convertToHTMLEntities($row['tag']);
                $founded = is_string($row['founded']) ? $this->basic->convertToHTMLEntities($row['founded']) : "";
                $ended = is_string($row['ended']) ? $this->basic->convertToHTMLEntities($row['ended']) : "";
                $info = is_string($row['info']) ? $row['info'] : "";
                require_once(dirname(__FILE__)."/../../admin/template/cbe.bands.edit.tpl.php");
            }
        }
    }

}
