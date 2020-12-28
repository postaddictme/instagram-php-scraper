<?php
ini_set('display_errors', 1);ini_set('display_startup_errors', 1);error_reporting(E_ALL);
use Phpfastcache\Helper\Psr16Adapter;
use InstagramScraper\Exception\InstagramException;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'user', 'passwd', new Psr16Adapter('Files'));
$instagram->login();

$mediaId='2474033634010898853';

function loghasLiked($bool)
{
  echo "HasLiked: ";
  var_export($bool);
  if     ($bool === null) echo " -> Unknown\n<br>"; //e.g. getMedia by tag won't give us haslike (-> an additional call to getMediaByCode/Id will give the result)
  elseif ($bool)          echo " -> Liked\n<br>";
  else                    echo " -> Not Liked\n<br>";
}

try {
    loghasLiked($instagram->getMediaById($mediaId)->getHasLiked());
    //hasLiked: False -> Not Liked

    $instagram->like($mediaId);
    loghasLiked($instagram->getMediaById($mediaId)->getHasLiked());
    //hasLiked: True -> Liked

    $instagram->unlike($mediaId);
    loghasLiked($instagram->getMediaById($mediaId)->getHasLiked());
    //hasLiked: False -> Not Liked

    echo "\n<br>";
    $media = $instagram->getCurrentTopMediasByTagName('Photography')[0];
    loghasLiked($media->getHasLiked());
    //hasLiked: NULL -> Unknown

    $media = $instagram->getMediaById($media->getId());
    loghasLiked($media->getHasLiked());
    //hasLiked: False -> Not Liked

} catch (InstagramException $ex) {
    echo $ex->getMessage();
}
