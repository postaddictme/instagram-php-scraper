<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';


$settings=json_decode(file_get_contents('settings.json'));
$username = $settings->username;
$password = $settings->password;

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), $username, $password, new Psr16Adapter('Files'));
$instagram->login();

$stories = $instagram->getStories();
print_r($stories);
