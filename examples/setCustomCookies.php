<?php
use Phpfastcache\Helper\Psr16Adapter;
use InstagramScraper\Exception\InstagramException;

require __DIR__ . '/../vendor/autoload.php';


$newCookie = [
    "ig_did"        =>	"88E6839******C29587D777B",
    "mid"           =>	"XtFTPg******3hDNk3",
    "shbid"         =>	"6262",
    "shbts"         =>	"1594047690****683",
    "sessionid"     =>	"3640101987****3A25",
    "csrftoken"     =>	"VeI80i*****0l6ggxd",
    "ds_user_id"    =>	"36*****872",
];

$instagram = \InstagramScraper\Instagram::withCredentials($this->instaUsername, $this->instaPassword, new Psr16Adapter('Files'));
$instagram->setUserAgent('User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:78.0) Gecko/20100101 Firefox/78.0');
$instagram->setCustomCookies($newCookie);
$instagram->login();
$instagram->saveSession();


try {
    $account = $instagram->getAccount('username');

} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
