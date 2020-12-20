<?php
require __DIR__ . '/../vendor/autoload.php';

use Phpfastcache\Helper\Psr16Adapter;
$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'user', 'passed', new Psr16Adapter('Files'));

$instagram  = Instagram::withCredentials(new \GuzzleHttp\Client(), 'login', 'password', new Psr16Adapter('Files'));
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
