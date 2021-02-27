<?php

use InstagramScraper\Exception\InstagramException;
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();

try {
    $username = 'youneverknow';
    $account = $instagram->getAccount($username);

    $taggedCount = $instagram->getAccountMediaTaggedCountByUsername($account->getId());
    echo 'Media Tagged Count: ' . $taggedCount . "\n";
} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
