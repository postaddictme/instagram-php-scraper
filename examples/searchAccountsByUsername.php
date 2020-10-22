<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();


$accounts = $instagram->searchAccountsByUsername('raiym');

$account = $accounts[0];
// Following fields are available in this request
echo "Account info:\n";
echo "Username: {$account->getUsername()}";
echo "Full name: {$account->getFullName()}";
echo "Profile pic url: {$account->getProfilePicUrl()}";

