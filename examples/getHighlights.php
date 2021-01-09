<?php
use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'username', 'password', new Psr16Adapter('Files'));
$instagram->login();

$accountName = 'nabikaz';

$userId = $instagram->getAccount($accountName)->getId();

$highlights = $instagram->getHighlights($userId);
foreach ($highlights as $highlight) {
    echo "* Highlight info:\n";
    echo "   Id: {$highlight->getId()}\n";
    echo "   Title: {$highlight->getTitle()}\n";
    echo "   Image thumbnail url: {$highlight->getImageThumbnailUrl()}\n";
    echo "   Image cropped thumbnail url: {$highlight->getImageCroppedThumbnailUrl()}\n";
    echo "   Owner Id: {$highlight->getOwnerId()}\n";

    $account = $highlight->getOwner();
    echo "   > Account info:\n";
    echo "      Id: {$account->getId()}\n";
    echo "      Username: {$account->getUsername()}\n";
    echo "      Profile pic url: {$account->getProfilePicUrl()}\n";

    echo "   > Highlights:\n";
    $highlightId = $highlight->getId();
    $highlightStories = $instagram->getHighlightStories($highlightId);
    $stories = $highlightStories[0]->getStories();
    foreach ($stories as $story) {
        echo "      Id: {$story->getId()}\n";
        echo "      Type: {$story->getType()}\n";
        echo "      Image high resolution url: {$story->getImageHighResolutionUrl()}\n";
        echo "      Video standard resolution url: {$story->getVideoStandardResolutionUrl()}\n";
        echo "      Created time: {$story->getCreatedTime()}\n";
        echo "      -----\n";
    }
    echo "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-\n";
}
