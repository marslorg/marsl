<?php

namespace marsl\modules\board;

include_once(dirname(__FILE__)."/../../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../../autoload.php");

use DateTime;
use DateTimeZone;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\Board;
use marsl\modules\Navigation;
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Thread
{
    private Authentication $authentication;
    private Basic $basic;
    private BoardBase $boardBase;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        BoardBase $boardBase,
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->boardBase = $boardBase;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Displays the thread overview in a board.
     */
    public function display(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $boardID = $this->boardBase->getBoardID();
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if (($location == $this->boardBase->getLocation($boardID))
        && ($this->authentication->moduleReadAllowed("board", $this->role->getRole())
            && $this->authentication->locationReadAllowed($location, $this->role->getRole())
            && $this->boardBase->readAllowed($boardID, $this->role->getRole()))) {
            // PHPStan is wrong here.
            $writeAllowed = $this->authentication->moduleWriteAllowed("board", $this->role->getRole())
                            // @phpstan-ignore booleanAnd.rightAlwaysTrue
                            && $this->authentication->locationReadAllowed($location, $this->role->getRole())
                            // @phpstan-ignore booleanAnd.rightAlwaysTrue
                            && $this->boardBase->readAllowed($boardID, $this->role->getRole());
            $globals = array();
            $fixeds = array();
            $page = 1;
            $uri = $this->navigation->getRelativeURI($location, null, false);
            $boardTitle = $this->boardBase->getNameById($boardID);
            $newThreadURI = $uri.$this->getNewThreadURIPart($boardID, $boardTitle);
            $boardURIPart = $this->boardBase->getBoardURIPart($boardID, $boardTitle);
            $threadsURI = $uri.$boardURIPart;
            $tmpPage = $this->getThreadPage();
            if ($tmpPage == 0) {
                $globals = $this->getGlobals($uri);
                $fixeds = $this->getFixeds($uri);
            } else {
                $page = $tmpPage;
                if ($tmpPage == 1) {
                    $globals = $this->getGlobals($uri);
                    $fixeds = $this->getFixeds($uri);
                }
            }
            $threads = array();
            $result = $this->db->query("SELECT COUNT(`board`) AS rowcount FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type` IN ('0','3') AND `board`='$boardID'");
            $pages = $this->db->getRowCount($result) / 15;
            $start = $page * 15 - 15;
            $end = 15;
            $result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date`, `type` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type` IN ('0','3') AND `board`='$boardID' ORDER BY `date` DESC LIMIT $start,$end");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['thread'])
                && is_string($row['post'])
                && is_string($row['postcount'])
                && is_string($row['title'])
                && is_string($row['postauthor'])
                && is_string($row['threadauthor'])
                && is_string($row['viewcount'])
                && is_string($row['date'])
                && is_string($row['type'])) {
                    $thread = intval(strval($row['thread']));
                    $post = intval(strval($row['post']));
                    $postcount = intval(strval($row['postcount'])) - 1;
                    $title = $this->basic->convertToHTMLEntities($row['title']);
                    $postAuthor = intval(strval($row['postauthor']));
                    $threadAuthor = intval(strval($row['threadauthor']));
                    $postNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($postAuthor));
                    $threadNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($threadAuthor));
                    $viewcount = intval(strval($row['viewcount']));
                    $dateTime->setTimestamp(intval(strval($row['date'])));
                    $date = $dateTime->format("d\.m\.Y\, H\:i\:s");
                    $type = "closed";
                    if (intval(strval($row['type'])) == 0) {
                        $type = "open";
                    }
                    $curPage = $this->getPageNumber($thread);
                    $postsURI = $uri.$this->getThreadURIPart($thread, $title, 0);
                    array_push($threads, array('page' => $curPage, 'post' => $post, 'postsURI' => $postsURI, 'thread' => $thread, 'postcount' => $postcount, 'title' => $title, 'postAuthor' => $postAuthor, 'threadAuthor' => $threadAuthor, 'postNickname' => $postNickname, 'threadNickname' => $threadNickname, 'viewcount' => $viewcount, 'date' => $date, 'type' => $type));
                }
            }
            require_once(dirname(__FILE__)."/../../template/board.threads.tpl.php");
        }
    }

    /*
     * Get type of the thread.
     * 0 = normal
     * 1 = fixed
     * 2 = globally fixed
     * 3 = closed
     * 4 = deleted
     */
    public function getType(int $thread): int
    {
        $type = 4;
        $result = $this->db->query("SELECT `type` FROM `thread` WHERE `thread`='$thread'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['type'])) {
                $type = intval(strval($row['type']));
            }
        }
        return $type;
    }

    /*
     * Get the number of pages a thread is containing.
     */
    public function getPageNumber(int $thread): int
    {
        $result = $this->db->query("SELECT COUNT(`thread`) AS rowcount FROM `post` WHERE `thread`='$thread' AND `deleted`='0'");
        $pages = intval(ceil($this->db->getRowCount($result) / 10));
        return $pages;
    }

    /*
     * Get the title of a thread.
     */
    public function getTitle(int $thread): string
    {
        $title = "";
        $result = $this->db->query("SELECT `title` FROM `thread` WHERE `thread`='$thread' AND NOT (`type`='4')");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['title'])) {
                $title = $this->basic->convertToHTMLEntities($row['title']);
            }
        }
        return $title;
    }

    /**
     * Get globally fixed threads.
     * @return array<array<mixed>>
     */
    private function getGlobals(string $uri): array
    {
        $globals = array();
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        $result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type`='2' ORDER BY `date` DESC");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['board'])
            && is_string($row['thread'])
            && is_string($row['post'])
            && is_string($row['postcount'])
            && is_string($row['title'])
            && is_string($row['postauthor'])
            && is_string($row['threadauthor'])
            && is_string($row['viewcount'])
            && is_string($row['date'])) {
                if ($this->authentication->locationReadAllowed($this->boardBase->getLocation(intval(strval($row['board']))), $this->role->getRole())
                && $this->boardBase->readAllowed(intval(strval($row['board'])), $this->role->getRole())) {
                    $thread = intval(strval($row['thread']));
                    $post = intval(strval($row['post']));
                    $postcount = intval(strval($row['postcount'])) - 1;
                    $title = $this->basic->convertToHTMLEntities($row['title']);
                    $postAuthor = intval(strval($row['postauthor']));
                    $threadAuthor = intval(strval($row['threadauthor']));
                    $postNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($postAuthor));
                    $threadNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($threadAuthor));
                    $viewcount = intval(strval($row['viewcount']));
                    $dateTime->setTimestamp(intval(strval($row['date'])));
                    $date = $dateTime->format("d\.m\.Y\, H\:i\:s");
                    $page = $this->getPageNumber($thread);
                    $postsURI = $uri.$this->getThreadURIPart($thread, $title, 0);
                    array_push($globals, array('page' => $page, 'post' => $post, 'postsURI' => $postsURI, 'thread' => $thread, 'postcount' => $postcount, 'title' => $title, 'postAuthor' => $postAuthor, 'threadAuthor' => $threadAuthor, 'postNickname' => $postNickname, 'threadNickname' => $threadNickname, 'viewcount' => $viewcount, 'date' => $date));
                }
            }
        }
        return $globals;
    }

    /**
     * Get fixed threads.
     * @return array<array<mixed>>
     */
    private function getFixeds(string $uri)
    {
        $board = $this->boardBase->getBoardID();
        $fixeds = array();
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        $result = $this->db->query("SELECT `post`, `thread`.`thread` AS `thread`, `board`, `postcount`, `title`, `thread`.`author` AS `threadauthor`, `post`.`author` AS `postauthor`, `viewcount`, `date` FROM `thread` JOIN `post` ON (`lastpost`=`post`) WHERE `type`='1' AND `board`='$board' ORDER BY `date` DESC");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['thread'])
            && is_string($row['post'])
            && is_string($row['postcount'])
            && is_string($row['title'])
            && is_string($row['postauthor'])
            && is_string($row['threadauthor'])
            && is_string($row['viewcount'])
            && is_string($row['date'])) {
                $thread = intval(strval($row['thread']));
                $post = intval(strval($row['post']));
                $postcount = intval(strval($row['postcount'])) - 1;
                $title = $this->basic->convertToHTMLEntities($row['title']);
                $postAuthor = intval(strval($row['postauthor']));
                $threadAuthor = intval(strval($row['threadauthor']));
                $postNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($postAuthor));
                $threadNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($threadAuthor));
                $viewcount = intval(strval($row['viewcount']));
                $dateTime->setTimestamp(intval(strval($row['date'])));
                $date = $dateTime->format("d\.m\.Y\, H\:i\:s");
                $page = $this->getPageNumber($thread);
                $postsURI = $uri.$this->getThreadURIPart($thread, $title, 0);
                array_push($fixeds, array('page' => $page, 'post' => $post, 'postsURI' => $postsURI, 'thread' => $thread, 'postcount' => $postcount, 'title' => $title, 'postAuthor' => $postAuthor, 'threadAuthor' => $threadAuthor, 'postNickname' => $postNickname, 'threadNickname' => $threadNickname, 'viewcount' => $viewcount, 'date' => $date));
            }
        }
        return $fixeds;
    }

    /*
     * Get the parent board of a thread.
     */
    public function getBoard(int $thread): int
    {
        $board = -1;
        $result = $this->db->query("SELECT `board` FROM `thread` WHERE `thread`='$thread'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['board'])) {
                $board = intval(strval($row['board']));
            }
        }
        return $board;
    }

    /*
     * Move thread to another board.
     */
    public function moveThread(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $threadID = $this->getThreadID();
        $threadTitle = $this->getNameById($threadID);
        $boardID = $this->getBoard($threadID);
        $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
        $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
        $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
        if (($location == $this->boardBase->getLocation($boardID)) && ($isGlobalAdmin || $isOperator || $isAdmin)) {
            $uri = $this->navigation->getRelativeURI($location, null, false);
            $uri = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
            $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
            if ($do != "") {
                if ($do == "move") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $destinationID = $this->requestParametersService->fromPost()->getIntegerParameter("destination", -1);
                        if ($this->boardBase->readAllowed($destinationID, $this->role->getRole())
                        && $this->boardBase->writeAllowed($destinationID, $this->role->getRole())
                        && $this->authentication->locationReadAllowed($this->boardBase->getLocation($destinationID), $this->role->getRole())
                        && $this->authentication->locationWriteAllowed($this->boardBase->getLocation($destinationID), $this->role->getRole())
                        && $this->authentication->moduleReadAllowed("board", $this->role->getRole())
                        && $this->authentication->moduleWriteAllowed("board", $this->role->getRole())) {
                            $this->db->query("UPDATE `thread` SET `board`='$destinationID' WHERE `thread`='$threadID'");

                            $result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
                            while ($row = $this->db->fetchArray($result)) {
                                if (is_string($row['postcount'])) {
                                    $postcount = intval(strval($row['postcount']));
                                    $result2 = $this->db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$boardID'");
                                    while ($row2 = $this->db->fetchArray($result2)) {
                                        if (is_string($row2['threadcount'])
                                        && is_string($row2['postcount'])) {
                                            $threadcount = intval(strval($row2['threadcount'])) - 1;
                                            $newPostcount = intval(strval($row2['postcount'])) - $postcount;
                                            $this->db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$newPostcount' WHERE `board`='$boardID'");
                                        }
                                    }
                                    $result2 = $this->db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$destinationID'");
                                    while ($row2 = $this->db->fetchArray($result2)) {
                                        if (is_string($row2['threadcount'])
                                        && is_string($row2['postcount'])) {
                                            $threadcount = intval(strval($row2['threadcount'])) + 1;
                                            $newPostcount = intval(strval($row2['postcount'])) + $postcount;
                                            $this->db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$newPostcount' WHERE `board`='$destinationID'");
                                        }
                                    }
                                }
                            }
                            echo "<div class=\"success\">Das Thema wurde verschoben! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$uri."\">hier</a>.</div><script>top.location.href='".$uri."'</script>";
                        }
                    }
                }
            } else {
                $boards = array();
                $result = $this->db->query("SELECT `board`, `title` FROM `board` WHERE `type`='1'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['board'])
                    && is_string($row['title'])) {
                        $destinationID = intval(strval($row['board']));
                        $destinationTitle = $this->basic->convertToHTMLEntities($row['title']);
                        if ($this->boardBase->readAllowed($destinationID, $this->role->getRole())
                        && $this->boardBase->writeAllowed($destinationID, $this->role->getRole())
                        && $this->authentication->locationReadAllowed($this->boardBase->getLocation($destinationID), $this->role->getRole())
                        && $this->authentication->locationWriteAllowed($this->boardBase->getLocation($destinationID), $this->role->getRole())
                        && $this->authentication->moduleReadAllowed("board", $this->role->getRole())
                        && $this->authentication->moduleWriteAllowed("board", $this->role->getRole())) {
                            array_push($boards, array('board' => $destinationID, 'title' => $destinationTitle));
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                $title = $this->getTitle($threadID);
                $moveURI = $uri.$this->getMoveURI($threadID);
                require_once(dirname(__FILE__)."/../../template/board.move.tpl.php");
            }
        }
    }

    /*
     * Change the title of a thread.
     */
    public function changeTitle(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $threadID = $this->getThreadID();
        $threadTitle = $this->getNameById($threadID);
        $boardID = $this->getBoard($threadID);
        $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
        $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
        $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
        if (($location == $this->boardBase->getLocation($boardID)) && ($isGlobalAdmin || $isOperator || $isAdmin)) {
            $uri = $this->navigation->getRelativeURI($location, null, false);
            $uri = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
            $titleURI = $uri.$this->getChangeTitleURI($threadID);
            $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
            if ($do != "") {
                if ($do == "change") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $title = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("title"));
                        $this->db->query("UPDATE `thread` SET `title`='$title' WHERE `thread`='$threadID'");
                        echo "<div class=\"success\">Der Titel wurde ge&auml;ndert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$uri."\">hier</a>.</div><script>top.location.href='".$uri."'</script>";
                    }
                }
            } else {
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                $title = $this->getTitle($threadID);
                require_once(dirname(__FILE__)."/../../template/board.change.tpl.php");
            }
        }
    }

    /*
     * Open up a closed thread.
     */
    public function open(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $pageID = $this->navigation->getPageID();
            $location = $pageID;
            $threadID = $this->getThreadID();
            $threadTitle = $this->getNameById($threadID);
            $boardID = $this->getBoard($threadID);
            $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
            $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
            $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
            if (($location == $this->boardBase->getLocation($boardID)) && ($isGlobalAdmin || $isOperator || $isAdmin)) {
                $this->db->query("UPDATE `thread` SET `type`='0' WHERE `thread`='$threadID'");
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $link = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
                echo "<div class=\"success\">Das Thema wurde ge&ouml;ffnet! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
            }
        }
    }

    /*
     * Close a thread.
     */
    public function close(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $pageID = $this->navigation->getPageID();
            $location = $pageID;
            $threadID = $this->getThreadID();
            $threadTitle = $this->getNameById($threadID);
            $boardID = $this->getBoard($threadID);
            $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
            $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
            $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
            if (($location == $this->boardBase->getLocation($boardID)) && ($isGlobalAdmin || $isOperator || $isAdmin)) {
                $this->db->query("UPDATE `thread` SET `type`='3' WHERE `thread`='$threadID'");
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $link = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
                echo "<div class=\"success\">Das Thema wurde geschlossen! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
            }
        }
    }

    /*
     * Delete a thread.
     */
    public function delete(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $pageID = $this->navigation->getPageID();
            $location = $pageID;
            $threadID = $this->getThreadID();
            $boardID = $this->getBoard($threadID);
            $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
            $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
            $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
            if (($location == $this->boardBase->getLocation($boardID)) && ($isGlobalAdmin || $isOperator || $isAdmin)) {
                $this->db->query("UPDATE `thread` SET `type`='4' WHERE `thread`='$threadID'");
                $result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['postcount'])) {
                        $postcount = intval(strval($row['postcount']));
                        $result2 = $this->db->query("SELECT `threadcount`, `postcount` FROM `board` WHERE `board`='$boardID'");
                        while ($row2 = $this->db->fetchArray($result2)) {
                            if (is_string($row2['threadcount'])
                            && is_string($row2['postcount'])) {
                                $threadcount = intval(strval($row2['threadcount'])) - 1;
                                $postcount = intval(strval($row2['postcount'])) - $postcount;
                                $this->db->query("UPDATE `board` SET `threadcount`='$threadcount', `postcount`='$postcount' WHERE `board`='$boardID'");
                            }
                        }
                    }
                }
                $uri = $this->navigation->getRelativeURI($location, null, false);

                $boardTitle = $this->boardBase->getNameById($boardID);
                $boardURIPart = $this->boardBase->getBoardURIPart($boardID, $boardTitle);
                $link = $uri.$boardURIPart;
                echo "<div class=\"success\">Das Thema wurde gel&ouml;scht! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
            }
        }
    }

    /*
     * Globally fix a thread.
     */
    public function fixGlobal(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $pageID = $this->navigation->getPageID();
            $location = $pageID;
            $threadID = $this->getThreadID();
            $threadTitle = $this->getNameById($threadID);
            $boardID = $this->getBoard($threadID);
            $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
            if (($location == $this->boardBase->getLocation($boardID)) && $isGlobalAdmin) {
                $this->db->query("UPDATE `thread` SET `type`='2' WHERE `thread`='$threadID'");
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $link = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
                echo "<div class=\"success\">Das Thema wurde global fixiert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
            }
        }
    }

    /*
     * Locally fix a thread.
     */
    public function fixLocal(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $pageID = $this->navigation->getPageID();
            $location = $pageID;
            $threadID = $this->getThreadID();
            $threadTitle = $this->getNameById($threadID);
            $boardID = $this->getBoard($threadID);
            $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
            $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
            $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
            if (($location == $this->boardBase->getLocation($boardID)) && ($isGlobalAdmin || $isOperator || $isAdmin)) {
                $this->db->query("UPDATE `thread` SET `type`='1' WHERE `thread`='$threadID'");
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $link = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
                echo "<div class=\"success\">Das Thema wurde fixiert! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
            }
        }
    }

    /*
     * Remove all fixations of a thread.
     */
    public function removeFixation(): void
    {
        if ($this->authentication->checkToken(
            $this->requestParametersService->fromGet()->getIntegerParameter("time"),
            $this->requestParametersService->fromGet()->getStringParameter("token")
        )) {
            $pageID = $this->navigation->getPageID();
            $location = $pageID;
            $threadID = $this->getThreadID();
            $threadTitle = $this->getNameById($threadID);
            $boardID = $this->getBoard($threadID);
            $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
            if (($location == $this->boardBase->getLocation($boardID)) && $isGlobalAdmin) {
                $this->db->query("UPDATE `thread` SET `type`='0' WHERE `thread`='$threadID'");
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $link = $uri.$this->getThreadURIPart($threadID, $threadTitle, 0);
                echo "<div class=\"success\">Die Ank&uuml;ndigung wurde aufgehoben! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
            }
        }
    }

    /*
     * Create a new thread.
     */
    public function newThread(): void
    {
        $boardID = $this->boardBase->getBoardID();
        $boardTitle = $this->boardBase->getNameById($boardID);
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $isAdmin = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->authentication->moduleAdminAllowed("board", $this->role->getRole()) || $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
        if (($location == $this->boardBase->getLocation($boardID))
        && $this->boardBase->readAllowed($boardID, $this->role->getRole())
        && $this->boardBase->writeAllowed($boardID, $this->role->getRole())
        && $this->authentication->locationReadAllowed($location, $this->role->getRole())
        && $this->authentication->locationWriteAllowed($location, $this->role->getRole())
        && $this->authentication->moduleReadAllowed("board", $this->role->getRole())
        && $this->authentication->moduleWriteAllowed("board", $this->role->getRole())) {
            $uri = $this->navigation->getRelativeURI($location, null, false);
            $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
            if ($do != "") {
                if ($do == "newthread") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $title = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("title", ""));
                        $content = $this->db->escapeString($this->basic->cleanStrict($this->requestParametersService->fromPost()->getStringParameter("content", "")));
                        $author = $this->user->getID();
                        $time = time();
                        $ip = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR", ""));
                        $this->db->query("INSERT INTO `thread`(`board`,`postcount`,`type`,`title`,`author`,`viewcount`,`lastpost`) VALUES('$boardID','0','0','$title','$author','0','0')");
                        $threadID = (int)$this->db->lastInsertedID();
                        $result = $this->db->query("SELECT `threadcount` FROM `board` WHERE `board`='$boardID'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['threadcount'])) {
                                $threadcount = intval(strval($row['threadcount'])) + 1;
                                $this->db->query("UPDATE `board` SET `threadcount`='$threadcount' WHERE `board`='$boardID'");
                            }
                        }

                        $this->db->query("INSERT INTO `post`(`author`, `thread`, `date`, `operator`, `lastedit`, `content`, `ip`, `deleted`) VALUES('$author','$threadID','$time','0','0','$content','$ip','0')");
                        $postID = $this->db->lastInsertedID();
                        $result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['postcount'])) {
                                $postcount = intval(strval($row['postcount'])) + 1;
                                $this->db->query("UPDATE `thread` SET `postcount`='$postcount', `lastpost`='$postID' WHERE `thread`='$threadID'");
                            }
                        }
                        $result = $this->db->query("SELECT `postcount` FROM `board` WHERE `board`='$boardID'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['postcount'])) {
                                $postcount = intval(strval($row['postcount'])) + 1;
                                $this->db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$boardID'");
                            }
                        }

                        $temporary = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("temporary", ""));
                        $result = $this->db->query("SELECT `file` FROM `attachment` WHERE `temporary`='$temporary'");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['file'])) {
                                $newTemporary = $this->basic->tempFileKey();
                                $file = $row['file'];
                                $this->db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
                                $this->db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
                            }
                        }

                        $page = $this->getPageNumber($threadID);
                        $pagePostsURI = $uri.$this->getThreadURIPart($threadID, $title, $page);
                        $link = $pagePostsURI."#".$postID;
                        echo "<div class=\"success\">Das Thema wurde erfolgreich erstellt! Du wirst nun weitergeleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
                    }
                }
            } else {
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                $temporaryKey = $this->basic->tempFileKey();
                $newThreadURI = $uri.$this->getNewThreadURIPart($boardID, $boardTitle);
                require_once(dirname(__FILE__)."/../../template/board.newthread.tpl.php");
            }
        }
    }

    public function generateRestfulURIByID(int $thread): string
    {
        $result = "";
        $sqlResult = $this->db->query("SELECT `title` FROM `thread` WHERE `thread`='$thread'");
        while ($row = $this->db->fetchArray($sqlResult)) {
            if (is_string($row['title'])) {
                $title = $row['title'];
                $result = $this->navigation->generateRestfulURI($thread, $title);
            }
        }
        return $result;
    }

    public function getThreadPage(): int
    {
        $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", 1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($page == 1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 3) {
                $page = intval($explodedRequestURI[3]);
            }
        }
        return $page;
    }

    public function getThreadID(): int
    {
        $threadID = $this->requestParametersService->fromGet()->getIntegerParameter("thread", 0);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($threadID == 0 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $threadSlug = $explodedRequestURI[1];
                $explodedThreadSlug = explode('-', $threadSlug);
                $explodedThreadSlugSize = sizeof($explodedThreadSlug);

                // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
                // @phpstan-ignore greater.alwaysTrue
                if ($explodedThreadSlugSize > 0) {
                    $threadID = intval($explodedThreadSlug[$explodedThreadSlugSize - 1]);
                }
            }
        }
        return $threadID;
    }

    public function getNameById(int $thread): string
    {
        $title = "";
        $result = $this->db->query("SELECT `title` FROM `thread` WHERE `thread`='$thread'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['title'])) {
                $title = $row['title'];
            }
        }
        return $title;
    }

    public function getThreadURIPart(int $threadID, string $threadTitle, int $page): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&action=posts&thread=".$threadID;
            if ($page > 0) {
                $result = $result."&page=".$page;
            }
        } else {
            $result = "/".$this->navigation->generateRestfulURI($threadID, $threadTitle)."/posts";
            if ($page > 0) {
                $result = $result."/".$page;
            }
        }

        $quote = $this->requestParametersService->fromGet()->getIntegerParameter("quote", -1);
        if ($quote != -1) {
            $result = $result."#".$quote;
        }

        return $result;
    }

    // Method used in PHP template file.
    // @phpstan-ignore method.unused
    private function getPageURIFormatted(int $page): string
    {
        return $this->boardBase->getPageURIFormatted($page);
    }

    private function getMoveURI(int $threadID): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&";
        } else {
            $result = "?";
        }
        return $result."threadaction=move&thread=".$threadID;
    }

    private function getChangeTitleURI(int $threadID): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&";
        } else {
            $result = "?";
        }
        return $result."threadaction=title&thread=".$threadID;
    }

    private function getNewThreadURIPart(int $boardID, string $boardTitle): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&action=newthread&board=".$boardID;
        } else {
            $result = "/".$this->navigation->generateRestfulURI($boardID, $boardTitle)."/newthread";
        }
        return $result;
    }

    public function getRestfulURIPartFromOldURL(string $action): string
    {
        $result = "";
        $boardID = $this->boardBase->getBoardID();
        $boardTitle = $this->boardBase->getNameById($boardID);

        if ($action == "threads") {
            $result = $this->boardBase->getBoardURIPart($boardID, $boardTitle);
        } elseif ($action == "newthread") {
            $result = $this->getNewThreadURIPart($boardID, $boardTitle);
        }

        return $result;
    }

    public function getOldURIPartFromRestfulURL(): string
    {
        $boardID = $this->boardBase->getBoardID();
        return "&board=".$boardID;
    }
}
