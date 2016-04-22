<?php
/**
 * Created by PhpStorm.
 * User: rajymbekkapisev
 * Date: 19/04/16
 * Time: 23:30
 */

require 'vendor/autoload.php';
require 'src/data/InstagramDataProvider.php';

require 'src/model/Account.php';
require 'src/model/Media.php';


$instagram = new InstagramScraper();
$account = $instagram->getAccount('kevin');
echo $account->followedByCount;
echo $account->mediaCount;

$medias = $instagram->getMedias('kevin', 150);

echo $medias[0]->imageStandardResolutionUrl;
echo $medias[0]->caption;
