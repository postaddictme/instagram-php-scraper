<?php
require __DIR__ . '/../vendor/autoload.php';

function printMediaInfo(\InstagramScraper\Model\Media $media, $padding = '') {
    echo "${padding}Id: {$media->getId()}\n";
    echo "${padding}Shortcode: {$media->getShortCode()}\n";
    echo "${padding}Created at: {$media->getCreatedTime()}\n";
    echo "${padding}Caption: {$media->getCaption()}\n";
    echo "${padding}Number of comments: {$media->getCommentsCount()}\n";
    echo "${padding}Number of likes: {$media->getLikesCount()}\n";
    echo "${padding}Get link: {$media->getLink()}\n";
    echo "${padding}High resolution image: {$media->getImageHighResolutionUrl()}\n";
    echo "${padding}Media type (video/image/sidecar): {$media->getType()}\n";
}

// If account is public you can query Instagram without auth
$instagram = new \InstagramScraper\Instagram();

// If account is private and you subscribed to it firstly login
$instagram = \InstagramScraper\Instagram::withCredentials('username', 'password', '/path/to/cache/folder');
$instagram->login();

$media = $instagram->getMediaByUrl('https://www.instagram.com/p/BQ0lhTeAYo5');
echo "Media info:\n";
printMediaInfo($media);

$padding = '   ';
echo "Sidecar medias info:\n";
foreach ($media->getSidecarMedias() as $sidecarMedia) {
    printMediaInfo($sidecarMedia, $padding);
    echo "\n";
}

$account = $media->getOwner();
echo "Account info:\n";
echo "Id: {$account->getId()}\n";
echo "Username: {$account->getUsername()}\n";
echo "Full name: {$account->getFullName()}\n";
echo "Profile pic url: {$account->getProfilePicUrl()}\n";
