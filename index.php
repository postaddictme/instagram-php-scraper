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

//$client = new GuzzleHttp\Client();
//
//$res = $client->request('GET', "https://www.instagram.com/$username/media/");
//var_dump($res->getBody());

$instagram = new FirstInstagramDataProvider();
$account = $instagram->getAccount('raiym');
echo '<pre>';
echo json_encode($account);
echo '</pre>';
//
//$medias = $instagram->getMedias('durov', 150);
//echo json_encode($medias);
//$response = Unirest\Request::get(FirstInstagramDataProvider::INSTAGRAM_URL . 'madinamstf/media/');
//echo $response->raw_body;