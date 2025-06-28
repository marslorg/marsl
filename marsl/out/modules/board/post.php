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

class Post
{
    private Authentication $authentication;
    private Basic $basic;
    private BoardBase $boardBase;
    private Configuration $configuration;
    private DB $db;
    private IRequestParametersService $requestParametersService;
    private Navigation $navigation;
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
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->thread = $thread;
        $this->user = $user;
    }

    /*
     * Displays the content of a thread.
     */
    public function display(): void
    {
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->authentication->moduleReadAllowed("board", $this->role->getRole())
        && $this->authentication->locationReadAllowed($location, $this->role->getRole())) {
            $threadID = $this->thread->getThreadID();
            $threadTitle = $this->thread->getNameById($threadID);
            $boardID = $this->thread->getBoard($threadID);
            if (($location == $this->boardBase->getLocation($boardID))
            && ($this->boardBase->readAllowed($boardID, $this->role->getRole())
            && $this->authentication->locationReadAllowed($this->boardBase->getLocation($boardID), $this->role->getRole()))) {
                if ($this->requestParametersService->fromGet()->getStringParameter("do", "") != ""
                || $this->requestParametersService->fromPost()->getStringParameter("do", "") != "") {
                    $this->doThings();
                }
                $posts = array();
                $page = $this->thread->getThreadPage();
                if (!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1")) {
                    $result = $this->db->query("SELECT COUNT(`thread`) AS rowcount FROM `post` WHERE `thread`='$threadID' AND `deleted`='0'");
                    $pages = $this->db->getRowCount($result) / 10;
                    $start = $page * 10 - 10;
                    $end = 10;

                    $isAuthor = (($this->thread->getType($threadID) != 3)
                                && ($this->boardBase->readAllowed($boardID, $this->role->getRole())
                                    && $this->boardBase->writeAllowed($boardID, $this->role->getRole())
                                    && $this->authentication->locationReadAllowed($location, $this->role->getRole())
                                    && $this->authentication->locationWriteAllowed($location, $this->role->getRole())
                                    && $this->authentication->moduleReadAllowed("board", $this->role->getRole())
                                    && $this->authentication->moduleWriteAllowed("board", $this->role->getRole())));
                    $isOperator = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()));
                    $isAdmin = $this->boardBase->isAdmin($boardID, $this->user->getID());
                    $isGlobalAdmin = ($this->authentication->moduleAdminAllowed("board", $this->role->getRole()) && $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
                    $uri = $this->navigation->getRelativeURI($location, null, false);
                    $threadURI = $uri.$this->thread->getThreadURIPart($threadID, $threadTitle, 0);
                    $answerURI = $uri.$this->getAnswerURIPart($threadID, $threadTitle, $page);
                    $result = $this->db->query("SELECT `viewcount` FROM `thread` WHERE `thread`='$threadID'");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['viewcount'])) {
                            $viewcount = intval(strval($row['viewcount'])) + 1;
                            $this->db->query("UPDATE `thread` SET `viewcount`='$viewcount' WHERE `thread`='$threadID'");
                        }
                    }

                    $authTime = time();
                    $authToken = $this->authentication->getToken($authTime);

                    $result = $this->db->query("SELECT `post`, `date`, `operator`, `lastedit`, `content`, `ip`, `author` FROM `post` WHERE `deleted`='0' AND `thread`='$threadID' ORDER BY `date` LIMIT $start,$end");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['post'])
                        && is_string($row['date'])
                        && is_string($row['operator'])
                        && is_string($row['lastedit'])
                        && is_string($row['content'])
                        && is_string($row['ip'])
                        && is_string($row['author'])) {
                            $post = intval(strval($row['post']));
                            $dateTime->setTimestamp(intval(strval($row['date'])));
                            $date = $dateTime->format("\a\m d\.m\.Y\ \u\m H\:i\:s");
                            $operator = intval(strval($row['operator']));
                            $operatorNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($operator));
                            $dateTime->setTimestamp(intval(strval($row['lastedit'])));
                            $lastedit = $dateTime->format("\a\m d\.m\.Y\ \u\m H\:i\:s");
                            $content = $row['content'];
                            $ip = $this->basic->convertToHTMLEntities($row['ip']);
                            $author = intval(strval($row['author']));
                            $authorNickname = $this->basic->convertToHTMLEntities($this->user->getNickbyID($author));
                            $editable = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID()) || ((($this->user->getID() == $author) && ($this->boardBase->writeAllowed($boardID, $this->role->getRole())))));
                            $files = array();
                            $result2 = $this->db->query("SELECT `file`, `realname` FROM `post_attachment` NATURAL JOIN `attachment` WHERE `post`='$post'");
                            while ($row2 = $this->db->fetchArray($result2)) {
                                if (is_string($row2['realname'])
                                && is_string($row2['file'])) {
                                    $filename = $this->basic->convertToHTMLEntities($row2['realname']);
                                    $file = intval(strval($row2['file']));
                                    array_push($files, array('filename' => $filename, 'file' => $file));
                                }
                            }
                            $quoteURI = $answerURI.$this->getQuoteURIPart($post);
                            $editURI = $uri.$this->getEditURIPart($threadID, $threadTitle, $post, $page);
                            $deleteURI = $threadURI.$this->getDeleteURIPart($post, $authTime, $authToken);
                            array_push($posts, array('post' => $post, 'date' => $date, 'operator' => $operator, 'operatorNickname' => $operatorNickname, 'quoteURI' => $quoteURI, 'editURI' => $editURI, 'deleteURI' => $deleteURI, 'lastedit' => $lastedit, 'content' => $content, 'ip' => $ip, 'author' => $author, 'authorNickname' => $authorNickname, 'editable' => $editable, 'files' => $files));
                        }
                    }
                    $oldURIsEnabled = $this->configuration->getEnableOldURIs();
                    require_once(dirname(__FILE__)."/../../template/board.posts.tpl.php");
                }
            }
        }
    }

    /*
     * Do small functions which can be applied to a post.
     */
    private function doThings(): void
    {
        if ($this->requestParametersService->fromGet()->getStringParameter("do", "") == "del") {
            if ($this->authentication->checkToken(
                $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                $this->requestParametersService->fromGet()->getStringParameter("token")
            )) {
                $postID = $this->requestParametersService->fromGet()->getIntegerParameter("post", -1);
                $threadID = $this->getThread($postID);
                if ($threadID == $this->thread->getThreadID() && (!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1"))) {
                    $boardID = $this->thread->getBoard($threadID);
                    if ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->boardBase->isOperator($boardID, $this->user->getID())) {
                        $this->db->query("UPDATE `post` SET `deleted`='1' WHERE `post`='$postID'");
                        if ($this->db->isExisting("SELECT `post` FROM `post` WHERE `deleted`='1' AND `post`='$postID' LIMIT 1")) {
                            $result = $this->db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
                            while ($row = $this->db->fetchArray($result)) {
                                if (is_string($row['postcount'])) {
                                    $postcount = intval(strval($row['postcount'])) - 1;
                                    $this->db->query("UPDATE `thread` SET `postcount`='$postcount' WHERE `thread`='$threadID'");
                                }
                            }
                            $result = $this->db->query("SELECT `postcount` FROM `board` WHERE `board`='$boardID'");
                            while ($row = $this->db->fetchArray($result)) {
                                if (is_string($row['postcount'])) {
                                    $postcount = intval(strval($row['postcount'])) - 1;
                                    $this->db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$boardID'");
                                }
                            }
                            echo "<div class=\"success\">Der Post wurde erfolgreich gel&ouml;scht!</div>";
                        } else {
                            echo "<div class=\"caution\">Achtung, der Post wurde nicht gel&ouml;scht!</div>";
                        }
                    }
                }
            }
        }
    }

    /*
     * Get the thread ID of a post.
     */
    public function getThread(int $post): int
    {
        $thread = -1;
        $result = $this->db->query("SELECT `thread` FROM `post` WHERE `post`='$post'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['thread'])) {
                $thread = intval(strval($row['thread']));
            }
        }
        return $thread;
    }

    /*
     * Get the author of a post.
     */
    public function getAuthor(int $post): int
    {
        $author = -1;
        $result = $this->db->query("SELECT `author` FROM `post` WHERE `post`='$post'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['author'])) {
                $author = intval(strval($row['author']));
            }
        }
        return $author;
    }

    /*
     * Dialog to insert a new post.
     */
    public function answer(): void
    {
        $threadID = $this->thread->getThreadID();
        $threadTitle = $this->thread->getNameById($threadID);
        $boardID = $this->thread->getBoard($threadID);
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $isAdmin = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->authentication->moduleAdminAllowed("board", $this->role->getRole()) || $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
        if (($location == $this->boardBase->getLocation($boardID)) && (!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type` IN ('4','3') LIMIT 1"))) {
            if ($this->boardBase->readAllowed($boardID, $this->role->getRole())
            && $this->boardBase->writeAllowed($boardID, $this->role->getRole())
            && $this->authentication->locationReadAllowed($location, $this->role->getRole())
            && $this->authentication->locationWriteAllowed($location, $this->role->getRole())
            && $this->authentication->moduleReadAllowed("board", $this->role->getRole())
            && $this->authentication->moduleWriteAllowed("board", $this->role->getRole())) {
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $page = $this->thread->getThreadPage();
                $postsURI = $uri.$this->thread->getThreadURIPart($threadID, $threadTitle, $page);
                $answerURI = $uri.$this->getAnswerURIPart($threadID, $threadTitle, $page);
                $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
                if ($do != "") {
                    if ($do == "answer") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                            $this->requestParametersService->fromPost()->getStringParameter("authToken")
                        )) {
                            $content = $this->db->escapeString($this->basic->cleanStrict($this->requestParametersService->fromPost()->getStringParameter("content", "")));
                            $author = $this->user->getID();
                            $time = time();
                            $ip = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter($this->configuration->getRemoteIPFieldName(), ""));
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
                                    $file = intval(strval($row['file']));
                                    $this->db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
                                    $this->db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
                                }
                            }

                            $link = $postsURI."#".$postID;
                            echo "<div class=\"success\">Deine Antwort wurde erfolgreich gespeichert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
                        }
                    }
                } else {
                    $quote = $this->requestParametersService->fromGet()->getIntegerParameter("quote", -1);
                    if ($quote != -1) {
                        $postID = $quote;
                        $page = $this->thread->getThreadPage();
                        if ($threadID == $this->getThread($postID)) {
                            $result = $this->db->query("SELECT `content`, `author` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
                            while ($row = $this->db->fetchArray($result)) {
                                if (is_string($row['author'])
                                    && ($row['content'] == null || is_string($row['content']))) {
                                    $authorNickname = $this->user->getNickbyID(intval(strval($row['author'])));
                                    $content = is_string($row['content']) ? $row['content'] : "";
                                    $quote = "<blockquote>".$authorNickname." <a href=\"".$postsURI."\">schrieb</a>:<br /><br />".$content."</blockquote><br />";
                                }
                            }
                        }
                    }
                    $title = $this->thread->getTitle($threadID);
                    $authTime = time();
                    $authToken = $this->authentication->getToken($authTime);
                    $temporaryKey = $this->basic->tempFileKey();
                    require_once(dirname(__FILE__)."/../../template/board.answer.tpl.php");
                }
            }
        }
    }

    /*
     * Dialog to edit a post.
     */
    public function edit(): void
    {
        $postID = $this->getPostID();
        $author = $this->getAuthor($postID);
        $pageID = $this->navigation->getPageID();
        $location = $pageID;
        $threadID = $this->getThread($postID);
        $threadTitle = $this->thread->getNameById($threadID);
        $boardID = $this->thread->getBoard($threadID);
        $page = $this->thread->getThreadPage();
        if (($location == $this->boardBase->getLocation($boardID))
        && (!$this->db->isExisting("SELECT `type` FROM `thread` WHERE `thread`='$threadID' AND `type`='4' LIMIT 1"))) {
            if ($this->boardBase->isAdmin($boardID, $this->user->getID())
            || $this->boardBase->isOperator($boardID, $this->user->getID())
            || ((($this->user->getID() == $author) && ($this->boardBase->writeAllowed($boardID, $this->role->getRole()))))) {
                $uri = $this->navigation->getRelativeURI($location, null, false);
                $pagePostsURI = $uri.$this->thread->getThreadURIPart($threadID, $threadTitle, $page);
                $pagePostEditURI = $uri.$this->getEditURIPart($threadID, $threadTitle, $postID, $page);
                $do = $this->requestParametersService->fromPost()->getStringParameter("do", "");
                if ($do != "") {
                    if ($do == "edit") {
                        if ($this->authentication->checkToken(
                            $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                            $this->requestParametersService->fromPost()->getStringParameter("authToken")
                        )) {
                            $content = $this->db->escapeString($this->basic->cleanStrict($this->requestParametersService->fromPost()->getStringParameter("content", "")));
                            $operator = $this->user->getID();
                            $time = time();
                            $link = $pagePostsURI."#".$postID;
                            $this->db->query("UPDATE `post` SET `content`='$content', `operator`='$operator', `lastedit`='$time' WHERE `post`='$postID'");
                            if ($this->db->isExisting("SELECT `post` FROM `post` WHERE `post`='$postID' AND `content`='$content' AND `operator`='$operator' AND `lastedit`='$time' LIMIT 1")) {

                                $temporary = $this->db->escapeString($this->requestParametersService->fromPost()->getStringParameter("temporary", ""));
                                $result = $this->db->query("SELECT `file` FROM `attachment` WHERE `temporary`='$temporary'");
                                while ($row = $this->db->fetchArray($result)) {
                                    if (is_string($row['file'])) {
                                        $newTemporary = $this->basic->tempFileKey();
                                        $file = intval(strval($row['file']));
                                        $this->db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$file')");
                                        $this->db->query("UPDATE `attachment` SET `temporary`='$newTemporary' WHERE `file`='$file'");
                                    }
                                }

                                echo "<div class=\"success\">Der Post wurde erfolgreich ge&auml;ndert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"".$link."\">hier</a>.</div><script>top.location.href='".$link."'</script>";
                            } else {
                                echo "<div class=\"caution\">Achtung, der Post wurde nicht ge&auml;ndert! Du wirst gleich zur&uuml;ck geleitet. Wenn es nicht automatisch weiter geht, klicke <a href=\"javascript:history.back()\">hier</a>.</div><script>top.location.href='javascript:history.back()'</script>";
                            }
                        }
                    }
                } else {
                    $isAdmin = ($this->boardBase->isAdmin($boardID, $this->user->getID()) || $this->authentication->moduleAdminAllowed("board", $this->role->getRole()) || $this->authentication->locationAdminAllowed($location, $this->role->getRole()));
                    $content = "";
                    $result = $this->db->query("SELECT `content` FROM `post` WHERE `post`='$postID' AND `deleted`='0'");
                    while ($row = $this->db->fetchArray($result)) {
                        $content = $row['content'];
                        $authTime = time();
                        $authToken = $this->authentication->getToken($authTime);
                        $temporaryKey = $this->basic->tempFileKey();
                        require_once(dirname(__FILE__)."/../../template/board.edit.tpl.php");
                    }
                }
            }
        }
    }

    private function getQuoteURIPart(int $postID): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&quote=".$postID;
        } else {
            $result = "?quote=".$postID;
        }
        return $result;
    }

    private function getDeleteURIPart(int $postID, int $authTime, string $authToken): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&";
        } else {
            $result = "?";
        }
        $result = $result."do=del&post=".$postID."&time=".$authTime."&token=".$authToken;
        return $result;
    }

    private function getEditURIPart(int $threadID, string $threadTitle, int $postID, int $page): string
    {
        $result = "";

        if ($this->configuration->getEnableOldURIs()) {
            $result = "&action=edit&thread=".$threadID."&post=".$postID."&page=".$page;
        } else {
            $result = "/".$this->navigation->generateRestfulURI($threadID, $threadTitle)."/edit/".$page."/".$postID;
        }

        return $result;
    }

    private function getAnswerURIPart(int $threadID, string $threadTitle, int $page): string
    {
        $result = "";
        if ($this->configuration->getEnableOldURIs()) {
            $result = "&action=answer&thread=".$threadID."&page=".$page;
        } else {
            $result = "/".$this->navigation->generateRestfulURI($threadID, $threadTitle)."/answer/".$page;
        }
        return $result;
    }

    private function getRestfulAnswerURIPart(): string
    {
        $result = "";
        $threadID = $this->thread->getThreadID();
        $result = "/".$this->thread->generateRestfulURIByID($threadID)."/answer";
        $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", -1);
        if ($page != -1) {
            $result = $result."/".$page;
        }

        $quote = $this->requestParametersService->fromGet()->getIntegerParameter("quote", -1);
        if ($quote != -1) {
            $result = $result."?quote=".$quote;
        }
        return $result;
    }

    private function getOldAnswerURIPart(): string
    {
        $result = "";
        $threadID = $this->thread->getThreadID();
        $result = "&thread=".$threadID."&page=".$this->thread->getThreadPage();

        $quote = $this->requestParametersService->fromGet()->getIntegerParameter("quote", -1);
        if ($quote != -1) {
            $result = $result."&quote=".$quote;
        }
        return $result;
    }

    private function getRestfulEditURIPart(): string
    {
        $result = "";
        $threadID = $this->thread->getThreadID();
        $result = "/".$this->thread->generateRestfulURIByID($threadID)."/edit";

        $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", -1);
        if ($page != -1) {
            $result = $result."/".$page;
        }

        $post = $this->requestParametersService->fromGet()->getIntegerParameter("post", -1);
        if ($post != -1) {
            $result = $result."/".$post;
        }
        return $result;
    }

    private function getOldEditURIPart(): string
    {
        $result = "";
        $threadID = $this->thread->getThreadID();
        $result = "&thread=".$threadID."&page=".$this->thread->getThreadPage()."&post=".$this->getPostID();
        return $result;
    }

    private function getRestfulPostsURIPart(): string
    {
        $result = "";
        $threadID = $this->thread->getThreadID();
        $result = "/".$this->thread->generateRestfulURIByID($threadID)."/posts";
        $page = $this->thread->getThreadPage();
        if ($page > 0) {
            $result = $result."/".$page;
        }
        return $result;
    }

    private function getOldPostsURIPart(): string
    {
        $result = "";
        $threadID = $this->thread->getThreadID();
        $result = "&thread=".$threadID;
        $page = $this->thread->getThreadPage();
        if ($page > 0) {
            $result = $result."&page=".$page;
        }
        return $result;
    }

    // Method used in PHP template file.
    // @phpstan-ignore method.unused
    private function getPageURIFormatted(int $page): string
    {
        return $this->boardBase->getPageURIFormatted($page);
    }

    private function getPostID(): int
    {
        $postID = $this->requestParametersService->fromGet()->getIntegerParameter("post", -1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($postID == -1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 4) {
                $postID = intval($explodedRequestURI[4]);
            }
        }
        return $postID;
    }

    public function getRestfulURIPartFromOldURL(string $action): string
    {
        $result = "";

        if ($action == "posts") {
            $result = $this->getRestfulPostsURIPart();
        } elseif ($action == "edit") {
            $result = $this->getRestfulEditURIPart();
        } elseif ($action == "answer") {
            $result = $this->getRestfulAnswerURIPart();
        }

        return $result;
    }

    public function getOldURIPartFromRestfulURL(string $action): string
    {
        $result = "";

        if ($action == "posts") {
            $result = $this->getOldPostsURIPart();
        } elseif ($action == "edit") {
            $result = $this->getOldEditURIPart();
        } elseif ($action == "answer") {
            $result = $this->getOldAnswerURIPart();
        }

        return $result;
    }
}
