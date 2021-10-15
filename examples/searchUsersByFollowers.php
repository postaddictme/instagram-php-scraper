<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();
sleep(2); // Delay to mimic user

$username = 'kevin';
$search_result = [];
$account = $instagram->getAccount($username);
sleep(1);
$search_result = $instagram->searchFollowers($account->getId(), 'andy'); // Search users with a nickname 'andy' by followers 'kevin'
echo '<pre>' . json_encode($search_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
