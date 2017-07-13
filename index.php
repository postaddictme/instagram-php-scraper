<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'vendor/autoload.php';
//require_once 'src/InstagramScraper.php';

use InstagramScraper\Instagram;

$username = 'victoria.guryanova';

$account = Instagram::getAccount($username);
var_dump($account);

$account = Instagram::getMedias($username);
var_dump($account);

$account = Instagram::searchAccountsByUsername($username);
var_dump($account);