<?php

namespace marsl\includes;

/**
 * @SuppressWarnings(PHPMD)
 */
class Configuration
{
    /***
     * Database variables
     */
    private string $dbname = "marsl";
    private string $dbuser = "root";
    private string $dbhost = "localhost";
    private string $dbpass = "";

    /***
     * System environment
     */
    private string $timezone = "Europe/Berlin";
    private bool $enableOldURIs = false;
    private string $remoteIPFieldName = "REMOTE_ADDR"; // Usually REMOTE_ADDR, if in a Cloudflare environment might be HTTP_CF_CONNECTING_IP

    /***
     * Metatags
     */
    private string $title = "The marsl CMS";
    private string $subtitle = "Standard Subtitle";
    private string $author = "marsl cms";
    private string $keywords = "cms, content, management, system, blog, software, easy, to, use";
    private string $fb_comments = "";
    private string $fbAppID = "";
    private string $fbAppSecret = "";
    private string $fbPageID = "";
    private string $gpAPIKey = "";
    private string $gpPageID = "";
    private string $twitterPageID = "";

    /***
     * Administrative variables
     */
    private string $sysMail = "noreply@mustermail.de";
    private string $errMail = "bugs@mlrecords.de";
    private string $domain = "http://localhost"; // Domain must not end with a slash. Domain must start with either http:// or https://
    private string $basePath = "/dev/marsl/out"; // If base directory is directly under the domain leave this field empty. Field must begin with a slash and must not end with a slash.
    private string $clusterServer = "";

    /***
     * Marsl CMS API
     */
    private string $appKey = "25eb0de4045a79eaa5d44f89bbad19b3";
    private string $secret = "7375bf48dc9de4ee79a519bd7c4a45ac71d5ad8d30e1282f8b50cce132dabbd367d6c210f63a4423b73b8d8186f4d55e10dc2c5cb29dd9975ade3e3046004c3a";

    /***
     * Statistics Gateway
     */
    private string $statisticsGateway = "matomo"; // Allowed values: "matomo", ""
    private string $statisticsApiProtocol = "http"; // Allowed values: "http", "https"
    private string $statisticsApiHost = "localhost/matomo/";
    private int $statisticsApiPort = -1; // Default port for HTTP is 80, for HTTPS is 443, -1 for default port
    private int $siteId = 1;
    private string $authToken = "";

    /***
     * Last.fm event importer
     */
    private string $lastfmKey = "";
    private string $cities = "Arnsberg;Oeynhausen;Balve;Barsinghausen;Bestwig;Berlin;Bielefeld;Bochum;Bonn;Braunschweig;Bremen;Dieburg;Diepholz;Dortmund;Duisburg;D�ren;D�sseldorf;Eschwege;Essen;Eupen;Frankfurt;Friedrichshafen;Gelsenkirchen;G�tersloh;Hamburg;Hannover;Ha�furt;Hemer;Herford;Hiddenhausen;Karlsruhe;K�ln;Konstanz;Liedolsheim;L�dinghausen;L�nen;L�ttich;Magdeburg;Mannheim;M�nster;Nideggen;N�rnberg;Ochtrup;Osnabr�ck;Paderborn;Recklinghausen;Rodgau;Schee�el;Stukenbrock;Soest;Stuttgart;Trier;Vlotho;Wien;Witten;Wuppertal;W�rzburg";

    /***
     * Recaptcha Codes
     */
    private string $privateRecaptcha = "";
    private string $publicRecaptcha = "";

    /***
     * Web-Push-Keys
     */
    private string $webpushPrivateKey = "";
    private string $webpushPublicKey = "";

    public function getRemoteIPFieldName(): string
    {
        return $this->remoteIPFieldName;
    }

    public function getEnableOldURIs(): bool
    {
        return $this->enableOldURIs;
    }

    public function getLastFMKey(): string
    {
        return $this->lastfmKey;
    }

    public function getCities(): string
    {
        return $this->cities;
    }

    public function getDBName(): string
    {
        return $this->dbname;
    }

    public function getDBUser(): string
    {
        return $this->dbuser;
    }

    public function getDBHost(): string
    {
        return $this->dbhost;
    }

    public function getDBPass(): string
    {
        return $this->dbpass;
    }

    public function getAppKey(): string
    {
        return $this->appKey;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }


    public function getStatisticsGateway(): string
    {
        return $this->statisticsGateway;
    }

    public function getStatisticsApiProtocol(): string
    {
        return $this->statisticsApiProtocol;
    }

    public function getStatisticsApiHost(): string
    {
        return $this->statisticsApiHost;
    }

    public function getStatisticsApiPort(): int
    {
        return $this->statisticsApiPort;
    }

    public function getSiteId(): int
    {
        return $this->siteId;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function getFBComments(): string
    {
        return $this->fb_comments;
    }

    public function getFBAppID(): string
    {
        return $this->fbAppID;
    }

    public function getFBAppSecret(): string
    {
        return $this->fbAppSecret;
    }

    public function getFBPageID(): string
    {
        return $this->fbPageID;
    }

    public function getGPAPIKey(): string
    {
        return $this->gpAPIKey;
    }

    public function getGPPageID(): string
    {
        return $this->gpPageID;
    }

    public function getTwitterPageID(): string
    {
        return $this->twitterPageID;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubTitle(): string
    {
        return $this->subtitle;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function sysMail(): string
    {
        return $this->sysMail;
    }

    public function errMail(): string
    {
        return $this->errMail;
    }

    public function getPrivateRecaptcha(): string
    {
        return $this->privateRecaptcha;
    }

    public function getPublicRecaptcha(): string
    {
        return $this->publicRecaptcha;
    }

    public function getClusterServer(): string
    {
        return $this->clusterServer;
    }

    public function getWebPushPrivateKey(): string
    {
        return $this->webpushPrivateKey;
    }

    public function getWebPushPublicKey(): string
    {
        return $this->webpushPublicKey;
    }
}
