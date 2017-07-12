<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'vendor/autoload.php';
//require_once 'src/InstagramScraper.php';

use InstagramScraper\Instagram;

$account = Instagram::getAccount('kevin');
var_dump($account);