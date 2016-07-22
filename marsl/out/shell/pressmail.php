#!/usr/bin/php
<?php

include_once(dirname(__FILE__)."/../includes/basic.php");
include_once(dirname(__FILE__)."/../includes/dbsocket.php");
include_once(dirname(__FILE__)."/../includes/encryption.php");
include_once(dirname(__FILE__)."/../includes/config.inc.php");

$config = new Configuration();

date_default_timezone_set($config->getTimezone());


$basic = new Basic();

$db = new DB();

$ServerName = "{imap.gmail.com:993/imap/ssl}INBOX";
$UserName = "red@music2web.de";
$PassWord = "e%aH2MVD";
$boardUser = "23";
$board = "35";
$ip = "127.0.0.1";
$crypt = new Encryption();

$db->connect();
$mbox = imap_open($ServerName, $UserName, $PassWord);

if ($hdr = imap_check($mbox)) {
	$msgCount = $hdr->Nmsgs;
}

$MN=$msgCount;

if ($MN>0) {
	$overview=imap_fetch_overview($mbox,"1:$MN",0);
	$size=sizeof($overview);
	for($i=0;$i<$size;$i++){
		if (isset($overview[$i])) {
			$val=$overview[$i];
			$msgno=$val->msgno;
			$subElements=imap_mime_header_decode($val->subject);
			$subj = "";
			for ($e=0; $e<count($subElements); $e++) {
				$subj = $subj." ".$subElements[$e]->text;
			}
			
			$header = imap_headerinfo($mbox, $msgno);
			$email = $header->from[0]->mailbox."@".$header->from[0]->host;
			
			// GET TEXT BODY
			$dataTxt = get_part($mbox, $msgno, "TEXT/PLAIN");
			 
			// GET HTML BODY
			$dataHtml = get_part($mbox, $msgno, "TEXT/HTML");
			 
			if ($dataHtml != "") {
				$msgBody = $dataHtml;
		 	}
		 	else {
		    	$msgBody = nl2br($dataTxt);
		   		$msgBody = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/i","$1http://$2",    $msgBody);
		   		$msgBody = preg_replace("/([\w]+:\/\/[\w-?&;#~=\.\/\@]+[\w\/])/i","<A    TARGET=\"_blank\" HREF=\"$1\">$1</A>", $msgBody);
		   		$msgBody = preg_replace("/([\w-?&;#~=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/i","<A    HREF=\"mailto:$1\">$1</A>",$msgBody);
		 	}
			// To out put the message body to the user simply print $msgBody like this.
			
		 	$curTime = time();
		 	
		 	$msgBody = "<h3>".$email."</h3>".$basic->cleanHTML($msgBody);
		 	$msgBody = $db->escape($msgBody);
		 	$subj = $db->escape($subj);
		 	
		 	$db->query("INSERT INTO `thread`(`board`,`postcount`,`type`,`title`,`author`,`viewcount`) VALUES('$board','0','0','$subj','$boardUser','0')");
		 	$threadID = $db->getLastID();
		 	
		 	$result = $db->query("SELECT `threadcount` FROM `board` WHERE `board`='$board'");
		 	while ($row = $db->fetchArray($result)) {
		 		$threadcount = $row['threadcount']+1;
		 		$db->query("UPDATE `board` SET `threadcount`='$threadcount' WHERE `board`='$board'");
		 	}
		 	
		 	$db->query("INSERT INTO `post`(`author`, `thread`, `date`, `operator`, `lastedit`, `content`, `ip`, `deleted`) VALUES('$boardUser','$threadID','$curTime','0','0','$msgBody','$ip','0')");
		 	$postID = $db->getLastID();
		 	$result = $db->query("SELECT `postcount` FROM `thread` WHERE `thread`='$threadID'");
		 	while ($row = $db->fetchArray($result)) {
		 		$postcount = $row['postcount']+1;
		 		$db->query("UPDATE `thread` SET `postcount`='$postcount', `lastpost`='$postID' WHERE `thread`='$threadID'");
		 	}
		 	$result = $db->query("SELECT `postcount` FROM `board` WHERE `board`='$board'");
		 	while ($row = $db->fetchArray($result)) {
		 		$postcount = $row['postcount']+1;
		 		$db->query("UPDATE `board` SET `postcount`='$postcount' WHERE `board`='$board'");
		 	}
		
			
			$struct = imap_fetchstructure($mbox, $msgno);
			$contentParts = 0;
			if (isset($struct->parts)) {
				if (is_array($struct->parts)) {
					$contentParts = count($struct->parts);
				}
			}
			
			if ($contentParts >= 2) {
				for ($i=2; $i<=$contentParts; $i++) {
					$att[$i-2] = imap_bodystruct($mbox, $msgno, $i);
				}
				for ($k=0; $k<sizeof($att); $k++) {
					$temporary = $basic->tempFileKey();
					if (is_array($att[$k]->parameters)) {
						$strFileName = $att[$k]->parameters[0]->value;
						if ((strtolower($strFileName) == "windows-1252")||(strtolower($strFileName) == "utf-8")) {
							if (isset($att[$k]->parameters[1])) {
								$strFileName = $att[$k]->parameters[1]->value;
							}
						}
						
						$strFileType = strrev(substr(strrev($strFileName),0,4));
						$ContentType = "12345";
						if ($strFileType == ".xml")
							$ContentType = "text/xml";
						if ($strFileType == ".xsl")
							$ContentType = "text/xsl";
						if ($strFileType == ".css")
							$ContentType = "text/css";
						if ($strFileType == ".php")
							$ContentType = "text/php";
						if ($strFileType == ".asp")
							$ContentType = "text/asp";
						if ($strFileType == ".txt")
							$ContentType = "text/plain";
						
						$fileContent = imap_fetchbody($mbox, $msgno, $k+2);
						if (strlen($fileContent)>0) {
							$newFileName = generateFileName();
							$key = generateKey();
							if (substr($ContentType,0,4) == "text") {
								$fileContent = imap_qprint($fileContent);
							}
							else {
								$fileContent = base64_decode($fileContent);
							}
							if (strlen($fileContent)>0) {
								$cryptedContent = $crypt->encrypt($fileContent, $key);
								file_put_contents(dirname(__FILE__)."/../files/".$newFileName, $cryptedContent);
								$db->query("INSERT INTO `attachment`(`servername`, `realname`, `key`, `temporary`) VALUES('$newFileName', '$strFileName', '$key', '$temporary')");
								$fileID = $db->getLastID();
								$db->query("INSERT INTO `post_attachment`(`post`,`file`) VALUES('$postID', '$fileID')");
							}
						}
					}
				}
			}
			
			imap_delete($mbox, $msgno);
		}
	}
	
	imap_expunge($mbox);
}
imap_close($mbox);
$db->close();

function get_mime_type(&$structure) {
	$primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
	if($structure->subtype) {
		return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
	}
	return "TEXT/PLAIN";
}
function get_part($stream, $msg_number, $mime_type, $structure = false,$part_number    = false) {
	 
	if(!$structure) {
		$structure = imap_fetchstructure($stream, $msg_number);
	}
	if($structure) {
		if($mime_type == get_mime_type($structure)) {
			if(!$part_number) {
				$part_number = "1";
			}
			$text = imap_fetchbody($stream, $msg_number, $part_number);
			if($structure->encoding == 3) {
				return imap_base64($text);
			} else if($structure->encoding == 4) {
				return imap_qprint($text);
			} else {
				return $text;
			}
		}
		 
		if($structure->type == 1) /* multipart */ {
			while(list($index, $sub_structure) = each($structure->parts)) {
				$prefix = "";
				if($part_number) {
					$prefix = $part_number . '.';
				}
				$data = get_part($stream, $msg_number, $mime_type, $sub_structure,$prefix .    ($index + 1));
				if($data) {
					return $data;
				}
			} // END OF WHILE
		} // END OF MULTIPART
	} // END OF STRUTURE
	return false;
} // END OF FUNCTION

function generateFileName() {
	$db = new DB();
	$basic = new Basic();
	$filename = $db->escape($basic->randomHash());
	while ($db->isExisting("SELECT * FROM `attachment` WHERE `servername`='$filename'")) {
		$filename = $db->escape($basic->randomHash());
	}
	return $filename;
}

function generateKey() {
	$db = new DB();
	$basic = new Basic();
	$key = $db->escape($basic->randomHash());
	while ($db->isExisting("SELECT * FROM `attachment` WHERE `key`='$key'")) {
		$key = $db->escape($basic->randomHash());
	}
	return $key;
}

?>