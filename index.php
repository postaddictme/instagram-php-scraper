<?php
error_reporting(-1);

require_once 'vendor/autoload.php';
require_once 'src/InstagramScraper.php';
use InstagramScraper\Exception\InstagramException;
use InstagramScraper\Instagram;

$instagram = new Instagram();
try {
//    $medias = Instagram::getMedias('kevin', 1497);
    $parameters = 'ig_user(3){id,username,external_url,full_name,profile_pic_url,biography,followed_by{count},follows{count},media{count},is_private,is_verified}';
//    echo json_encode($medias[1497]);
    $account = json_decode(getContentsFromUrl($parameters), ($assoc || $assoc == "array"));
    print_r($account);
} catch (\Exception $ex) {
    print_r($ex);
}


function getContentsFromUrl($parameters) {
    if (!function_exists('curl_init')) {
        return false;
    }
    $random = generateRandomString();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.instagram.com/query/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'q='.$parameters);
    $headers = array();
    $headers[] = "Cookie:  csrftoken=$random;";
    $headers[] = "X-Csrftoken: $random";
    $headers[] = "Referer: https://www.instagram.com/";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//echo Media::getIdFromCode('z-arAqi4DP') . '<br/>';
//echo Media::getCodeFromId('936303077400215759_123123');
//echo Media::getLinkFromId('936303077400215759_123123');
//echo shorten(936303077400215759);

//echo json_encode($instagram->getMediaById('936303077400215759_123123123'));