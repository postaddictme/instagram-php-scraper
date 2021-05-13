<?php
require __DIR__ . '/../../../../vendor/autoload.php';

use InstagramScraper\Instagram;

$instagram  = Instagram::withCredentials(new \GuzzleHttp\Client(), '', '', null);
$instagram->loginWithSessionId('9970068671%3AG9gsBMhabRfBcz%3A1');      // 'sessionid' from browser
                                                                        // it can work for a long time, months
                                                                        // and can throw InstagramAuthException
$posts  = $instagram->getFeed();

foreach ($posts as $post){
    echo $post->getImageHighResolutionUrl()."\n";
}