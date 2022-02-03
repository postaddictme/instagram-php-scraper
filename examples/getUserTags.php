<?php

use InstagramScraper\Instagram;
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$username = 'username';
$password = 'password';

$instagram  = Instagram::withCredentials(
    new \GuzzleHttp\Client(),
    $username,
    $password,
    new Psr16Adapter('Files')
);

$instagram->login();

$instagram->saveSession();


$accountId = $instagram->getAccountInfo($username)->getId();

$tags = $instagram->getUserTags($accountId, 12);

var_dump($tags);