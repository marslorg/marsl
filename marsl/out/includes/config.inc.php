<?php

class Configuration {
	
	/***
	 * Database variables
	 */
	private $dbname = "marsl";
	private $dbuser = "root";
	private $dbhost = "localhost";
	private $dbpass = "";
	
	/***
	 * System environment
	 */
	private $timezone = "Europe/Berlin";
	
	/***
	 * Metatags 
	 */
	private $title = "The marsl CMS";
	private $subtitle = "Standard Subtitle";
	private $author = "marsl cms";
	private $keywords = "cms, content, management, system, blog, software, easy, to, use";
	private $fb_comments = "";
	private $fbAppID = "";
	private $fbAppSecret = "";
	private $fbPageID = "";
	private $gpAPIKey = "";
	private $gpPageID = "";
	private $twitterPageID = "";
	
	/***
	 * Administrative variables
	 */
	private $sysMail = "noreply@mustermail.de";
	private $errMail = "bugs@mlrecords.de";
	private $domain = "http://localhost"; // Domain must not end with a slash. Domain must start with either http:// or https://
	private $basePath = "/dev/marsl/out"; // If base directory is directly under the domain leave this field empty. Field must begin with a slash and must not end with a slash.
	private $clusterServer = "";

	/***
	 * Marsl CMS API
	 */
	private $appKey = "c1706b9961e7942112f93bb557702926";
	private $secret = "8391bfcc1edfc165fcec9ea6dd7860598ebaa3374f13a7d23b23eb2f0b67d00b6cccd48bd12c04aa2bf1d1e2e5502e896fe967d1f8988879fe49b6dfd74eb3f7";
	
	/***
	 * Last.fm event importer
	 */
	private $lastfmKey = "";
	private $cities = "Arnsberg;Oeynhausen;Balve;Barsinghausen;Bestwig;Berlin;Bielefeld;Bochum;Bonn;Braunschweig;Bremen;Dieburg;Diepholz;Dortmund;Duisburg;D�ren;D�sseldorf;Eschwege;Essen;Eupen;Frankfurt;Friedrichshafen;Gelsenkirchen;G�tersloh;Hamburg;Hannover;Ha�furt;Hemer;Herford;Hiddenhausen;Karlsruhe;K�ln;Konstanz;Liedolsheim;L�dinghausen;L�nen;L�ttich;Magdeburg;Mannheim;M�nster;Nideggen;N�rnberg;Ochtrup;Osnabr�ck;Paderborn;Recklinghausen;Rodgau;Schee�el;Stukenbrock;Soest;Stuttgart;Trier;Vlotho;Wien;Witten;Wuppertal;W�rzburg";
	
	/***
	 * Recaptcha Codes
	 */
	private $privateRecaptcha = "6LchdekSAAAAAFIzvFEfI4Fz9rstTEZ-1PUK4nkO";
	private $publicRecaptcha = "6LchdekSAAAAALPCXPe191Yv_Hdkex3XnXsjJgDR";

	/***
	 * Web-Push-Keys
	 */
	private $webpushPrivateKey = "Z2-a6_AcNNwQKN7qLt6FlkRapDxLb9-M0vze6cSX8zM";
	private $webpushPublicKey = "BL8jnwynx9RcT8FEiFSabFunD2y_4u-zGOgSw3LyZZe-lDd38Gd-j-qVgw8AUMpxWNbFyCoXNQ3WFM-ZJeTp5fo";
	
	public function getLastFMKey() {
		return $this->lastfmKey;
	}
	
	public function getCities() {
		return $this->cities;
	}
	
	public function getDBName() {
		return $this->dbname;
	}
	
	public function getDBUser() {
		return $this->dbuser;
	}
	
	public function getDBHost() {
		return $this->dbhost;
	}
	
	public function getDBPass() {
		return $this->dbpass;
	}

	public function getAppKey() {
		return $this->appKey;
	}

	public function getSecret() {
		return $this->secret;
	}
	
	public function getFBComments() {
		return $this->fb_comments;
	}
	
	public function getFBAppID() {
		return $this->fbAppID;
	}
	
	public function getFBAppSecret() {
		return $this->fbAppSecret;
	}
	
	public function getFBPageID() {
		return $this->fbPageID;
	}
	
	public function getGPAPIKey() {
		return $this->gpAPIKey;
	}
	
	public function getGPPageID() {
		return $this->gpPageID;
	}
	
	public function getTwitterPageID() {
		return $this->twitterPageID;
	}
	
	public function getDomain() {
		return $this->domain;
	}

	public function getBasePath() {
		return $this->basePath;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function getSubTitle() {
		return $this->subtitle;
	}
	
	public function getAuthor() {
		return $this->author;
	}
	
	public function getKeywords() {
		return $this->keywords;
	}
	
	public function getTimezone() {
		return $this->timezone;
	}
	
	public function sysMail() {
		return $this->sysMail;
	}
	
	public function errMail() {
		return $this->errMail;
	}
	
	public function getPrivateRecaptcha() {
		return $this->privateRecaptcha;
	}
	
	public function getPublicRecaptcha() {
		return $this->publicRecaptcha;
	}

	public function getClusterServer() {
		return $this->clusterServer;
	}

	public function getWebPushPrivateKey() {
		return $this->webpushPrivateKey;
	}

	public function getWebPushPublicKey() {
		return $this->webpushPublicKey;
	}
}
?>
