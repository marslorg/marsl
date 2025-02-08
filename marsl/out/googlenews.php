<?php

namespace marsl;

include_once(dirname(__FILE__)."/includes/errorHandler.php");
include_once(dirname(__FILE__)."/autoload.php");

use DateTime;
use DateTimeZone;
use marsl\includes\Basic;
use marsl\includes\Configuration;
use marsl\includes\DB;
use marsl\modules\Module;
use marsl\modules\News;
use marsl\user\Authentication;
use marsl\user\Role;

class GoogleNews
{
    private Authentication $authentication;
    private Basic $basic;
    private Configuration $configuration;
    private DB $db;
    private News $news;
    private Role $role;

    public function __construct(
        Authentication $authentication,
        Basic $basic,
        Configuration $configuration,
        DB $db,
        News $news,
        Role $role
    ) {
        $this->authentication = $authentication;
        $this->basic = $basic;
        $this->configuration = $configuration;
        $this->db = $db;
        $this->news = $news;
        $this->role = $role;
    }

    /*
     * Initialize the RSS feed for the news articles.
     */
    public function display(): void
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Content-type: application/rss+xml");
        $this->db->connect();
        if ($this->authentication->moduleReadAllowed("news", $this->role->getGuestRole())) {
            $dateTime = new DateTime("now", new DateTimeZone($this->configuration->getTimezone()));
            $feedtitle = $this->configuration->getTitle()." - RSS Feed";
            $feedlink = $this->configuration->getDomain();
            $feeddescription = "RSS Feed von ".$this->configuration->getTitle();
            $items = array();
            $result = $this->db->query("SELECT `news`.`location` AS `location`, `news`.`news` AS `news`, `news`.`teaser` AS `teaser`, `news`.`headline` AS `headline`, `news`.`title` AS `title`, `news`.`postdate` AS `postdate`, `news`.`picture1` AS `picture1`, `news`.`picture2` AS `picture2`, `news`.`text` AS `text` FROM `news`
					JOIN `rights` ON (`rights`.`location` = `news`.`location`)
					JOIN `stdroles` ON (`rights`.`role` = `stdroles`.`guest`)
					WHERE `rights`.`read` = '1' AND `news`.`deleted` = '0' AND `news`.`visible` = '1' ORDER BY `postdate` DESC LIMIT 0,60");
            while ($row = $this->db->fetchArray($result)) {
                if (is_string($row['location'])
                    && is_string($row['news'])
                    && ($row['headline'] == null || is_string($row['headline']))
                    && ($row['title'] == null || is_string($row['title']))
                    && ($row['teaser'] == null || is_string($row['teaser']))
                    && ($row['text'] == null || is_string($row['text']))
                    && is_string($row['postdate'])
                    && ($row['picture1'] == null || is_string($row['picture1']))
                    && ($row['picture2'] == null || is_string($row['picture2']))) {
                    $location = intval(strval($row['location']));
                    $news = intval(strval($row['news']));

                    $domain = $this->configuration->getDomain();
                    $baseURI = $domain.$this->configuration->getBasePath();
                    $headline = is_string($row['headline']) ? $row['headline'] : "";
                    $title = is_string($row['title']) ? $row['title'] : "";
                    $link = $this->generateLink(
                        $baseURI,
                        $location,
                        $this->basic->convertToHTMLEntities($headline),
                        $this->basic->convertToHTMLEntities($title),
                        $news
                    );

                    $teaser = $this->basic->convertToHTMLEntities(is_string($row['teaser']) ? $row['teaser'] : "");
                    $text = $this->basic->convertToHTMLEntities(is_string($row['text']) ? $row['text'] : "");
                    $title = htmlspecialchars($headline).": ".htmlspecialchars($title);
                    $dateTime->setTimestamp(intval(strval($row['postdate'])));
                    $date = $dateTime->format("D, d M Y H:i:s O");

                    $picID1 = intval(strval(is_string($row['picture1']) ? $row['picture1'] : "0"));
                    $picID2 = intval(strval(is_string($row['picture2']) ? $row['picture2'] : "0"));
                    $teaserPicture = "empty";
                    $newsPicture = "empty";
                    $result2 = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picID1'");
                    while ($row2 = $this->db->fetchArray($result2)) {
                        if (is_string($row2['url'])) {
                            $teaserPicture = $domain."/news/".$this->basic->convertToHTMLEntities($row2['url']);
                        }
                    }
                    $result2 = $this->db->query("SELECT `url` FROM `news_picture` WHERE `picture`='$picID2'");
                    while ($row2 = $this->db->fetchArray($result2)) {
                        if (is_string($row2['url'])) {
                            $newsPicture = $domain."/news/".$this->basic->convertToHTMLEntities($row2['url']);
                        }
                    }

                    $modules = $this->basic->getModules();
                    $moduleTags = array();
                    foreach ($modules as $module) {
                        $classPath = "\\marsl\\modules\\".$module['class'];
                        $class = ComponentBuilder::buildDependencies()->make($classPath);
                        if (($class instanceof Module) && $class->isTaggable()) {
                            $tagList = $class->getTagList();
                            foreach ($tagList as $tagType) {
                                $typeID = $module['file']."_".$tagType['type'];
                                $typeName = $tagType['text'];
                                $tags = $class->getTags($tagType['type'], $news);
                                foreach ($tags as $tag) {
                                    array_push($moduleTags, htmlspecialchars($tag['tag']));
                                }
                            }
                        }
                    }

                    array_push($items, array('link' => $link, 'teaser' => $teaser, 'text' => $text, 'title' => $title, 'date' => $date, 'teaserPicture' => $teaserPicture, 'newsPicture' => $newsPicture, 'tags' => $moduleTags));
                }
            }
            require_once("template/googlenews.tpl.php");
        }
        $this->db->close();
    }

    private function generateLink(string $baseURI, int $location, string $headline, string $title, int $news): string
    {
        $link = "";

        $uri = $this->news->generateLink($location, null, "read", $headline, $title, $news);
        $link = $baseURI."/".$uri;

        return $link;
    }
}

$googleNews = ComponentBuilder::buildDependencies()->make('marsl\GoogleNews');

if ($googleNews instanceof GoogleNews) {
    $googleNews->display();
}
