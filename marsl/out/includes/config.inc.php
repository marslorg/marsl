<?php
include_once(dirname(__FILE__)."/errorHandler.php");

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
	
	/***
	 * Administrative variables
	 */
	private $sysMail = "noreply@mustermail.de";
	private $errMail = "bugs@mlrecords.de";
	private $domain = "http://www.musterdomain.de";
	
	/***
	 * Last.fm event importer
	 */
	private $lastfmKey = "";
	private $cities = "Arnsberg;Oeynhausen;Balve;Barsinghausen;Bestwig;Berlin;Bielefeld;Bochum;Bonn;Braunschweig;Bremen;Dieburg;Diepholz;Dortmund;Duisburg;Düren;Düsseldorf;Eschwege;Essen;Eupen;Frankfurt;Friedrichshafen;Gelsenkirchen;Gütersloh;Hamburg;Hannover;Haßfurt;Hemer;Herford;Hiddenhausen;Karlsruhe;Köln;Konstanz;Liedolsheim;Lüdinghausen;Lünen;Lüttich;Magdeburg;Mannheim;Münster;Nideggen;Nürnberg;Ochtrup;Osnabrück;Paderborn;Recklinghausen;Rodgau;Scheeßel;Stukenbrock;Soest;Stuttgart;Trier;Vlotho;Wien;Witten;Wuppertal;Würzburg";
	
	/***
	 * Recaptcha Codes
	 */
	private $privateRecaptcha = "6LchdekSAAAAAFIzvFEfI4Fz9rstTEZ-1PUK4nkO";
	private $publicRecaptcha = "6LchdekSAAAAALPCXPe191Yv_Hdkex3XnXsjJgDR";
	
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
	
	public function getFBComments() {
		return $this->fb_comments;
	}
	
	public function getDomain() {
		return $this->domain;
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
}
?>
