<?php
require __DIR__ . '/../../../../vendor/autoload.php';

use InstagramScraper\Instagram;
use Phpfastcache\Helper\Psr16Adapter;

$instagram  = Instagram::withCredentials(new \GuzzleHttp\Client(), 'login', 'password', new Psr16Adapter('Files'));
$instagram->login();
$instagram->saveSession();

$posts  = $instagram->getFeed();

foreach ($posts as $post){
    echo $post->getImageHighResolutionUrl()."\n";
}