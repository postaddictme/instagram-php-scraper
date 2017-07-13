<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'vendor/autoload.php';
//require_once 'src/InstagramScraper.php';

use InstagramScraper\Instagram;

//$username = 'kevin';
//
//$account = Instagram::getAccount($username);
//var_dump($account);
//
//$medias = Instagram::getMedias($username);
//if (!empty($medias)) {
//    /**
//     * @var \InstagramScraper\Model\Media $media
//     */
//    foreach ($medias as $media) {
//        var_dump($media);
//    }
//} else {
//    print 'medias array is empty';
//}

//$account = Instagram::searchAccountsByUsername($username);
//var_dump($account);

$account = Instagram::searchTagsByTagName('питер');
var_dump($account);