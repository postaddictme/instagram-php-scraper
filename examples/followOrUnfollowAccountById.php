<?php
use Phpfastcache\Helper\Psr16Adapter;
use InstagramScraper\Exception\InstagramException;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', new Psr16Adapter('Files'));
$instagram->login();

try {
    $instagram->followAccountByAccountId('3');
    $instagram->UnFollowAccountByAccountId('3');
} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
