<?php

use InstagramScraper\Exception\InstagramException;
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();

try {
    $username = 'youneverknow';
    $account = $instagram->getAccount($username);

    $tagged = $instagram->getAccountMediaTaggedByUsername($account->getId());
    echo '<pre>' . json_encode($tagged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
