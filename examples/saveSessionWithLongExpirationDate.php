<?php
ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);//Example how to:
//1. Use ttl in saveSettings to extend expiration date of apc_cache_info
//2. Extend expiration date of cache on initial stats_stat_correlation
//
//Why:
//I received a lot of emails from instagram with title :'Nieuwe aanmelding bij Instagram via Chrome op Windows' (new login an Instagram via Chrome on Windows)
//
//Defailt ttl was 900 seconds, meaning a new loging every 15 minutes (on QNAP php)
//
//Setting a longer cache alse speedup login requests from this api


require __DIR__ . '/../vendor/autoload.php';

use Phpfastcache\Helper\Psr16Adapter;
$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'login', 'password', new Psr16Adapter('Files'));
$instagram->login();

//Save Session to reuse Insragram LoginL
$instagram->saveSession(); //no argument given -> no change in expiration date
$instagram->saveSession(3600);  //expiration set to now + 1 hour
$instagram->saveSession(86400); //expiration set to now + 1 day

$posts  = $instagram->getFeed();

foreach ($posts as $post){
    echo $post->getImageHighResolutionUrl()."\n";
}


//Set initial Expiration date
use Phpfastcache\Config\Config;
$config = new Config();
$config->setDefaultTtl(86400); //default ttl in seconds, should be as long as instagram login stays valid (don't know how long this is)
$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'user', 'passed', new Psr16Adapter('Files', $config));
$instagram->login();
$instagram->saveSession(); //Expiration date set to value as specified in config - defaultTtl

$posts  = $instagram->getFeed();

foreach ($posts as $post){
    echo $post->getImageHighResolutionUrl()."\n";
}
