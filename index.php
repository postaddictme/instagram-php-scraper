<?php
/**
 * Created by PhpStorm.
 * User: rajymbekkapisev
 * Date: 19/04/16
 * Time: 23:30
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

require 'src/InstagramScraper/InstagramDataProvider.php';
require 'src/InstagramScraper/Account.php';
require 'src/InstagramScraper/Media.php';
require 'src/InstagramScraper.php';


use InstagramScraper\InstagramScraper;


$instagram = new InstagramScraper();
$account = $instagram->getAccount('kevin');
echo $account->followedByCount;
echo $account->mediaCount;

$medias = $instagram->getMedias('kevin', 150);

echo $medias[0]->imageStandardResolutionUrl;
echo $medias[0]->caption;
