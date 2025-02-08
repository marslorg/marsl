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

class Portal implements Module
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private Navigation $navigation;
    private News $news;
    private IRequestParametersService $requestParametersService;
    private Role $role;
    private User $user;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        Navigation $navigation,
        News $news,
        IRequestParametersService $requestParametersService,
        Role $role,
        User $user
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->navigation = $navigation;
        $this->news = $news;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
        $this->user = $user;
    }

    /*
     * Displays the frontend portal with the featured content slider and the category boxes.
     */
    public function display(): void
    {
        $id = -1;
        $pageID = $this->navigation->getPageID();
        if ($pageID > -1) {
            $id = $pageID;
        } else {
            $id = $this->basic->getHomeLocation();
        }
        $result = $this->db->query("SELECT `maps_to` FROM `navigation` WHERE `id` = '$id' AND `type`='4'");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['maps_to'])) {
                $id = intval(strval($row['maps_to']));
            }
        }
        if ($this->authentication->moduleReadAllowed("portal", $this->role->getRole()) && $this->authentication->moduleReadAllowed("news", $this->role->getRole())) {
            if ($this->authentication->locationReadAllowed($id, $this->role->getRole())) {
                $this->constructFeaturedContent();
                $this->constructPortal();
            }
        }
    }

    /*
     * Displays the featured content slider.
     */
    private function constructFeaturedContent(): void
    {
        $news = array();
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        $result = $this->db->query("SELECT `location`, `date`, `photograph`, `url`, `news`, `headline`, `teaser`, `title`  FROM `news` JOIN `news_picture` ON `picture2`=`picture` WHERE `deleted`='0' AND `visible`='1' AND `featured`='1' ORDER BY `postdate` DESC LIMIT 4");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['location'])
                && is_string($row['date'])
                && ($row['photograph'] == null || is_string($row['photograph']))
                && ($row['url'] == null || is_string($row['url']))
                && is_string($row['news'])
                && ($row['headline'] == null || is_string($row['headline']))
                && ($row['teaser'] == null || is_string($row['teaser']))
                && ($row['title'] == null || is_string($row['title']))) {
                $location = intval(strval($row['location']));
                $photograph = "";
                if ($this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                    $dateTime->setTimestamp(intval(strval($row['date'])));
                    $date = $dateTime->format("d\.m\.Y");
                    if (is_string($row['photograph']) && $row['photograph'] != "") {
                        $photograph = " Foto: ".$this->basic->convertToHTMLEntities($row['photograph']);
                    }
                    $newsURI = $this->news->generateLink(
                        $location,
                        null,
                        "read",
                        is_string($row['headline']) ? $row['headline'] : "",
                        is_string($row['title']) ? $row['title'] : "",
                        intval(strval($row['news']))
                    );
                    array_push(
                        $news,
                        array(
                            'location' => $location,
                            'picture' => $this->basic->convertToHTMLEntities(is_string($row['url']) ? $row['url'] : ""),
                            'photograph' => $photograph,
                            'date' => $date,
                            'news' => intval(strval($row['news'])),
                            'newsURI' => $newsURI,
                            'headline' => $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : ""),
                            'title' => $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : ""),
                            'teaser' => is_string($row['teaser']) ? $row['teaser'] : ""
                        )
                    );
                }
            }
        }
        require_once(dirname(__FILE__)."/../template/portal.featured.tpl.php");
    }

    /*
     * Displays the content boxes.
     */
    private function constructPortal(): void
    {
        $pages = array();
        $result = $this->db->query("SELECT `id`, `name` FROM `navigation` WHERE `module`='news' AND `type` IN ('1', '2') ORDER BY `pos`");
        while ($row = $this->db->fetchArray($result)) {
            if (is_string($row['id'])
                && is_string($row['name'])) {
                $id = intval(strval($row['id']));
                if ($this->authentication->locationReadAllowed($id, $this->role->getRole())) {
                    array_push($pages, array('location' => $id, 'name' => $this->basic->convertToHTMLEntities($row['name'])));
                }
            }
        }
        require_once(dirname(__FILE__)."/../template/portal.head.tpl.php");
        $nb_id = 0;
        foreach ($pages as $page) {
            $location = $page['location'];
            $news = array();
            $result = $this->db->query("SELECT `picture1`, `url`, `photograph`, `teaser`, `news`, `headline`, `title` FROM `news` LEFT JOIN `news_picture` ON `picture` = `picture1` WHERE `location`='$location' AND `visible`='1' AND `deleted`='0' AND `featured`='0' ORDER BY `postdate` DESC LIMIT 3");
            while ($row = $this->db->fetchArray($result)) {
                if (($row['picture1'] == null || is_string($row['picture1']))
                    && ($row['url'] == null || is_string($row['url']))
                    && ($row['teaser'] == null || is_string($row['teaser']))
                    && is_string($row['news'])
                    && ($row['headline'] == null || is_string($row['headline']))
                    && ($row['title'] == null || is_string($row['title']))) {
                    $picID = is_string($row['picture1']) ? intval(strval($row['picture1'])) : 0;
                    $picture = $this->basic->convertToHTMLEntities(is_string($row['url']) ? $row['url'] : "");
                    $photograph = "<br /><b>Foto: ".$this->basic->convertToHTMLEntities(is_string($row['photograph']) ? $row['photograph'] : "")."</b><br />";
                    $width = 0;
                    $height = 0;
                    if (!empty($picture) && file_exists("news/".$picture)) {
                        $picinfo = @getimagesize("news/".$picture);
                        if (is_array($picinfo)) {
                            $width = $picinfo[0] / 1.5;
                            $height = $picinfo[1] / 1.5;
                        }
                    }
                    $newsURI = $this->news->generateLink($location, $page['name'], "read", is_string($row['headline']) ? $row['headline'] : "", is_string($row['title']) ? $row['title'] : "", intval(strval($row['news'])));
                    array_push($news, array('width' => $width,'height' => $height,'picture' => $picture, 'photograph' => $photograph, 'teaser' => is_string($row['teaser']) ? $row['teaser'] : "", 'location' => $location, 'news' => intval(strval($row['news'])), 'newsURI' => $newsURI, 'headline' => $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : ""), 'title' => $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : "")));
                }
            }
            require(dirname(__FILE__)."/../template/portal.main.tpl.php");
            $nb_id++;
        }
        require_once(dirname(__FILE__)."/../template/portal.foot.tpl.php");
    }

    /*
     * Admin interface for the portal to choose the articles which should be shown in the featured content slider.
     */
    public function admin(): void
    {
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
        if ($this->user->isAdmin()) {
            if ($this->authentication->moduleAdminAllowed("portal", $this->role->getRole())) {
                if ($this->requestParametersService->fromPost()->getStringParameter("action", "") != "") {
                    if ($this->authentication->checkToken(
                        $this->requestParametersService->fromPost()->getIntegerParameter("authTime"),
                        $this->requestParametersService->fromPost()->getStringParameter("authToken")
                    )) {
                        $result = $this->db->query("SELECT `location`, `picture2`, `news` FROM `news` WHERE `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC LIMIT 30");
                        while ($row = $this->db->fetchArray($result)) {
                            if (is_string($row['location'])
                                && ($row['picture2'] == null || is_string($row['picture2']))
                                && is_string($row['news'])) {
                                $location = intval(strval($row['location']));
                                $picture = "empty";
                                $picID = is_string($row['picture2']) ? intval(strval($row['picture2'])) : 0;
                                $result2 = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picID'");
                                while ($row2 = $this->db->fetchArray($result2)) {
                                    if (is_string($row2['url'])) {
                                        $picture = $this->basic->convertToHTMLEntities($row2['url']);
                                    }
                                }
                                if ($this->authentication->moduleReadAllowed("news", $this->role->getGuestRole())
                                    && ($picture != "empty") && $this->authentication->locationReadAllowed($location, $this->role->getGuestRole())
                                    && $this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                                    $article = intval(strval($row['news']));
                                    $featured = $this->requestParametersService->fromPost()->getIntegerParameter(strval($article), 0);
                                    $this->db->query("UPDATE `news` SET `featured`='$featured' WHERE `news`='$article'");
                                }
                            }
                        }
                    }
                }
                $result = $this->db->query("SELECT `location`, `postdate`, `picture2`, `headline`, `news`, `title`, `featured` FROM `news` WHERE `deleted`='0' AND `visible`='1' ORDER BY `postdate` DESC LIMIT 30");
                $news = array();
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['location'])
                        && is_string($row['postdate'])
                        && ($row['picture2'] == null || is_string($row['picture2']))
                        && ($row['headline'] == null || is_string($row['headline']))
                        && is_string($row['news'])
                        && ($row['title'] == null || is_string($row['title']))
                        && ($row['featured'] == null || is_string($row['featured']))) {
                        $location = intval(strval($row['location']));
                        $dateTime->setTimestamp(intval(strval($row['postdate'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $picture = "empty";
                        $picID = is_string($row['picture2']) ? intval(strval($row['picture2'])) : 0;
                        $result2 = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picID'");
                        while ($row2 = $this->db->fetchArray($result2)) {
                            if (is_string($row2['url'])) {
                                $picture = $row2['url'];
                            }
                        }
                        if ($this->authentication->moduleReadAllowed("news", $this->role->getGuestRole())
                            && ($picture != "empty")
                            && $this->authentication->locationReadAllowed($location, $this->role->getGuestRole())
                            && $this->authentication->locationAdminAllowed($location, $this->role->getRole())) {
                            array_push(
                                $news,
                                array(
                                    'headline' => $this->basic->convertToHTMLEntities(is_string($row['headline']) ? $row['headline'] : ""),
                                    'id' => intval(strval($row['news'])),
                                    'title' => $this->basic->convertToHTMLEntities(is_string($row['title']) ? $row['title'] : ""),
                                    'date' => $date,
                                    'featured' => is_string($row['featured']) ? boolval(strval($row['featured'])) : false
                                )
                            );
                        }
                    }
                }
                $authTime = time();
                $authToken = $this->authentication->getToken($authTime);
                require_once(dirname(__FILE__)."/../admin/template/portal.tpl.php");
            }
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
