<?php
include_once(dirname(__FILE__)."/errorHandler.php");

class Configuration {
	
	/***
	 * Database variables
	 */
	private $dbname = "music2test";
	private $dbuser = "root";
	private $dbhost = "localhost";
	private $dbpass = "123";
	
	/***
	 * System environment
	 */
	private $timezone = "Europe/Berlin";
	
	/***
	 * Metatags 
	 */
	private $title = "Music2Web.de";
	private $subtitle = "Das Musikportal für alternative Popkultur.";
	private $author = "Music2Web.de e. V.";
	private $keywords = "Musik, Konzertberichte, Konzertfotos, MP3, News, Musiknews, Music, Musicnews, Community";
	private $fb_comments = "";
	
	/***
	 * Administrative variables
	 */
	private $sysMail = "noreply@music2web.de";
	private $errMail = "webmaster@music2web.de";
	private $domain = "http://www.music2web.de";
	
	/***
	 * Last.fm event importer
	 */
	private $lastfmKey = "";
	private $cities = "Arnsberg;Oeynhausen;Balve;Barsinghausen;Bestwig;Berlin;Bielefeld;Bochum;Bonn;Braunschweig;Bremen;Dieburg;Diepholz;Dortmund;Duisburg;Düren;Düsseldorf;Eschwege;Essen;Eupen;Frankfurt;Friedrichshafen;Gelsenkirchen;Gütersloh;Hamburg;Hannover;Haßfurt;Hemer;Herford;Hiddenhausen;Karlsruhe;Köln;Konstanz;Liedolsheim;Lüdinghausen;Lünen;Lüttich;Magdeburg;Mannheim;Münster;Nideggen;Nürnberg;Ochtrup;Osnabrück;Paderborn;Recklinghausen;Rodgau;Scheeßel;Stukenbrock;Soest;Stuttgart;Trier;Vlotho;Wien;Witten;Wuppertal;Würzburg";
	
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
}
?>