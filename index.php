<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'vendor/autoload.php';
//require_once 'src/InstagramScraper.php';

use InstagramScraper\Instagram;

//const    MAIN_URL = "https://www.instagram.com",
//LOGIN_URL = "https://www.instagram.com/accounts/login/ajax/";
////echo 'Hello </br>';
////$response = Request::get(MAIN_URL);
//$response = Request::get(MAIN_URL);
//
//$cookie = $response->headers['Set-Cookie'];
//$cookie[0] = explode(';', $cookie[0])[0] ;
//$cookie[1] = explode(';', $cookie[1])[0] ;
//$cookie[2] = explode(';', $cookie[2])[0] ;
//$h = '';
//$crfs = '';
//foreach ($cookie as $c) {
//    if (strpos($c, 'sessionid') !== false) {
//        continue;
//    }
//    if (strpos($c, 'csrf') !== false) {
//        $crfs = explode('=',$c)[1];
//    }
//
//    $h = $h . $c . ';';
//}
//
////echo $crfs;
//$headers = ['cookie' => $h, 'referer' => 'https://www.instagram.com/', 'x-csrftoken' => $crfs];
//$response = Request::post(LOGIN_URL, $headers, ['username' => 'debugposter8', 'password' => 'wrong']);
//echo json_encode($response);

//echo Media::getIdFromCode('z-arAqi4DP') . '<br/>';
//echo Media::getCodeFromId('936303077400215759_123123');
//echo Media::getLinkFromId('936303077400215759_123123');
//echo shorten(936303077400215759);

//echo json_encode($instagram->getMediaById('936303077400215759_123123123'));

$media = Instagram::getMediaByCode('BQ6W8k6goDE');
var_dump($media);
echo __DIR__;

//echo json_encode($instagram->getTopMediasByTagName('hello'));