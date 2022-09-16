<?php
include_once(dirname(__FILE__)."/errorHandler.php");
include_once(dirname(__FILE__)."/config.inc.php");
include_once(dirname(__FILE__)."/dbsocket.php");
include_once(dirname(__FILE__)."/htmlpurifier/library/HTMLPurifier.auto.php");
include_once(dirname(__FILE__)."/../modules/urlloader.php");
include_once(dirname(__FILE__)."/../user/role.php");

class Basic {

	private $db;
	private $auth;
	private $role;

	public function __construct($db, $auth, $role) {
		$this->db = $db;
		$this->auth = $auth;
		$this->role = $role;
	}

	public function convertToHTMLEntities($dirt) {
if ($dirt != null) {
    return htmlentities($dirt, 0, 'ISO-8859-1');
}
		else {
			return $dirt;
		}
	}
	
	/*
	 * Cleans the HTML output of tinymce to prevent XSS attacks.
	 */
	public function cleanHTML($dirt) {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding','ISO-8859-1');
		$config->set('HTML.Doctype','XHTML 1.1');
		$config->set('Core.EscapeNonASCIICharacters', true);
		$def = $config->getHTMLDefinition(true);
		$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($dirt);
	}
	
	/*
	 * Cleans the HTML output of tinymce only to some allowed elements. E.g. used in the board.
	 */
	public function cleanStrict($dirt) {
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core.Encoding', 'ISO-8859-1');
		$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
		$config->set('Core.EscapeNonASCIICharacters', true);
		$config->set('HTML.AllowedElements', array('a','b','strong','i','em','u','img','blockquote','s','br'));
		$config->set('HTML.AllowedAttributes', array('a.href', 'img.src', '*.alt', '*.title', '*.border', '*.align', '*.width', '*.height', 'img.vspace', 'img.hspace', 'a.target', 'a.rel'));
		$config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
		$config->set('AutoFormat.RemoveEmpty', true);
		$config->set('Core.RemoveProcessingInstructions', true);
		$config->set('HTML.TargetBlank', true);
		$config->set('HTML.Nofollow', true);
		$purifier = new HTMLPurifier($config);
		return $purifier->purify($dirt);
	}
	
	/*
	 * Get the location for the standard page.
	 */
	public function getHomeLocation() {
		$homepage = -1;
		$result = $this->db->query("SELECT `homepage` FROM `homepage`");
		while ($row = $this->db->fetchArray($result)) {
			$homepage = $row['homepage'];
		}
		return $homepage;
	}
	
	/*
	 * Get the title of the current page.
	 */
	public function getTitle() {
		$config = new Configuration();
		$urlloader = new URLLoader($this->db, $this->auth, $this->role);
		$title = $urlloader->getTitle();
		if ($title!=null) {
			return $urlloader->getTitle().$config->getTitle();
		}
		else {
			return $config->getTitle()." - ".$config->getSubTitle();
		}
	}
	
	/*
	 * Gets the page corresponding thumbnail.
	 */
	public function getImage() {
		$urlloader = new URLLoader($this->db, $this->auth, $this->role);
		return $urlloader->getImage();
	}
	
	/*
	 * Check an input if it is an e-mail-adresse.
	 */
	public function checkMail($email) {
		$regexp="/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
  		if ( !preg_match($regexp, $email) ) {
       		return false;
  		}
  		return true;
	}
	
	/*
	 * Get module information for a given unique file name.
	 */
	public function getModule($file) {
		$success = false;
		$module = array();
		$file = $this->db->escapeString($file);
		$result = $this->db->query("SELECT `name`, `class`, `file` FROM `module` WHERE `file`='$file'");
		while ($row = $this->db->fetchArray($result)) {
			$module['name'] = $row['name'];
			$module['class']  = $row['class'];
			$module['file'] = $row['file'];
			$success = true;
		}

		$methodResult = $module;

		if (!$success) {
			$methodResult = false;
		}
		return $methodResult;
	}
	
	/*
	 * Get all modules.
	 */
	public function getModules() {
		$modules = array();
		$result = $this->db->query("SELECT `name`, `file`, `class` FROM `module`");
		while ($row = $this->db->fetchArray($result)) {
			array_push($modules, array('name' => $row['name'],'file' => $row['file'],'class' => $row['class']));
		}
		return $modules;
	}

	/*
	 * Set a new session ID.
	 */
	public function session() {
		$db = new DB();
		$session = $this->randomHash();
		$session = $this->db->escapeString($session);
		while ($this->db->isExisting("SELECT `sessionid` FROM `user` WHERE `sessionid`='$session' LIMIT 1")) {
			$session = $this->randomHash();
			$session = $this->db->escapeString($session);
		}
		return $session;
	}
	
	/*
	 * Set a new double-opt-in confirmation ID.
	 */
	public function confirmID() {
		$db = new DB();
		$confirmID = $this->randomHash();
		$confirmID = $this->db->escapeString($confirmID);
		while ($this->db->isExisting("SELECT `confirm_id` FROM `email` WHERE `confirm_id`='$confirmID' LIMIT 1")) {
			$confirmID = $this->randomHash();
			$confirmID = $this->db->escapeString($confirmID);
		}
		return $confirmID;
	}
	
	/*
	 * Get a random hash.
	 */
	public function randomHash() {
		mt_srand(time());
		$randomHash = mt_rand().mt_rand().mt_rand().mt_rand();
		$randomHash = md5($randomHash);
		return $randomHash;
	}
	
	public function randomSHA512() {
		mt_srand(time());
		$randomHash = mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand().mt_rand();
		$randomHash = hash("sha512", $randomHash);
		return $randomHash;
	}

	/*
	 * Return the numeric value of a month.
	 */
	public function getNumericMonth($month) {
		if ($month=="Jan") {
			return 1;
		}
		if ($month == "Feb") {
			return 2;
		}
		if ($month=="Mar") {
			return 3;
		}
		if ($month == "Apr") {
			return 4;
		}
		if ($month=="May") {
			return 5;
		}
		if ($month == "Jun") {
			return 6;
		}
		if ($month=="Jul") {
			return 7;
		}
		if ($month == "Aug") {
			return 8;
		}
		if ($month=="Sep") {
			return 9;
		}
		if ($month == "Oct") {
			return 10;
		}
		if ($month=="Nov") {
			return 11;
		}
		if ($month == "Dec") {
			return 12;
		}
		
		return 0;
	}
	
	/**
	 * xml2array() will convert the given XML text to an array in the XML structure.
	 * Link: http://www.bin-co.com/php/scripts/xml2array/
	 * Arguments : $contents - The XML text
	 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
	 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
	 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
	 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
	 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
	 */
	public function xml2array($contents, $get_attributes=1, $priority = 'tag') {
		if(!$contents) return array();
	
		if(!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}
	
		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);
	
		if(!$xml_values) return;//Hmm...
	
		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();
	
		$current = &$xml_array; //Refference
	
		//Go through the tags.
		$repeated_tag_index = array();//Multiple tags with same name will be turned into an array
		foreach($xml_values as $data) {
			unset($attributes,$value);//Remove existing values, or there will be trouble
	
			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data);//We could use the array by itself, but this cooler.
	
			$result = array();
			$attributes_data = array();
	
			if(isset($value)) {
				if($priority == 'tag') $result = $value;
				else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
			}
	
			//Set the attributes too.
			if(isset($attributes) and $get_attributes) {
				foreach($attributes as $attr => $val) {
					if($priority == 'tag') $attributes_data[$attr] = $val;
					else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
	
			//See tag status and do the needed.
			if($type == "open") {//The starting of the tag '<tag>'
				$parent[$level-1] = &$current;
				if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
					$repeated_tag_index[$tag.'_'.$level] = 1;
	
					$current = &$current[$tag];
	
				} else { //There was another element with the same tag name
	
					if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						$repeated_tag_index[$tag.'_'.$level]++;
					} else {//This section will make the value an array if multiple tags with the same name appear together
						$current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
						$repeated_tag_index[$tag.'_'.$level] = 2;
	
						if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
							$current[$tag]['0_attr'] = $current[$tag.'_attr'];
							unset($current[$tag.'_attr']);
						}
	
					}
					$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
					$current = &$current[$tag][$last_item_index];
				}
	
			} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 1;
					if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;
	
				} else { //If taken, put all things inside a list(array)
					if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...
	
						// ...push the new element into that array.
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
	
						if($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag.'_'.$level]++;
	
					} else { //If it is not an array...
						$current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
						$repeated_tag_index[$tag.'_'.$level] = 1;
						if($priority == 'tag' and $get_attributes) {
							if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
	
								$current[$tag]['0_attr'] = $current[$tag.'_attr'];
								unset($current[$tag.'_attr']);
							}
	
							if($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
					}
				}
	
			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level-1];
			}
		}
	
		return($xml_array);
	}
	
	public function tempFileKey() {
		$tempKey = $this->db->escapeString($this->randomHash());
		while($this->db->isExisting("SELECT `temporary` FROM `attachment` WHERE `temporary`='$tempKey' LIMIT 1")) {
			$tempKey = $this->db->escapeString($this->randomHash());
		}
		return $tempKey;
	}
}
?>