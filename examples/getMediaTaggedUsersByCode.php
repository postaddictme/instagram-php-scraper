<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'testtt5059', 'testtt5059#', new Psr16Adapter('Files'));
$instagram->login();


$medias = $instagram->getMedias('irohslegacy');
$media = $medias[0];

$tagged = $instagram->getMediaTaggedUsersByCode($media->getShortCode());

echo "Tagged info:\n";

foreach($tagged  as $tag)
{
    print_r($tag);
    echo "\n----------------------------------------\n";
}
