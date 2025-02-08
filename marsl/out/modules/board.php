<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../autoload.php");

use DateTime;
use DateTimeZone;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\board\BoardBase;
use marsl\modules\board\Post;
use marsl\modules\board\Thread;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Board implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private BoardBase $boardBase;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private Post $post;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private Thread $thread;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        BoardBase $boardBase,
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        Post $post,
        IRequestParametersService $requestParametersService,
        Role $role,
        Thread $thread,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->boardBase = $boardBase;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->post = $post;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->thread = $thread;
        $this->user = $user;
    }

    /*
     * Displays the boards of a global location.
     */
    public function display(): void
    {
        $id = $this->navigation->getPageID();
        $location = $id;
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->authentication->moduleReadAllowed("board", $this->role->getRole())
        && $this->authentication->locationReadAllowed($location, $this->role->getRole())) {
            $action = $this->getAction();
            $threadAction = $this->getThreadAction();
            if (!empty($threadAction)) {
                if ($threadAction == "globalfix") {
                    $this->thread->fixGlobal();
                }
                if ($threadAction == "defix") {
                    $this->thread->removeFixation();
                }
                if ($threadAction == "localfix") {
                    $this->thread->fixLocal();
                }
                if ($threadAction == "posts") {
                    $this->post->display();
                }
                if ($threadAction == "title") {
                    $this->thread->changeTitle();
                }
                if ($threadAction == "move") {
                    $this->thread->moveThread();
                }
                if ($threadAction == "delete") {
                    $this->thread->delete();
                }
                if ($threadAction == "close") {
                    $this->thread->close();
                }
                if ($threadAction == "open") {
                    $this->thread->open();
                }
            } elseif (!empty($action)) {
                if ($action == "threads") {
                    $this->thread->display();
                }
                if ($action == "posts") {
                    $this->post->display();
                }
                if ($action == "edit") {
                    $this->post->edit();
                }
                if ($action == "answer") {
                    $this->post->answer();
                }
                if ($action == "newthread") {
                    $this->thread->newThread();
                }
            } else {
                $categories = array();
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $result = $this->db->query("SELECT `board`, `title`, `threadcount`, `postcount`, `description`, `type`, `location` FROM `board` WHERE `type` IN ('0', '1') AND `location`='$location' OR `location`IN (SELECT `board` FROM `board` WHERE `location`='$location') ORDER BY `type`, `pos`");
                while ($row = $this->db->fetchArray($result)) {
                    $type = $row['type'];
                    if (is_string($row['board'])
                        && is_string($row['title'])
                        && is_string($row['threadcount'])
                        && is_string($row['postcount'])
                        && ($row['description'] == null || is_string($row['description']))
                        && is_string($row['location'])) {
                        $board = intval(strval($row['board']));
                        $title = $this->basic->convertToHTMLEntities($row['title']);
                        if ($this->boardBase->readAllowed($board, $this->role->getRole())) {
                            if ($type == 0) {
                                $categories[$board] = array('title' => $title, 'boards' => array());
                            } elseif ($type == 1) {
                                $threadcount = intval(strval($row['threadcount']));
                                $postcount = intval(strval($row['postcount']));
                                $description = $this->basic->convertToHTMLEntities(is_string($row['description']) ? $row['description'] : "");
                                $category = intval(strval($row['location']));
                                $thread = -1;
                                $post = -1;
                                $threadTitle = "";
                                $postTime = "";
                                $postAuthor = "";
                                $authorName = "";
                                $page = -1;
                                $result3 = $this->db->query("SELECT `post`, `date`, `post`.`thread` AS `thread`, `title`, `post`.`author` AS postauthor FROM `post` JOIN `thread` ON (`thread`.`thread`=`post`.`thread`) WHERE `deleted`='0' AND `board`='$board' AND `type` IN ('0', '1', '2', '3') ORDER BY `date` DESC LIMIT 1");
                                while ($row3 = $this->db->fetchArray($result3)) {
                                    if (is_string($row3['thread'])
                                        && is_string($row3['post'])
                                        && ($row3['title'] == null || is_string($row3['title']))
                                        && is_string($row3['date'])
                                        && is_string($row3['postauthor'])) {
                                        $thread = intval(strval($row3['thread']));
                                        $post = intval(strval($row3['post']));
                                        $threadTitle = $this->basic->convertToHTMLEntities(is_string($row3['title']) ? $row3['title'] : "");
                                        $dateTime->setTimestamp(intval(strval($row3['date'])));
                                        $postTime = $dateTime->format("d\.m\.Y\, H\:i\:s");
                                        $postAuthor = intval(strval($row3['postauthor']));
                                        $authorName = $this->basic->convertToHTMLEntities($this->user->getNickbyID($postAuthor));
                                        $page = 1;
                                        $result4 = $this->db->query("SELECT COUNT(`thread`) AS paging FROM `post` WHERE `thread`='$thread' AND `deleted`='0'");
                                        while ($row4 = $this->db->fetchArray($result4)) {
                                            if (is_string($row4['paging'])) {
                                                $paging = intval(strval($row4['paging']));
                                                $page = (int)ceil($paging / 10);
                                            }
                                        }
                                    }
                                }
                                $operators = array();
                                $result3 = $this->db->query("SELECT `user` FROM `board_operator` WHERE `board`='$board'");
                                while ($row3 = $this->db->fetchArray($result3)) {
                                    if (is_string($row3['user'])) {
                                        $operator = intval(strval($row3['user']));
                                        $operatorRole = $this->role->getRoleByUser($operator);
                                        if ($this->authentication->moduleReadAllowed("board", $operatorRole)
                                        && $this->authentication->moduleWriteAllowed("board", $operatorRole)
                                        && $this->authentication->locationReadAllowed($location, $operatorRole)
                                        && $this->authentication->locationWriteAllowed($location, $operatorRole)
                                        && $this->boardBase->readAllowed($board, $operatorRole)
                                        && $this->boardBase->writeAllowed($board, $operatorRole)
                                        && $this->boardBase->extendedAllowed($board, $operatorRole)) {
                                            $operatorNick = $this->basic->convertToHTMLEntities($this->user->getNickbyID($operator));
                                            array_push($operators, array('user' => $operator, 'nickname' => $operatorNick));
                                        }
                                    }
                                }
                                $boardURIPart = $this->boardBase->getBoardURIPart($board, $title);
                                $threadsURI = $uri.$boardURIPart;
                                $pagePostsURI = $uri.$this->thread->getThreadURIPart($thread, $threadTitle, $page);
                                array_push($categories[$category]['boards'], array('board' => $board, 'title' => $title, 'description' => $description, 'threadcount' => $threadcount, 'postcount' => $postcount, 'thread' => $thread, 'threadTitle' => $threadTitle, 'threadsURI' => $threadsURI, 'page' => $page, 'post' => $post, 'pagePostsURI' => $pagePostsURI, 'date' => $postTime, 'user' => $postAuthor, 'nickname' => $authorName, 'operators' => $operators));
                            }
                        }
                    }
                }
                require_once(dirname(__FILE__)."/../template/board.main.tpl.php");
            }
        }
    }

    /*
     * Displays the administrator view of the board.
     */
    public function admin(): void
    {
        if ($this->authentication->moduleAdminAllowed("board", $this->role->getRole())) {
            $page = $this->requestParametersService->fromGet()->getStringParameter("page", "");
            if ($page != "") {
                if ($page == "role") {
                    $this->roleManagement();
                }
                if ($page == "operator") {
                    $this->operatorManagement();
                }
                if ($page == "description") {
                    $this->changeDescription();
                }
            } else {
                $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
                if ($action != "") {
                    if ($action == "change" && ($this->requestParametersService->fromPost()->getStringParameter("board", "") != "")) {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                            $this->requestParametersService->fromPost()->getStringParameter("authToken")
                        )) {
                            $board = $this->requestParametersService->fromPost()->getIntegerParameter("board", -1);
                            if ($this->boardBase->adminAllowed($board, $this->role->getRole())) {
                                $type = "2";
                                $location = -1;
                                $newLocation = $this->requestParametersService->fromPost()->getIntegerParameter("location", -1);
                                $result = $this->db->query("SELECT `type`, `location` FROM `board` WHERE `board`='$board'");
                                while ($row = $this->db->fetchArray($result)) {
                                    if (is_string($row['location'])) {
                                        $type = $row['type'];
                                        $location = intval(strval($row['location']));
                                    }
                                }
                                if ($type == "0") {
                                    if ($this->db->isExisting("SELECT `id` FROM `navigation` WHERE `module`='board' AND `id`='$newLocation' LIMIT 1")) {
                                        $location = $newLocation;
                                    }
                                }
                                if ($type == "1") {
                                    if ($this->db->isExisting("SELECT `board` FROM `board` WHERE `type`='0' AND `board`='$newLocation' LIMIT 1")) {
                                        $location = $newLocation;
                                    }
                                }
                                $pos = $this->requestParametersService->fromPost()->getIntegerParameter("pos");
                                $title = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("title", ""));
                                $this->db->query("UPDATE `board` SET `title`='$title', `pos`='$pos', `location`='$location' WHERE `board`='$board'");
                            }
                        }
                    }
                    if ($action == "del") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $board = $this->requestParametersService->fromGet()->getIntegerParameter("board");
                            if ($this->boardBase->adminAllowed($board, $this->role->getRole())
                            && $this->boardBase->extendedAllowed($board, $this->role->getRole())
                            && $this->boardBase->writeAllowed($board, $this->role->getRole())
                            && $this->boardBase->readAllowed($board, $this->role->getRole())) {
                                $this->db->query("UPDATE `board` SET `type`='2' WHERE `board`='$board'");
                            }
                        }
                    }
                    if ($action == "addcat") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $this->db->query("INSERT INTO `board`(`pos`, `title`,`type`) VALUES('0','Standard','0')");
                            $board = (int)$this->db->lastInsertedID();
                            $this->setRights($this->role->getRole(), $board, true, true, true, true);
                        }
                    }
                    if ($action == "addboard") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                            $this->requestParametersService->fromGet()->getStringParameter("token")
                        )) {
                            $this->db->query("INSERT INTO `board`(`pos`, `title`,`type`,`threadcount`,`postcount`) VALUES('0','Standard','1','0','0')");
                            $board = (int)$this->db->lastInsertedID();
                            $this->setRights($this->role->getRole(), $board, true, true, true, true);
                        }
                    }
                }

                $locations = array();
                $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='board' AND (`type`='1' OR `type`='2') ORDER BY `pos`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['id'])
                        && is_string($row['name'])) {
                        $id = intval(strval($row['id']));
                        if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())) {
                            $name = $this->basic->convertToHTMLEntities($row['name']);
                            array_push($locations, array('id' => $id, 'name' => $name));
                        }
                    }
                }

                $categories = array();
                $result = $this->db->query("SELECT `board`, `pos`, `location`, `title` FROM `board` WHERE `type`='0' ORDER BY `pos`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['board'])
                        && is_string($row['pos'])
                        && is_string($row['location'])
                        && is_string($row['title'])) {
                        $board = intval(strval($row['board']));
                        if ($this->boardBase->adminAllowed($board, $this->role->getRole())) {
                            $pos = intval(strval($row['pos']));
                            $location = intval(strval($row['location']));
                            $title = $this->basic->convertToHTMLEntities($row['title']);

                            // PHPStan is wrong here.
                            // @phpstan-ignore booleanAnd.leftAlwaysTrue
                            $boardAdmin = ($this->boardBase->adminAllowed($board, $this->role->getRole())
                                            && $this->boardBase->extendedAllowed($board, $this->role->getRole())
                                            && $this->boardBase->writeAllowed($board, $this->role->getRole())
                                            && $this->boardBase->readAllowed($board, $this->role->getRole()));
                            array_push($categories, array('boardAdmin' => $boardAdmin, 'board' => $board, 'pos' => $pos, 'location' => $location, 'title' => $title));
                        }
                    }
                }

                $boards = array();
                $result = $this->db->query("SELECT `board`, `pos`, `location`, `title` FROM `board` WHERE `type`='1' ORDER BY `pos`");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['board'])
                        && is_string($row['location'])
                        && is_string($row['pos'])
                        && is_string($row['title'])) {
                        $board = intval(strval($row['board']));
                        if ($this->boardBase->adminAllowed($board, $this->role->getRole())) {
                            $location = intval(strval($row['location']));
                            $pos = intval(strval($row['pos']));
                            $title = $this->basic->convertToHTMLEntities($row['title']);

                            // PHPStan is wrong here.
                            // @phpstan-ignore booleanAnd.leftAlwaysTrue
                            $boardAdmin = ($this->boardBase->adminAllowed($board, $this->role->getRole())
                                            && $this->boardBase->extendedAllowed($board, $this->role->getRole())
                                            && $this->boardBase->writeAllowed($board, $this->role->getRole())
                                            && $this->boardBase->readAllowed($board, $this->role->getRole()));
                            array_push($boards, array('boardAdmin' => $boardAdmin, 'location' => $location, 'board' => $board, 'pos' => $pos, 'title' => $title));
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/board.main.tpl.php");
            }
        }
    }

    /*
     * Sets the rights of a board.
     */
    public function setRights(int $role, int $board, bool $read, bool $write, bool $extended, bool $admin): void
    {
        $read = $read ? 1 : 0;
        $write = $write ? 1 : 0;
        $extended = $extended ? 1 : 0;
        $admin = $admin ? 1 : 0;
        if ($this->db->isExisting("SELECT `board` FROM `rights_board` WHERE `role`= '$role' AND `board`='$board' LIMIT 1")) {
            $this->db->query("UPDATE `rights_board` SET `read` = '$read', `write` = '$write', `extended` = '$extended', `admin` = '$admin' WHERE `role` = '$role' AND `board` = '$board'");
        } else {
            $this->db->query("INSERT INTO `rights_board`(`role`,`board`,`read`,`write`,`extended`,`admin`) VALUES('$role','$board','$read','$write','$extended','$admin')");
        }
    }

    /*
     * Manages which role has which right on a board.
     */
    private function roleManagement(): void
    {
        $board = $this->requestParametersService->fromGet()->getIntegerParameter("board", -1);
        if ($this->boardBase->adminAllowed($board, $this->role->getRole())
        && $this->boardBase->extendedAllowed($board, $this->role->getRole())
        && $this->boardBase->writeAllowed($board, $this->role->getRole())
        && $this->boardBase->readAllowed($board, $this->role->getRole())) {
            $name = $this->boardBase->getNameById($board);
            $roles = $this->role->getPossibleRoles($this->role->getRole());
            if ($this->requestParametersService->fromPost()->getStringParameter("change", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    foreach ($roles as $roleID) {
                        if ($roleID != $this->role->getRole()) {
                            $read = $this->requestParametersService->fromPost()->getBoolParameter($roleID.'_read', false);
                            $write = $this->requestParametersService->fromPost()->getBoolParameter($roleID.'_write', false);
                            $extended = $this->requestParametersService->fromPost()->getBoolParameter($roleID.'_extended', false);
                            $admin = $this->requestParametersService->fromPost()->getBoolParameter($roleID.'_admin', false);
                            $this->setRights($roleID, $board, $read, $write, $extended, $admin);
                        }
                    }
                }
            }
            $rights = array();
            foreach ($roles as $roleID) {
                if ($roleID != $this->role->getRole()) {
                    if ($this->db->isExisting("SELECT `board` FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board' LIMIT 1")) {
                        $result = $this->db->query("SELECT `role`, `read`, `write`, `extended`, `admin` FROM `rights_board` WHERE `role`='$roleID' AND `board`='$board'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['role'])) {
                                $roleID = intval(strval($row['role']));
                                $roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($roleID));
                                array_push($rights, array('name' => $roleName,'role' => $this->basic->convertToHTMLEntities($row['role']),'read' => $row['read'],'write' => $row['write'],'extended' => $row['extended'],'admin' => $row['admin']));
                            }
                        }
                    } else {
                        $roleName = $this->basic->convertToHTMLEntities($this->role->getNamebyID($roleID));
                        array_push($rights, array('name' => $roleName,'role' => $roleID,'read' => "0",'write' => "0",'extended' => "0",'admin' => "0"));
                    }
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once(dirname(__FILE__)."/../admin/template/board.role.tpl.php");
        }
    }

    /*
     * Manages the operators of a board.
     */
    private function operatorManagement(): void
    {
        $board = $this->requestParametersService->fromGet()->getIntegerParameter("board", -1);
        if ($this->boardBase->adminAllowed($board, $this->role->getRole()) && $this->authentication->moduleAdminAllowed("board", $this->role->getRole())) {
            $operator = $this->requestParametersService->fromPost()->getIntegerParameter("operator", -1);
            if ($this->requestParametersService->fromPost()->getStringParameter("add", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    if ($this->db->isExisting("SELECT `nickname` FROM `user` JOIN `rights_board` USING(`role`) WHERE `extended`='1' AND `read`='1' AND `write`='1' AND `admin`='0' AND `board`='$board' AND `user`='$operator' LIMIT 1")) {
                        $this->db->query("INSERT INTO `board_operator`(`user`,`board`) VALUES('$operator','$board')");
                    }
                }
            }
            if ($this->requestParametersService->fromGet()->getStringParameter("action", "") == "delete") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                    $this->requestParametersService->fromGet()->getStringParameter("token")
                )) {
                    $this->db->query("DELETE FROM `board_operator` WHERE `user`='$operator' AND `board`='$board'");
                }
            }
            $name = $this->boardBase->getNameById($board);
            $operators = array();
            $boardOperators = array();
            $result = $this->db->query("SELECT `user`, `nickname` FROM `user` JOIN `rights_board` USING(`role`) WHERE `extended`='1' AND `read`='1' AND `write`='1' AND `admin`='0' AND `board`='$board'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['user']) && is_string($row['nickname'])) {
                    $user = intval(strval($row['user']));
                    $nickname = $this->basic->convertToHTMLEntities($row['nickname']);
                    if ($this->db->isExisting("SELECT `board` FROM `board_operator` WHERE `user`='$user' AND `board`='$board' LIMIT 1")) {
                        array_push($boardOperators, array('user' => $user, 'nickname' => $nickname));
                    } else {
                        array_push($operators, array('user' => $user, 'nickname' => $nickname));
                    }
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once(dirname(__FILE__)."/../admin/template/board.operator.tpl.php");
        }
    }

    /*
     * Changes the description of a board.
     */
    public function changeDescription(): void
    {
        $board = $this->requestParametersService->fromGet()->getIntegerParameter("board", -1);
        if ($this->boardBase->adminAllowed($board, $this->role->getRole())) {
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") != "") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    $description = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("description", ""));
                    $this->db->query("UPDATE `board` SET `description`='$description' WHERE `board`='$board' AND `type`='1'");
                }
            }
            $name = $this->boardBase->getNameById($board);
            $description = "";
            $result = $this->db->query("SELECT `description` FROM `board` WHERE `board`='$board'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['description'])) {
                    $description = $this->basic->convertToHTMLEntities($row['description']);
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once(dirname(__FILE__)."/../admin/template/board.description.tpl.php");
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

    public function getRestfulURIPartFromOldURL(): string
    {
        $uri = "";
        $action = $this->getAction();
        $threadAction = $this->getThreadAction();
        if (!empty($action)) {
            if ($action == "posts" || $action == "edit" || $action == "answer") {
                $uri = $this->post->getRestfulURIPartFromOldURL($action);
            }
            if ($action == "threads" || $action == "newthread") {
                $uri = $uri.$this->thread->getRestfulURIPartFromOldURL($action);
            }
        }

        if (!empty($threadAction)) {
            if ($threadAction == "posts") {
                $uri = "/".$threadAction;
                $uri = $uri.$this->post->getRestfulURIPartFromOldURL($threadAction);
            }
        }
        return $uri;
    }

    public function getOldURIPartFromRestfulURL(): string
    {
        $uri = "";
        $action = $this->getAction();
        $threadAction = $this->getThreadAction();
        if (!empty($action)) {
            $uri = "&action=".$action;
            if ($action == "posts" || $threadAction == "posts" || $action == "edit" || $action == "answer") {
                $uri = $uri.$this->post->getOldURIPartFromRestfulURL($action);
            }
            if ($action == "threads" || $action == "newthread") {
                $uri = $uri.$this->thread->getOldURIPartFromRestfulURL();
            }
        }
        return $uri;
    }

    private function getAction(): string
    {
        $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($action == "" && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $action = $explodedRequestURI[1];
                if ($action != "threads" && $action != "newthread" && sizeof($explodedRequestURI) == 2) {
                    $action = "threads";
                } elseif ($action != "threads" && $action != "newthread" && sizeof($explodedRequestURI) > 2) {
                    $action = $explodedRequestURI[2];
                }
            }
        }
        return $action;
    }

    private function getThreadAction(): string
    {
        return $this->requestParametersService->fromGet()->getStringParameter("threadaction", "");
    }
}
