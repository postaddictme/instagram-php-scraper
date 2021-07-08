<?php
require __DIR__ . '/../vendor/autoload.php';

$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', '/path/to/cache/folder');
$instagram->login();

$userId = $instagram->getAccount('instagram')->getId();

$highlights = $instagram->getHighlights($userId);
foreach ($highlights as $highlight) {
    echo "Highlight info:\n";
    echo "Id: {$highlight->getId()}\n";
    echo "Title: {$highlight->getTitle()}\n";
    echo "Image thumbnail url: {$highlight->getImageThumbnailUrl()}\n";
    echo "Image cropped thumbnail url: {$highlight->getImageCroppedThumbnailUrl()}\n";
    echo "Owner Id: {$highlight->getOwnerId()}\n";
    $account = $highlight->getOwner();
    echo "Account info:\n";
    echo " Id: {$account->getId()}\n";
    echo " Username: {$account->getUsername()}\n";
    echo " Profile pic url: {$account->getProfilePicUrl()}\n";
    echo "\n";
}
