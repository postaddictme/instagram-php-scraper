<?php

use InstagramScraper\Exception\InstagramException;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', '/path/to/cache/folder');
$instagram->login();

try {
    $instagram->like('1663256735663694497');
    $instagram->unlike('1663256735663694497');
} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
