<?php
include_once (dirname(__FILE__)."/../errorHandler.php");
include_once (dirname(__FILE__)."/../dbsocket.php");
include_once (dirname(__FILE__)."/../../user/user.php");

/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions
$db = new DB();
$db->connect();
$user = new User($db);
$admin = !$user->isAdmin();
$db->close();

$_CONFIG = array(

    'disabled' => $admin,
    'denyZipDownload' => true,
    'denyUpdateCheck' => true,
    'denyExtensionRename' => true,

    'theme' => "oxygen",

    'uploadURL' => "../../shared",
    'uploadDir' => "",

    'dirPerms' => 0755,
    'filePerms' => 0644,

    'access' => array(

        'files' => array(
            'upload' => true,
            'delete' => false,
            'copy' => true,
            'move' => false,
            'rename' => false
        ),

        'dirs' => array(
            'create' => true,
            'delete' => false,
            'rename' => false
        )
    ),

    'deniedExts' => "exe com msi bat php phps phtml php3 php4 cgi pl",

    'types' => array(

        // TinyMCE types
        'file'    =>  "",
        'media'   =>  "swf flv avi mpg mpeg qt mov wmv asf rm",
        'image'   =>  "*img",
    ),

    'filenameChangeChars' => array(/*
        ' ' => "_",
        ':' => "."
    */),

    'dirnameChangeChars' => array(/*
        ' ' => "_",
        ':' => "."
    */),

    'mime_magic' => "",

    'maxImageWidth' => 640,
    'maxImageHeight' => 640,

    'thumbWidth' => 200,
    'thumbHeight' => 200,

    'thumbsDir' => ".thumbs",

    'jpegQuality' => 100,

    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'KCFINDER_',

    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION
    '_check4htaccess' => true,
    '_tinyMCEPath' => "../jscripts/tiny_mce",

    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/full/directory/path",

    //'_sessionDomain' => ".mysite.com",
    //'_sessionPath' => "/my/path",
);

?>