<?php

namespace marsl\modules;

include_once(dirname(__FILE__)."/../includes/errorHandler.php");
include_once(dirname(__FILE__)."/../includes/slugify/vendor/autoload.php");
include_once(dirname(__FILE__)."/../autoload.php");

use DateTime;
use DateTimeZone;
use Cocur\Slugify\Slugify;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\Infrastructure\RequestParameters\Adapters\Drivers\Service\IRequestParametersService;
use marsl\modules\cbe\Band;
use marsl\modules\cbe\Location;
use marsl\user\Authentication;
use marsl\user\Role;

class CBE implements Module
{
    private Authentication $authentication;
    private Band $band;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private Location $location;
    private Navigation $navigation;
    private News $news;
    private IRequestParametersService $requestParametersService;
    private Role $role;

    /**
     * @var array<string>
     */
    private array $baseLinks = array();

    public function __construct(
        Authentication $authentication,
        Band $band,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        Location $location,
        Navigation $navigation,
        News $news,
        IRequestParametersService $requestParametersService,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->band = $band;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->location = $location;
        $this->navigation = $navigation;
        $this->news = $news;
        $this->requestParametersService = $requestParametersService;
        $this->role = $role;
    }

    public function display(): void
    {

    }

    public function admin(): void
    {
        if ($this->authentication->moduleAdminAllowed("cbe", $this->role->getRole())) {
            require_once(dirname(__FILE__)."/../admin/template/cbe.main.tpl.php");
            $action = $this->requestParametersService->fromGet()->getStringParameter("action", "");
            if ($action == "bands") {
                $this->band->admin();
            }
            if ($action == "clubs") {
                $this->location->admin();
            }
            if ($action == "editband") {
                $id = $this->requestParametersService->fromGet()->getIntegerParameter("band", -1);
                $this->band->edit($id);
            }
            if ($action == "editclub") {
                $id = $this->requestParametersService->fromGet()->getIntegerParameter("club", -1);
                $this->location->edit($id);
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

    public function isTaggable(): bool
    {
        return true;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getTagList(): array
    {
        $types = array();
        array_push($types, array('type' => "band", 'text' => "Bands"));
        array_push($types, array('type' => "location", 'text' => "Locations"));
        return $types;
    }

    public function addTags(string $tagString, string $type, int $news): void
    {
        $tags = array_filter(explode(";", $tagString));
        if ($type == "band") {
            $this->db->query("DELETE FROM `news_tag` WHERE `type`='cbe_band' AND `news`='$news'");
        }
        if ($type == "location") {
            $this->db->query("DELETE FROM `news_tag` WHERE `type`='cbe_location' AND `news`='$news'");
        }
        foreach ($tags as $tag) {
            $tag = $this->db->escapeString($tag);
            $tag = trim($tag);
            if ($type == "band") {
                $bandID = "";

                if ((strlen($tag) > 0) && (!$this->db->isExisting("SELECT `tag` FROM `band` WHERE `tag`='$tag' LIMIT 1"))) {
                    $this->db->query("INSERT INTO `band`(`tag`) VALUES('$tag')");
                }

                $result = $this->db->query("SELECT `id` FROM `band` WHERE `tag`='$tag'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['id'])) {
                        $bandID = intval(strval($row['id']));
                    }
                }
                $this->db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$bandID','$news','cbe_band')");
            }
            if ($type == "location") {
                $locationID = "";

                if ((strlen($tag) > 0) && (!$this->db->isExisting("SELECT `tag` FROM `location` WHERE `tag`='$tag' LIMIT 1"))) {
                    $this->db->query("INSERT INTO `location`(`tag`) VALUES('$tag')");
                }

                $result = $this->db->query("SELECT `id` FROM `location` WHERE `tag`='$tag'");
                while ($row = $this->db->fetchArray($result)) {
                    if (is_string($row['id'])) {
                        $locationID = intval(strval($row['id']));
                    }
                }

                $this->db->query("INSERT INTO `news_tag`(`tag`,`news`,`type`) VALUES('$locationID','$news','cbe_location')");
            }
        }
    }

    public function getTagString(string $type, int $news): string
    {
        $retString = array();

        if ($type == "band") {
            $result = $this->db->query("SELECT `band`.`tag` AS tagname FROM `band` JOIN `news_tag` ON(`band`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_band' AND `news`='$news' ORDER BY `band`.`tag`");
            while ($row = $this->db->fetchArray($result)) {
                array_push($retString, $row['tagname']);
            }
        }

        if ($type == "location") {
            $result = $this->db->query("SELECT `location`.`tag` AS tagname FROM `location` JOIN `news_tag` ON(`location`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_location' AND `news`='$news' ORDER BY `location`.`tag`");
            while ($row = $this->db->fetchArray($result)) {
                array_push($retString, $row['tagname']);
            }
        }

        return implode(";", $retString);

    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getTags(string $type, int $news): array
    {
        $ret = array();

        if ($type == "band") {
            $result = $this->db->query("SELECT `id`, `band`.`tag` AS tagname FROM `band` JOIN `news_tag` ON(`band`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_band' AND `news`='$news' ORDER BY `band`.`tag`");
            while ($row = $this->db->fetchArray($result)) {
                // @phpstan-ignore argument.type
                $ret = $this->buildTagArray($row, $type, $ret);
            }
        }

        if ($type == "location") {
            $result = $this->db->query("SELECT `id`, `location`.`tag` AS tagname FROM `location` JOIN `news_tag` ON(`location`.`id`=`news_tag`.`tag`) WHERE `type`='cbe_location' AND `news`='$news' ORDER BY `location`.`tag`");
            while ($row = $this->db->fetchArray($result)) {
                // @phpstan-ignore argument.type
                $ret = $this->buildTagArray($row, $type, $ret);
            }
        }

        return $ret;
    }

    /**
     * @param array<string, string> $row
     * @param string $type
     * @param array<int, array<string, string>> $ret
     *
     * @return array<int, array<string, string>>
     */
    private function buildTagArray(array $row, string $type, array $ret): array
    {
        $uri = "";
        if ($this->configuration->getEnableOldURIs()) {
            $uri = "index.php?tag=".$row['id']."&scope=cbe_".$type;
        } else {
            $slugify = new Slugify();
            $tagPart = $slugify->slugify($row['tagname'])."-".$row['id'];
            $uri = "tag/cbe_".$type."/".$tagPart;
        }
        array_push($ret, array('id' => $row['id'], 'tag' => $row['tagname'], 'uri' => $uri));
        return $ret;
    }

    public function displayTag(): void
    {
        $tagID = $this->getTagID();
        $type = $this->getScope();
        $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));

        if ($type == "cbe_location") {
            $articles = array();
            $tagName = "";
            $result = $this->db->query("SELECT `tag` FROM `location` WHERE `id`='$tagID'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['tag'])) {
                    $tagName = $this->basic->convertToHTMLEntities($row['tag']);
                }
            }
            $result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_location' ORDER BY `date` DESC");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                && is_string($row['news'])
                && is_string($row['headline'])
                && is_string($row['title'])
                && is_string($row['date'])
                && is_string($row['name'])) {
                    $location = intval(strval($row['location']));
                    if ($this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                        $news = intval(strval($row['news']));
                        $headline = $this->basic->convertToHTMLEntities($row['headline']);
                        $title = $this->basic->convertToHTMLEntities($row['title']);
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $locationName = $this->basic->convertToHTMLEntities($row['name']);
                        $link = $this->news->generateLink($location, $locationName, "read", $headline, $title, $news);
                        array_push($articles, array('news' => $news, 'headline' => $headline, 'title' => $title, 'date' => $date, 'location' => $location, 'locationName' => $locationName, 'link' => $link));
                    }
                }
            }
            require_once(dirname(__FILE__)."/../template/cbe.location.tpl.php");
        }

        if ($type == "cbe_band") {
            $articles = array();
            $tagName = "";
            $result = $this->db->query("SELECT `tag` FROM `band` WHERE `id`='$tagID'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['tag'])) {
                    $tagName = $this->basic->convertToHTMLEntities($row['tag']);
                }
            }
            $result = $this->db->query("SELECT `news`, `headline`, `title`, `date`, `location`, `name` FROM `news_tag` JOIN `news` USING (`news`) JOIN `navigation` ON (`news`.`location` = `navigation`.`id`) WHERE `tag`='$tagID' AND `news_tag`.`type`='cbe_band' ORDER BY `date` DESC");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                && is_string($row['news'])
                && is_string($row['headline'])
                && is_string($row['title'])
                && is_string($row['date'])
                && is_string($row['name'])) {
                    $location = intval(strval($row['location']));
                    if ($this->authentication->locationReadAllowed($location, $this->role->getRole())) {
                        $news = intval(strval($row['news']));
                        $headline = $this->basic->convertToHTMLEntities($row['headline']);
                        $title = $this->basic->convertToHTMLEntities($row['title']);
                        $dateTime->setTimestamp(intval(strval($row['date'])));
                        $date = $dateTime->format("d\.m\.Y");
                        $locationName = $this->basic->convertToHTMLEntities($row['name']);
                        $link = $this->news->generateLink($location, $locationName, "read", $headline, $title, $news);
                        array_push($articles, array('news' => $news, 'headline' => $headline, 'title' => $title, 'date' => $date, 'location' => $location, 'locationName' => $locationName, 'link' => $link));
                    }
                }
            }
            require_once(dirname(__FILE__)."/../template/cbe.band.tpl.php");
        }
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
        $scope = $this->getScope();
        if ($scope != "") {
            $uri = $uri."/".$scope;
            $tagID = $this->getTagID();
            $explodedScope = explode("_", $scope);
            $subScope = $this->db->escapeString($explodedScope[1]);
            $tagName = "";
            $result = $this->db->query("SELECT `tag` FROM `".$subScope."` WHERE `id`='$tagID'");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['tag'])) {
                    $tagName = $this->basic->convertToHTMLEntities($row['tag']);
                }
            }
            $slugify = new Slugify();
            $tagPart = $slugify->slugify($tagName)."-".$tagID;
            $uri = $uri."/".$tagPart;
        }
        return $uri;
    }

    public function getOldURIPartFromRestfulURL(): string
    {
        $uri = "";

        $scope = $this->getScope();
        $tagID = $this->getTagID();
        if ($scope != "" && $tagID > 0) {
            $uri = "index.php?tag=".$tagID."&scope=".$scope;
        }

        return $uri;
    }

    private function getTagID(): int
    {
        $tagID = $this->requestParametersService->fromGet()->getIntegerParameter("tag", -1);
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");

        if ($tagID == -1 && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 2) {
                $tag = $explodedRequestURI[2];
                $explodedTag = explode('-', $tag);
                $explodedTagSlugSize = sizeof($explodedTag);

                // PHPStan error. See https://github.com/phpstan/phpstan/issues/3995
                // @phpstan-ignore greater.alwaysTrue
                if ($explodedTagSlugSize > 0) {
                    $tagID = intval($explodedTag[$explodedTagSlugSize - 1]);
                }
            }
        }

        return $tagID;
    }

    private function getScope(): string
    {
        $scope = $this->requestParametersService->fromGet()->getStringParameter("scope", "");
        $requestURI = $this->requestParametersService->fromGet()->getStringParameter("request_uri", "");
        if ($scope == "" && $requestURI != "") {
            $explodedRequestURI = explode('/', $requestURI);
            if (sizeof($explodedRequestURI) > 1) {
                $scope = $explodedRequestURI[1];
            }
        }
        return $scope;
    }

    // Method used in PHP template file.
    // @phpstan-ignore method.unused
    private function generateRelativeURI(int $location, string $locationName, int $news): string
    {
        if (array_key_exists($location, $this->baseLinks)) {
            $link = $this->baseLinks[$location];
        } else {
            $uri = $this->navigation->getRelativeURI($location, $locationName, true);
            $link = $uri."show=".$news."&action=read";
        }
        return $link;
    }
}
