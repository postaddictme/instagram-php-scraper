<?php
require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', 'path/to/cache/folder');
$instagram->login();

$media = $instagram->getMediaById('1880687465858169462');

$tagged_users = $instagram->getMediaTaggedUsersByCode($media->getShortCode());

print_r($tagged_users);
