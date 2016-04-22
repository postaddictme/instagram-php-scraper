<?php
/**
 * Created by PhpStorm.
 * User: rajymbekkapisev
 * Date: 19/04/16
 * Time: 23:30
 */

require 'vendor/autoload.php';
require 'data/InstagramDataProvider.php';
require 'data/FirstInstagramDataProvider.php';
require 'model/Account.php';
require 'model/Media.php';


$instagram = new FirstInstagramDataProvider();
$account = $instagram->getAccount('kevin');
echo $account->followedByCount;
echo $account->mediaCount;

$medias = $instagram->getMedias('kevin', 150);

echo $medias[0]->imageStandardResolutionUrl;
echo $medias[0]->caption;
