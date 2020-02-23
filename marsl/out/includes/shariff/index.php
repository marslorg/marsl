<?php

require_once __DIR__.'/vendor/autoload.php';
include_once (dirname(__FILE__)."/../config.inc.php");

use Heise\Shariff\Backend;

/**
 * Demo Application using Shariff Backend
 */
class Application
{
    /**
     * Sample configuration
     *
     * @var array
     */
    private static function getConfiguration()
    {
    	$config = new Configuration();
    	
    	$configuration = [
	        'cache' => [
	            'ttl' => 60
	        ],
	        'domains' => [
	            $config->getDomain(),
	        	substr($config->getDomain(), 8),
	        	substr($config->getDomain(), 7),
	        	substr($config->getDomain(), 12),
	        	substr($config->getDomain(), 11)
	        ],
	        'services' => [
	            'Facebook',
	            'LinkedIn',
	            'Reddit',
	            'StumbleUpon',
	            'Flattr',
	            'Pinterest',
	            'Xing',
	            'AddThis',
	            'Vk'
	        ],
    		'Facebook' => [
    			'app_id' => $config->getFBAppID(),
    			'secret' => $config->getFBAppSecret()
    		]
    	];
    	return $configuration;
    }

    public static function run()
    {
        header('Content-type: application/json');

        $url = isset($_GET['url']) ? $_GET['url'] : '';
        if ($url) {
            $shariff = new Backend(self::getConfiguration());
            echo json_encode($shariff->get($url));
        } else {
            echo json_encode(null);
        }
    }
}

Application::run();
