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

$instagram->pending();
$inbox = $instagram->getInbox();
$instagram->seenInbox();

var_dump($inbox);