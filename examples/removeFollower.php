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

$followers = $instagram->getFollowers($accountId);

if (isset($followers[0])) {

    $lastFollower = $followers[0];
    $lastFollowerId = $lastFollower['id'];
    $lastFollowerUsername = $lastFollower['username'];

    $instagram->removeFollower($lastFollowerId);

    print_r("{$lastFollowerUsername} Removed.");
} else {

    print_r('Follower Not Found.');
}
