<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'user', 'password', new Psr16Adapter('Files'));
$instagram->login();

$stories = $instagram->getStories();
print_r($stories);
