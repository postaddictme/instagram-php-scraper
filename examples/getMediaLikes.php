<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();

// Get media likes by shortcode
$likes = $instagram->getMediaLikesByCode('CeExu4aD9RC',100, '');
$like = $likes[0];
echo "Liker info: \n";
echo "Username: {$like['username']}\n";
echo "Full Name: {$like['fullName']}\n";



