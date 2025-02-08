<?php

namespace marsl\admin;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use marsl\includes\Basic;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Tags
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
        if ($this->user->isHead()) {
            $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
            if ($action != "") {
                if ($action == "edit") {
                    $this->edit($this->requestParametersService->fromGet()->getIntegerParameter("tagid"));
                }
            } else {
                $newEntry = false;
                $entrySuccessful = false;
                $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
                if ($action != "") {
                    if ($action == "newTag") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                            $this->requestParametersService->fromPost()->getStringParameter("authToken")
                        )) {
                            $newEntry = true;
                            $entry = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("entry"));
                            if (!$this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$entry' LIMIT 1")) {
                                $this->db->query("INSERT INTO `general`(`tag`) VALUES('$entry')");
                                $entrySuccessful = true;
                            }
                        }
                    }
                }
                $deletionSuccessful = false;
                if ($this->requestParametersService->fromGet()->getStringParameter("action2", "") == "delete") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                        $this->requestParametersService->fromGet()->getStringParameter("token")
                    )) {
                        $tagID = $this->requestParametersService->fromGet()->getIntegerParameter("tagid");
                        $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$tagID' AND `type`='general'");
                        $this->db->query("DELETE FROM `general` WHERE `id`='$tagID'");
                        $deletionSuccessful = true;
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                $tags = array();
                $search = $this->db->escapeString($this->requestParametersService->fromGet()->getStringParameter("search"));
                $result = $this->db->query("SELECT `id`, `tag` FROM `general` WHERE `tag` LIKE '$search%' ORDER BY `tag` ASC");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['tag'])) {
                        $id = $row['id'];
                        $tag = $this->basic->convertToHTMLEntities($row['tag']);
                        array_push($tags, array('id' => $id, 'tag' => $tag));
                    }
                }
                require_once("template/tags.tpl.php");
            }
        }
    }

    private function edit(int $id): void
    {
        $authTime = time();
        $authToken = $this->authentication->getToken($authTime);
        if ($this->user->isHead()) {
            $nameconvertion = false;
            $action = $this->requestParametersService->fromPost()->getStringParameter("action", "");
            if ($action == "name") {
                $nameconvertion = true;
            }
            if ($action == "tagExists") {
                $nameconvertion = true;
            }

            $tag = "";

            if ($nameconvertion) {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    $tag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("tag", ""));
                    $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
                    if ($do == "autoRename") {
                        $tag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("autoTag"));
                    }
                    if (($action == "tagExists") || $this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
                        if ($action == "tagExists") {
                            if ((($do == "rename") || ($do == "autoRename")) && $this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id') LIMIT 1")) {
                                $result = $this->db->query("SELECT `id` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')");
                                while ($row = $this->db->fetchArray($result)) {
                                    $duplicateID = $row['id'];
                                    $result2 = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
                                    while ($row2 = $this->db->fetchArray($result2)) {
                                        if (is_string($row2['tag'])) {
                                            $oldTag = $this->basic->convertToHTMLEntities($row2['tag']);
                                            $i = 2;
                                            $autoTag = $tag." (".$i.")";
                                            while ($this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
                                                $i++;
                                                $autoTag = $tag." (".$i.")";
                                            }
                                            require_once("template/tags.tag.tpl.php");
                                        }
                                    }
                                }
                            } else {
                                if ($do == "saveDuplicate") {
                                    $duplicateID = $this->requestParametersService->fromPost()->getIntegerParameter("duplicateID");
                                    $result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$duplicateID' AND `type`='general'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['news'])) {
                                            $newsID = (int)$row['news'];
                                            $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$id' AND `news`='$newsID' AND `type`='general'");
                                        }
                                    }
                                    $this->db->query("UPDATE `news_tag` SET `tag`='$duplicateID' WHERE `type`='general' AND `tag`='$id'");
                                    $this->db->query("DELETE FROM `general` WHERE `id`='$id'");
                                    $id = $duplicateID;
                                    require_once("template/tags.edit.success.tpl.php");
                                }
                                if ($do == "moveToDuplicate") {
                                    $targetTag = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("targetTag"));
                                    $duplicateID = $this->requestParametersService->fromPost()->getIntegerParameter("duplicateID");
                                    $result = $this->db->query("SELECT `news` FROM `news_tag` WHERE `tag`='$id' AND `type`='general'");
                                    while ($row = $this->db->fetchArray($result)) {
                                        if (is_string($row['news'])) {
                                            $newsID = (int)$row['news'];
                                            $this->db->query("DELETE FROM `news_tag` WHERE `tag`='$duplicateID' AND `news`='$newsID' AND `type`='general'");
                                        }
                                    }
                                    $this->db->query("UPDATE `news_tag` SET `tag`='$id' WHERE `type`='general' AND `tag`='$duplicateID'");
                                    $this->db->query("DELETE FROM `general` WHERE `id`='$duplicateID'");
                                    $this->db->query("UPDATE `general` SET `tag`='$targetTag' WHERE `id`='$id'");
                                    require_once("template/tags.edit.success.tpl.php");
                                }
                                if ($do == "autoRename") {
                                    $this->db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
                                    $this->buildEditingForm($id);
                                }
                                if ($do == "rename") {
                                    $this->db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
                                    $this->buildEditingForm($id);
                                }
                            }
                        } else {
                            $result = $this->db->query("SELECT `id` FROM `general` WHERE `tag`='$tag' AND NOT(`id`='$id')");
                            while ($row = $this->db->fetchArray($result)) {
                                $duplicateID = $row['id'];
                                $result2 = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
                                while ($row2 = $this->db->fetchArray($result2)) {
                                    if (is_string($row2['tag'])) {
                                        $oldTag = $this->basic->convertToHTMLEntities($row2['tag']);
                                        $i = 2;
                                        $autoTag = $tag." (".$i.")";
                                        while ($this->db->isExisting("SELECT `tag` FROM `general` WHERE `tag`='$autoTag' AND NOT(`id`='$id') LIMIT 1")) {
                                            $i++;
                                            $autoTag = $tag." (".$i.")";
                                        }
                                        require_once("template/tags.tag.tpl.php");
                                    }
                                }
                            }
                        }
                    } else {
                        $this->db->query("UPDATE `general` SET `tag`='$tag' WHERE `id`='$id'");
                        require_once("template/tags.edit.success.tpl.php");
                    }
                }
            } else {
                $this->buildEditingForm($id);
            }
        }
    }

    private function buildEditingForm(int $id): void
    {
        $authTime = time();
        $authToken = $this->authentication->getToken($authTime);
        $news = array();
        $result = $this->db->query("SELECT `news`, `headline`,`title` FROM `news_tag` NATURAL JOIN `news` WHERE `type`='general' AND `tag`='$id' AND `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['news']) && is_string($row['headline']) && is_string($row['title'])) {
                $newsID = (int)$row['news'];
                $headline = $this->basic->convertToHTMLEntities($row['headline']);
                $title = $this->basic->convertToHTMLEntities($row['title']);
                array_push($news, array('news' => $newsID, 'headline' => $headline, 'title' => $title));
            }
        }
        $result = $this->db->query("SELECT `tag` FROM `general` WHERE `id`='$id'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['tag'])) {
                $tag = $this->basic->convertToHTMLEntities($row['tag']);
                require_once("template/tags.edit.tpl.php");
            }
        }
    }
}
