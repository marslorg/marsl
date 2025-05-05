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
use marsl\user\Authentication;
use marsl\user\Role;
use marsl\user\User;

class Gallery implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    private int $PAGINATION_DISTANCE = 3;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Administrator interface for the gallery.
     */
    public function admin(): void
    {
        if ($this->user->isAdmin()) {
            $moduleAdmin = $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole());
            $moduleExtended = $this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole());
            if ($moduleAdmin) {
                require_once(dirname(__FILE__)."/../admin/template/gallery.navigation.tpl.php");
                $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
                if ($action != "") {
                    if ($action == "ftp") {
                        if ($moduleExtended) {
                            $this->ftp();
                        }
                    }
                    if ($action == "queue") {
                        if ($moduleExtended) {
                            $this->doThings();
                            $this->queue();
                        }
                    }
                    if ($action == "edit") {
                        if ($moduleExtended) {
                            $this->edit();
                        }
                    }
                    if ($action == "details") {
                        $this->details();
                    }

                    if ($action == "albums") {
                        if ($moduleExtended) {
                            $this->doThings();
                        }
                        $this->albums();
                    }

                    if ($action == "add") {
                        if ($moduleExtended) {
                            $this->addPhoto();
                        }
                    }

                } else {
                    $this->upload();
                }
            }
        }
    }

    /*
     * Shows the albums which are note deleted and released to the frontend.
     * Will be called from the admin interface.
     */
    private function albums(): void
    {
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->user->isAdmin()) {
            $moduleAdmin = $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole());
            $moduleExtended = $this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole());
            if ($moduleAdmin) {
                $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", 1);
                $result = $this->db->query("SELECT COUNT(`album`) AS rowcount FROM `album` WHERE `visible`='1' AND `deleted`='0'");
                $pages = $this->db->getRowCount($result) / 10;
                $start = $page * 10 - 10;
                $end = 10;
                $galleries = array();
                $result = $this->db->query("SELECT `album`, `author`, `photograph`, `author_ip`, `location`, `description`, `date`, `postdate` FROM `album` WHERE `visible`='1' AND `deleted`='0' ORDER BY `postdate` DESC LIMIT $start,$end");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['album'])
                        && is_string($row['author'])
                        && ($row['photograph'] == null || is_string($row['photograph']))
                        && ($row['author_ip'] == null || is_string($row['author_ip']))
                        && is_string($row['location'])
                        && ($row['description'] == null || is_string($row['description']))
                        && is_string($row['date'])
                        && is_string($row['postdate'])) {
                        $id = intval(strval($row['album']));
                        $author = intval(strval($row['author']));
                        $authorName = $this->basic->convertToHTMLEntities($this->user->getAcronymbyID($author, $this->authentication));
                        $photograph = $this->basic->convertToHTMLEntities(is_string($row['photograph']) ? $row['photograph'] : "");
                        $authorIP = $this->basic->convertToHTMLEntities(is_string($row['author_ip']) ? $row['author_ip'] : "");
                        $location = $this->basic->convertToHTMLEntities($this->navigation->getNamebyID(intval(strval($row['location']))));
                        $locationAdmin = $this->authentication->locationAdminAllowed(intval(strval($row['location'])), $this->role->getRole());
                        $editLink = ($moduleExtended && $locationAdmin);
                        $description = is_string($row['description']) ? $row['description'] : "";
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $dateTime->setTimestamp(intval(strval($row['postdate'])));
                        $postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
                        array_push($galleries, array('photograph' => $photograph, 'album' => $id, 'authorIP' => $authorIP, 'author' => $authorName, 'location' => $location, 'description' => $description, 'date' => $date, 'postdate' => $postdate, 'editLink' => $editLink));
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/gallery.tpl.php");
            }
        }
    }

    /*
     * Allows and administrator to have a look at the album details and change them.
     * E.g. deleting uploaded pictures.
     */
    private function details(): void
    {
        $album = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        $moduleAdmin = $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole());
        $moduleExtended = $this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole());
        $locationRead = false;
        $locationAdmin = false;
        $result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$album'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['location'])) {
                $location = intval(strval($row['location']));
                $locationRead = $this->authentication->locationReadAllowed($location, $this->role->getRole());
                $locationAdmin = $this->authentication->locationAdminAllowed($location, $this->role->getRole());
            }
        }

        if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "send"
            && $this->authentication->checkToken(
                $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                $this->requestParametersService->fromPost()->getStringParameter("authToken")
            )) {
            if ($moduleExtended && $moduleAdmin && $locationAdmin) {
                $result = $this->db->query("SELECT `picture` FROM `picture` WHERE `album`='$album' AND `deleted`='0'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['picture'])) {
                        $picture = intval(strval($row['picture']));
                        if ($this->requestParametersService->fromPost()->getStringParameter($picture."_delete", "") != "") {
                            $this->db->query("UPDATE `picture` SET `deleted`='1' WHERE `picture`='$picture'");
                        }
                        if ($this->requestParametersService->fromPost()->getStringParameter($picture."_submit", "") != "") {
                            $this->db->query("UPDATE `picture` SET `visible`='1' WHERE `picture`='$picture'");
                        }
                    }
                }
            }
        }

        if ($locationRead) {
            $pictures = array();
            $result = $this->db->query("SELECT `picture`.`visible` AS `visibility`, `folder`, `picture`, `subtitle`, `filename` FROM `picture` JOIN `album` USING(`album`) WHERE `album`='$album' AND `picture`.`deleted`='0' ORDER BY `filename`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['picture'])
                    && ($row['subtitle'] == null || is_string($row['subtitle']))
                    && is_string($row['filename'])
                    && is_string($row['visibility'])
                    && is_string($row['folder'])) {
                    $picture = intval(strval($row['picture']));
                    $subtitle = $this->basic->convertToHTMLEntities(is_string($row['subtitle']) ? $row['subtitle'] : "");
                    $filename = $this->basic->convertToHTMLEntities($row['filename']);
                    $visible = intval(strval($row['visibility']));
                    $administrator = ($moduleExtended && $moduleAdmin && $locationAdmin);
                    $folder = $this->basic->convertToHTMLEntities($row['folder']);

                    $thumbPath = "../albums/".$folder."thumb_".$filename;
                    $picPath = "../albums/".$folder.$filename;

                    array_push($pictures, array('picture' => $picture, 'subtitle' => $subtitle, 'visible' => $visible, 'thumbPath' => $thumbPath, 'picPath' => $picPath, 'administrator' => $administrator));
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once(dirname(__FILE__)."/../admin/template/gallery.thumbs.tpl.php");
        }
    }

    /*
     * Shows the add photo dialog.
     */
    private function addPhoto(): void
    {
        $album = $this->requestParametersService->fromGet()->getIntegerParameter("album", -1);
        if ($this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole())
            && $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole())) {
            $result = $this->db->query("SELECT `location`, `album` FROM `album` WHERE `album`='$album' AND `deleted`='0'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                    && is_string($row['album'])) {
                    $location = intval(strval($row['location']));
                    if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                        $album = intval(strval($row['album']));
                        require_once(dirname(__FILE__)."/../admin/template/gallery.addphoto.tpl.php");
                    }
                }
            }
        }
    }

    /*
     * Changes the meta-information of an album.
     */
    private function edit(): void
    {
        $album = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole())) {
            if ($this->requestParametersService->fromPost()->getStringParameter("action", "") == "send") {
                if ($this->authentication->checkToken(
                    $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                    $this->requestParametersService->fromPost()->getStringParameter("authToken")
                )) {
                    $result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$album'");
                    while ($row = $this->db->fetchArray($result)) {
                        if (is_string($row['location'])) {
                            $location = intval(strval($row['location']));
                            $category = $this->requestParametersService->fromPost()->getIntegerParameter("category", -1);
                            if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())
                            && $this->authentication->locationAdminAllowed($category, $this->role->getRole())) {
                                $photograph = $this->requestParametersService->fromPost()->getStringParameter("photograph", "");
                                $year = $this->requestParametersService->fromPost()->getIntegerParameter("year", 0);
                                $month = $this->requestParametersService->fromPost()->getIntegerParameter("month", 0);
                                $day = $this->requestParametersService->fromPost()->getIntegerParameter("day", 0);
                                if (checkdate($month, $day, $year)) {
                                    $date = mktime(0, 0, 0, $month, $day, $year);
                                } else {
                                    $date = time();
                                }
                                $description = $this->db->escapeString(
                                    $this->basic->cleanHTML(
                                        $this->requestParametersService->fromPost()->getStringParameter("description", "")
                                    )
                                );
                                $this->db->query("UPDATE `album` SET `photograph`='$photograph', `location`='$category', `date`='$date', `description`='$description' WHERE `album`='$album'");
                            }
                        }
                    }

                }
            }
            $locations = array();
            $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='gallery' AND `type` IN ('1', '2') ORDER BY `pos`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                    && is_string($row['name'])) {
                    $id = intval(strval($row['id']));
                    if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())) {
                        array_push($locations, array('location' => $id,'name' => $this->basic->convertToHTMLEntities($row['name'])));
                    }
                }
            }
            $result = $this->db->query("SELECT `location`, `photograph`, `date`, `description` FROM `album` WHERE `album`='$album'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                    && ($row['photograph'] == null || is_string($row['photograph']))
                    && is_string($row['date'])
                    && ($row['description'] == null || is_string($row['description']))) {
                    $category = intval(strval($row['location']));
                    if ($this->authentication->locationAdminAllowed($category, $this->role->getRole())) {
                        $photograph = $this->basic->convertToHTMLEntities(is_string($row['photograph']) ? $row['photograph'] : "");
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $day = $dateTime->format("d");
                        $month = $dateTime->format("m");
                        $year = $dateTime->format("Y");
                        $description = $row['description'];
                        $album = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                        $authTime = time();
                        $authToken = $this->authentication->getToken($authTime);
                        require_once(dirname(__FILE__)."/../admin/template/gallery.edit.tpl.php");
                    }
                }
            }
        }

    }

    /*
     * Some smaller functions which can be applied on an album. E.g. deleting an album or releasing it.
     */
    private function doThings(): void
    {
        $do = $this->requestParametersService->fromGet()->getStringParameter("do", "");
        if ($do != "") {
            if ($this->user->isAdmin()) {
                $moduleAdmin = $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole());
                $moduleExtended = $this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole());
                if ($do == "submit") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                        $this->requestParametersService->fromGet()->getStringParameter("token")
                    )) {
                        if ($moduleExtended && $moduleAdmin) {
                            $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                            $result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$id'");
                            while ($row = $this->db->fetchArray($result)) {
                                if (is_string($row['location'])) {
                                    if ($this->authentication->locationAdminAllowed(
                                        intval(strval($row['location'])),
                                        $this->role->getRole()
                                    )) {
                                        $admin = $this->user->getID();
                                        $adminIP = $this->db->escapeString($this->requestParametersService->fromServer()->getStringParameter("REMOTE_ADDR"));
                                        $this->db->query("UPDATE `album` SET `visible`='1', `admin`='$admin', `admin_ip`='$adminIP' WHERE `album`='$id'");
                                        $this->db->query("UPDATE `picture` SET `visible`='1' WHERE `album`='$id'");
                                    }
                                }
                            }
                        }
                    }
                }
                if ($do == "del") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromGet()->getIntegerParameter("time"),
                        $this->requestParametersService->fromGet()->getStringParameter("token")
                    )) {
                        if ($moduleExtended && $moduleAdmin) {
                            $id = $this->requestParametersService->fromGet()->getIntegerParameter("id", -1);
                            $result = $this->db->query("SELECT `location` FROM `album` WHERE `album`='$id'");
                            while ($row = $this->db->fetchArray($result)) {
                                if (is_string($row['location'])) {
                                    if ($this->authentication->locationAdminAllowed(
                                        intval(strval($row['location'])),
                                        $this->role->getRole()
                                    )) {
                                        $this->db->query("UPDATE `album` SET `deleted`='1' WHERE `album`='$id'");
                                        $this->db->query("UPDATE `picture` SET `deleted`='1' WHERE `album`='$id'");
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /*
     * Shows all unreleased albums.
     */
    private function queue(): void
    {
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->user->isAdmin()) {
            $moduleAdmin = $this->authentication->moduleAdminAllowed("gallery", $this->role->getRole());
            $moduleExtended = $this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole());
            if ($moduleAdmin && $moduleExtended) {
                $galleries = array();
                $result = $this->db->query("SELECT `location`, `album`, `author`, `author_ip`, `photograph`, `description`, `date`, `postdate` FROM `album` WHERE `visible`='0' AND `deleted`='0'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['location'])
                        && is_string($row['album'])
                        && is_string($row['author'])
                        && ($row['author_ip'] == null || is_string($row['author_ip']))
                        && ($row['photograph'] == null || is_string($row['photograph']))
                        && ($row['description'] == null || is_string($row['description']))
                        && is_string($row['date'])
                        && is_string($row['postdate'])) {
                        $location = intval(strval($row['location']));
                        if ($this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                            $id = intval(strval($row['album']));
                            $author = intval(strval($row['author']));
                            $authorName = $this->basic->convertToHTMLEntities($this->user->getAcronymbyID($author, $this->authentication));
                            $authorIP = $this->basic->convertToHTMLEntities(is_string($row['author_ip']) ? $row['author_ip'] : "");
                            $photograph = $this->basic->convertToHTMLEntities(is_string($row['photograph']) ? $row['photograph'] : "");
                            $location = $this->basic->convertToHTMLEntities($this->navigation->getNamebyID($location));
                            $description = $row['description'];
                            $dateTime->setTimestamp(intval(strval($row['date'])));
                            $date = $dateTime->format("d\.m\.Y");
                            $dateTime->setTimestamp(intval(strval($row['postdate'])));
                            $postdate = $dateTime->format("d\. M Y \u\m H\:i\:s");
                            array_push($galleries, array('photograph' => $photograph, 'album' => $id, 'authorIP' => $authorIP, 'author' => $authorName, 'location' => $location, 'description' => $description, 'date' => $date, 'postdate' => $postdate));
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/gallery.queue.tpl.php");
            }
        }
    }

    /*
     * Shows the upload dialog.
     */
    private function upload(): void
    {
        $success = $this->requestParametersService->fromGet()->getBoolParameter("success", false);
        if ($this->authentication->moduleAdminAllowed("gallery", $this->role->getRole())) {
            $locations = array();
            $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='gallery' AND `type` IN ('1', '2') ORDER BY `pos`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                    && is_string($row['name'])) {
                    $id = intval(strval($row['id']));
                    if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())
                        || $this->authentication->locationExtendedAllowed($id, $this->role->getRole())) {
                        array_push($locations, array('location' => $id, 'name' => $this->basic->convertToHTMLEntities($row['name'])));
                    }
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            $step = $this->requestParametersService->fromGet()->getIntegerParameter("step", -1);
            if ($step != -1) {
                if ($step == 2) {
                    $tmpDir = $this->requestParametersService->fromGet()->getStringParameter("dir");
                    require_once(dirname(__FILE__)."/../admin/template/gallery.upload.step2.tpl.php");
                } else {
                    $tmpDir = $this->user->getID().$this->basic->randomHash();
                    require_once(dirname(__FILE__)."/../admin/template/gallery.upload.tpl.php");
                }
            } else {
                $tmpDir = $this->user->getID().$this->basic->randomHash();
                require_once(dirname(__FILE__)."/../admin/template/gallery.upload.tpl.php");
            }
        }
    }

    /*
     * Shows the FTP dialog.
     */
    private function ftp(): void
    {
        if ($this->authentication->moduleExtendedAllowed("gallery", $this->role->getRole())) {
            $locations = array();
            $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='gallery' AND `type` IN ('1', '2') ORDER BY `pos`");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['id'])
                    && is_string($row['name'])) {
                    $id = intval(strval($row['id']));
                    if ($this->authentication->locationAdminAllowed($id, $this->role->getRole())) {
                        array_push($locations, array('location' => $id,'name' => $this->basic->convertToHTMLEntities($row['name'])));
                    }
                }
            }
            $authTime = time();
            $authToken = $this->authentication->getToken($authTime);
            require_once(dirname(__FILE__)."/../admin/template/gallery.ftp.tpl.php");
        }
    }

    /*
     * Shows the frontend of the gallery.
     */
    public function display(): void
    {
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->authentication->moduleReadAllowed("gallery", $this->role->getRole())) {
            $pageID = $this->navigation->getPageID();
            if ($this->getAction() != "thumb") {
                $location = -1;
                if ($pageID > -1) {
                    $location = $pageID;
                } else {
                    $location = $this->basic->getHomeLocation();
                }
                $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['maps_to'])) {
                        $location = intval(strval($row['maps_to']));
                    }
                }
                $uri = $this->navigation->getRelativeURI($location, null, false);
                list($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage) = $this->getPagination($location);
                $galleries = array();
                $result = $this->db->query("SELECT `album`, `folder`, `photograph`, `date`, `description`, (SELECT `filename` FROM `picture` AS p WHERE `a`.`album` = `p`.`album` AND `deleted` = '0' AND `visible` = '1' ORDER BY RAND() LIMIT 1) AS `filename` FROM `album` AS a WHERE `visible`='1' AND `deleted`='0' AND `location`='$location' ORDER BY `postdate` DESC LIMIT $start,$end");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['album'])
                        && is_string($row['folder'])
                        && ($row['photograph'] == null || is_string($row['photograph']))
                        && is_string($row['date'])
                        && ($row['description'] == null || is_string($row['description']))
                        && is_string($row['filename'])) {
                        $album = intval(strval($row['album']));
                        $folder = $this->basic->convertToHTMLEntities($row['folder']);
                        $photograph = $row['photograph'];
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $description = $row['description'];
                        $file = htmlspecialchars($row['filename'], 0, "UTF-8");
                        $picture = "albums/".$folder."thumb_".$file;
                        $picSize = "";
                        if (file_exists($picture)) {
                            $picSize = getimagesize($picture);
                        } else {
                            $picture = "";
                        }

                        $galleryURI = $uri.$this->generateGalleryURI($album, $dateTime);

                        array_push($galleries, array('album' => $album,'photograph' => $photograph,'date' => $date,'description' => $description,'picture' => $picture,'picSize' => $picSize, 'galleryURI' => $galleryURI));
                    }
                }
                require_once(dirname(__FILE__)."/../template/gallery.main.tpl.php");
            } else {
                $location = $pageID;
                $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$location' AND `type`='4'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['maps_to'])) {
                        $location = intval(strval($row['maps_to']));
                    }
                }
                $album = $this->getGalleryID();
                $pictures = array();
                $result = $this->db->query("SELECT `folder`, `photograph` FROM `album` WHERE `album`='$album' AND `location`='$location' AND `visible`='1' AND `deleted`='0'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['folder'])
                        && ($row['photograph'] == null || is_string($row['photograph']))) {
                        $folder = $this->basic->convertToHTMLEntities($row['folder']);
                        $photograph = $this->basic->convertToHTMLEntities(is_string($row['photograph']) ? $row['photograph'] : "");
                        $result2 = $this->db->query("SELECT `filename`, `picture`, `subtitle` FROM `picture` WHERE `album`='$album' AND `deleted`='0' AND `visible`='1' ORDER BY `filename`");
                        while ($row2 = $this->db->fetchArray($result2)) {
                            if (is_string($row2['filename'])
                                && is_string($row2['picture'])
                                && ($row2['subtitle'] == null || is_string($row2['subtitle']))) {
                                $file = htmlspecialchars($row2['filename'], 0, "UTF-8");
                                $id = intval(strval($row2['picture']));
                                $thumb = "albums/".$folder."thumb_".$file;
                                $picture = "albums/".$folder.$file;
                                if (file_exists($picture)) {
                                    $picSize = getimagesize($picture);
                                    $subtitle = $this->basic->convertToHTMLEntities(is_string($row2['subtitle']) ? $row2['subtitle'] : "");
                                    if (is_array($picSize)) {
                                        array_push($pictures, array('subtitle' => $subtitle, 'id' => $id,'thumb' => $thumb,'picture' => $picture,'picSize' => $picSize, 'width' => $picSize[0], 'height' => $picSize[1]));
                                    }
                                }
                            }
                        }
                        require_once(dirname(__FILE__)."/../template/gallery.thumbs.tpl.php");
                    }
                }
            }
        }
    }

    /**
     * @return array<int, bool|float|int|string>
     */
    private function getPagination(int $location): array
    {
        $result = $this->db->query("SELECT COUNT(`album`) AS rowcount FROM `album` WHERE `visible`='1' AND `deleted`='0' AND `location`='$location'");
        $pages = $this->db->getRowCount($result) / 10;
        $page = $this->getPage();
        $startPage = 1;
        if ($page - $this->PAGINATION_DISTANCE > 1) {
            $startPage = $page - $this->PAGINATION_DISTANCE;
        }
        $endPage = $pages;
        if ($page + $this->PAGINATION_DISTANCE <= $endPage) {
            $endPage = $page + $this->PAGINATION_DISTANCE;
        }
        $showFirstPage = $page > 1;
        $showPreviousPage = $page > 2;
        $showNextPage = $page < $pages - 1;
        $showLastPage = $page < $pages;
        $start = $page * 10 - 10;
        $end = 10;

        return array($start, $end, $page, $pages, $startPage, $endPage, $showFirstPage, $showPreviousPage, $showNextPage, $showLastPage);
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

    private function generateGalleryURI(int $gallery, DateTime $dateTime): string
    {
        $uri = "";
        if ($this->configuration->getEnableOldURIs()) {
            $uri = "&show=".$gallery."&action=thumb";
        } else {
            $date = $dateTime->format("Y\-m\-d");

            $uri = "/".$date."-".$gallery;
        }
        return $uri;
    }

    public function getRestfulURIPartFromOldURL(): string
    {
        $uri = "";
        $action = $this->getAction();
        if ($action == "thumb") {
            $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
            $gallery = $this->getGalleryID();
            $result = $this->db->query("SELECT `date` FROM `album` WHERE `album` = $gallery");
            $date = "";
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['date'])) {
                    $dateTime->setTimestamp(intval(strval($row['date'])));
                    $date = $dateTime->format("Y\-m\-d");
                }
            }
            $uri = "/".$date."-".$gallery;
        }

        $page = $this->getPage();
        if ($page > 1) {
            $uri = "/".$page;
        }
        return $uri;
    }

    public function getOldURIPartFromRestfulURL(): string
    {
        $uri = "";
        $action = $this->getAction();
        if ($action == "thumb") {
            $uri = "&show=".$this->getGalleryID()."&action=".$action;
        }

        $page = $this->getPage();
        if ($page > 1) {
            $uri = "&page=".$page;
        }
        return $uri;
    }

    private function getGalleryID(): int
    {
        $gallery = $this->requestParametersService->fromGet()->getIntegerParameter("show", -1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($gallery == -1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $galleryPart = $explodedRequestURI[1];
                $explodedGalleryPart = explode('-', $galleryPart);
                $explodedGalleryPartSlugSize = sizeof($explodedGalleryPart);
                if ($explodedGalleryPartSlugSize > 1) {
                    $gallery = intval($explodedGalleryPart[$explodedGalleryPartSlugSize - 1]);
                }
            }
        }

        return $gallery;
    }

    public function getPage(): int
    {
        $page = $this->requestParametersService->fromGet()->getIntegerParameter("page", 1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($page == 1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $pageSlug = $explodedRequestURI[1];
                $explodedPageSlug = explode('-', $pageSlug);
                $explodedPageSlugSize = sizeof($explodedPageSlug);
                if ($explodedPageSlugSize == 1) {
                    $page = intval($pageSlug);
                }
            }
        }
        return $page;
    }

    private function getAction(): string
    {
        $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($action == "" && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                if ($this->getGalleryID() > -1) {
                    $action = "thumb";
                }
            }
        }

        return $action;
    }

    public function getPageURIFormatted(int $page): string
    {
        $result = "";
        if ($page > 1) {
            if ($this->configuration->getEnableOldURIs()) {
                $result = "&page=".$page;
            } else {
                $result = "/".$page;
            }
        }
        return $result;
    }
}
